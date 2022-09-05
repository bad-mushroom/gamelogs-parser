<?php

namespace App\Commands;

use App\Services\Parsers\Parser;
use App\Services\Parsers\ParserService;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ParseCommand extends AbstractLogCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'logs:parse {hash?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Parse gamelog file by its hash.';

    /**
     * Parser class to be called.
     *
     * @var ParserService
     */
    private ParserService $parser;

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule(Schedule $schedule)
    {
        // $schedule->command(static::class)->everyMinute();
    }

    private bool $inMatch = false;
    private array $matchData = [];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $hash = $this->argument('hash');
        $gamelogs = $this->fetchGamelogs($hash);

        if ($gamelogs->count() > 0) {
            foreach ($gamelogs as $gamelog) {

                $this->task('Parse Log: ' . $gamelog->filename, function() use ($gamelog) {
                        $fileHandle = fopen($this->storagePath() . DIRECTORY_SEPARATOR . env('DIR_GAMELOGS_QUEUED') . DIRECTORY_SEPARATOR . $gamelog->filename, 'r');

                        while (!feof($fileHandle)) {
                            $row = trim(fgets($fileHandle));

                            try {
                                // Check for match initialization string
                                if (strpos($row, 'InitGame:')) {
                                    $this->parseMatchStart($row);

                                    // Check for match shutdown string
                                } elseif (strpos($row, 'ShutdownGame:') && $this->inMatch === true) {
                                    $this->parseMatchEnd($row);

                                    // If in a match, continue capturing data
                                } elseif ($this->inMatch === true) {
                                    $this->matchData[] = $row;

                                    // If not in a match, disregard data
                                } else {
                                    //
                                }
                            } catch (Exception $e) {
                                DB::table('gamelogs')
                                    ->where(['hash' => $gamelog->hash])
                                    ->update([
                                        'status'         => self::STATUS_FAILED,
                                        'status_message' => $e->getMessage(),
                                    ]);
                                return false;
                            }
                        }

                        fclose($fileHandle);

                        DB::table('gamelogs')
                            ->where(['hash' => $gamelog->hash])
                            ->update(['status' => self::STATUS_COMPLETE]);

                        File::move(
                            $this->storagePath() . DIRECTORY_SEPARATOR . env('DIR_GAMELOGS_QUEUED') . DIRECTORY_SEPARATOR . $gamelog->filename,
                            $this->storagePath() . DIRECTORY_SEPARATOR . env('DIR_GAMELOGS_COMPLETE') . DIRECTORY_SEPARATOR . $gamelog->filename
                        );
                });
            }
        }
    }

    public function fetchGamelogs(?string $hash = null)
    {
        if (!empty($hash)) {
            $gamelogs = DB::table('gamelogs')
                ->where('status', self::STATUS_QUEUED)
                ->where('hash', $hash)
                ->get();
        } else {
            $gamelogs = DB::table('gamelogs')
                ->where('status', self::STATUS_QUEUED)
                ->get();
        }

        return $gamelogs;
    }

    protected function parseMatchStart(string $row)
    {
        $this->inMatch = true;
        $this->matchData[] = $row;

        try {
            $this->parser = Parser::load($row);
        } catch (Exception $e) {
            $this->inMatch = false;
            $this->matchData = [];

            throw $e;
        }
    }

    protected function parseMatchEnd(string $row)
    {
        $this->matchData[] = $row;

        try {
            $this->parser->matchEvents($this->matchData)->run();
        } catch (Exception $e) {
            throw $e;
        }

        $this->inMatch = false;
        $this->matchData = [];
    }
}
