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
use LaravelZero\Framework\Commands\Command;
use SplFileInfo;

/**
 * Queue command.
 */
class Queue extends Command
{
    public const STATUS_INCOMING = 1;
    public const STATUS_QUEUED = 2;
    public const STATUS_OUTGOING = 3;

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
        $files = File::allFiles(storage_path('gamelogs') . DIRECTORY_SEPARATOR . env('DIR_GAMELOGS_INCOMING'));

        $this->info(count($files) . ' Game ' . Str::plural('log', count($files)) .' to Parse');

        foreach ($files as $file) {
            if ($file->isReadable() && !in_array($file->getFilename(), $this->ignoredFiles)) {
                $hash = md5_file($file);

                try {
                    $this->createGamelogFromFile($file, $hash);
                    $this->info($file->getFilename() . ' has been queued.');
                } catch (Exception $e) {
                    $this->error($e->getMessage());

                    continue;
                }

                $source = env('DIR_GAMELOGS_INCOMING') . DIRECTORY_SEPARATOR . $file->getFilename();
                $destination = env('DIR_GAMELOGS_QUEUED') . DIRECTORY_SEPARATOR . $hash . '.log';

                Storage::disk('gamelogs')->move($source, $destination);
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
        $existing = DB::scalar('select count(id) from gamelogs where hash = ?', [$hash]);

        if ($existing) {
            throw new DuplicateGamelogException($file->getFilename() . ' has alrady been parsed. Skipping.');
        }

        return DB::table('gamelogs')
            ->insert([
                'id'                => Str::uuid(),
                'hash'              => $hash,
                'original_filename' => $file->getFilename(),
                'status'            => self::STATUS_INCOMING,
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ]);
    }
}
