<?php

namespace App\Commands;

use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

class Queue extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'queue';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Queue log files for parsing.';

    /**
     * File names listed here will be ignored if they are found in the
     * gamelogs/incoming directory.
     *
     * @var array
     */
    protected $ignoredFiles = [
        '.gitignore',
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $files = File::allFiles(storage_path('gamelogs') . DIRECTORY_SEPARATOR . env('DIR_GAMELOGS_INCOMING'));

        foreach ($files as $file) {

            if ($file->isReadable() && !in_array($file->getFilename(), $this->ignoredFiles)) {
                $hash = md5_file($file);
                try {
                    // Create record
                } catch (Exception $e) {
                    Log::error($e->getMessage());
                }

                Storage::disk('gamelogs')->move(env('DIR_GAMELOGS_INCOMING') .'/'. $file->getFilename(), env('DIR_GAMELOGS_QUEUED') . DIRECTORY_SEPARATOR . $hash . '.log');
            }
        }

    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
