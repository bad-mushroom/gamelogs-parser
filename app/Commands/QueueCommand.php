<?php

namespace App\Commands;

use App\Exceptions\DuplicateGamelogException;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SplFileInfo;

/**
 * Queue command.
 */
class QueueCommand extends AbstractLogCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'logs:queue';

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
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }

    /**
     * Queue log files for parsing.
     *
     * Scans "incoming" log directory for files that have not already been parsed
     * then moves them to "queued". Duplicate files are ignored and will need
     * to be removed manually.
     *
     * @return mixed
     */
    public function handle()
    {
        $files = File::allFiles($this->storagePath() . DIRECTORY_SEPARATOR . env('DIR_GAMELOGS_INCOMING'));

        $this->info(count($files) . ' Game ' . Str::plural('log', count($files)) .' to Queue');

        foreach ($files as $file) {
            if ($this->isReadableFile($file)) {
                $hash = md5_file($file);

                try {
                    $this->task('Checking ' . $file->getFilename(), function () use ($file, $hash) {
                        return $this->createGamelogFromFile($file, $hash);
                    });
                } catch (Exception $e) {
                    $this->error($e->getMessage());

                    continue;
                }

                $this->task('Queuing ' . $file->getFilename(), function() use ($file, $hash) {
                    $source = $this->storagePath() . DIRECTORY_SEPARATOR . env('DIR_GAMELOGS_INCOMING') . DIRECTORY_SEPARATOR . $file->getFilename();
                    $destination = $this->storagePath() . DIRECTORY_SEPARATOR . env('DIR_GAMELOGS_QUEUED') . DIRECTORY_SEPARATOR . $hash . '.log';

                    return File::move($source, $destination);
                });
            } else {
                $this->error('Unreadable file found at ' . $file->getFilename());
            }
        }
    }

    /**
     * Create `gamelogs` record in database.
     *
     * @param SplFileInfo $file
     * @param string $hash
     * @return boolean
     * @throws DuplicateGamelogException
     */
    protected function createGamelogFromFile(SplFileInfo $file, string $hash): bool
    {
        if (DB::scalar('select count(hash) from gamelogs where hash = ?', [$hash])) {
            throw new DuplicateGamelogException($file->getFilename() . ' has alrady been parsed. Skipping.');
        }

        return DB::table('gamelogs')
            ->insert([
                'hash'              => $hash,
                'original_filename' => $file->getFilename(),
                'filename'          => $hash . '.log',
                'status'            => self::STATUS_QUEUED,
                'created_at'        => Carbon::now(),
            ]);
    }

    /**
     * Determine if file is readable, not ignored, and is not binary.
     *
     * @param SplFileInfo $file
     * @return boolean
     */
    protected function isReadableFile(SplFileInfo $file)
    {
        return $file->isReadable()
            && !in_array($file->getFilename(), $this->ignoredFiles)
            && mb_detect_encoding((string) $file, null, true) === 'ASCII';
    }
}
