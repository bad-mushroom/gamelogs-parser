<?php

namespace App\Commands;

use App\Models\Game;
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

    private ParserService $parser;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $hash = $this->argument('hash');
        $gamelog = DB::scalar('select count(id) from gamelogs where status = ? and hash = ? limit 1', [self::STATUS_QUEUED, $hash]);

        if ($gamelog) {
            $fileHandle = fopen(storage_path('gamelogs') . DIRECTORY_SEPARATOR . env('DIR_GAMELOGS_QUEUED') . DIRECTORY_SEPARATOR . $hash . '.log', 'r');
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
}
