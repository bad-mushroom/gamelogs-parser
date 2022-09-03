<?php

namespace App\Commands;

use App\Services\Parsers\Parser;
use App\Services\Parsers\ParserService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;

class ParseCommand extends AbstractLogCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'logs:parse {hash}';

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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $hash = $this->argument('hash');
        $gamelog = DB::table('gamelogs')
            ->where('status', self::STATUS_QUEUED)
            ->where('hash', $hash)
            ->limit(1);

        if ($gamelog) {
            $fileHandle = fopen(storage_path('gamelogs') . DIRECTORY_SEPARATOR . env('DIR_GAMELOGS_QUEUED') . DIRECTORY_SEPARATOR . $gamelog->filename, 'r');
        }

        $inMatch = false;
        $match = [];

        while (!feof($fileHandle)) {
            $row = trim(fgets($fileHandle));

            // Check for match initialization string
            if (strpos($row, 'InitGame:')) {
                $inMatch = true;
                $match[] = $row;
                $this->parser = Parser::load($row);

            // Check for match shutdown string
            } elseif (strpos($row, 'ShutdownGame:') && $inMatch === true) {
                $match[] = $row;
                $this->parser->matchEvents($match)->run();
                $inMatch = false;
                $match = [];

            // If in a match, continue capturing data
            } elseif ($inMatch === true) {
                $match[] = $row;

            // If not in a match, disregard data
            } else {
                //
            }
        }

        fclose($fileHandle);
    }

}
