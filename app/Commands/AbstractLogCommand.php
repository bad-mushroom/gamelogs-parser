<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

abstract class AbstractLogCommand extends Command
{
    /**
     * App constants for log file statuses.
     *
     * @var int
     */
    public const STATUS_INCOMING = 1;
    public const STATUS_QUEUED = 2;
    public const STATUS_COMPLETE = 3;
    public const STATUS_FAILED = 4;

    /**
     * Fetch the storage directory used for log files.
     *
     * @return string
     */
    public function storagePath(): string
    {
        $envPath = env('STORAGE_PATH');

        if (isset($envPath) && !empty($envPath)) {
            $path = env('STORAGE_PATH');
        } else {
            $path = storage_path('gamelogs');
        }

        return rtrim($path, DIRECTORY_SEPARATOR);
    }
}
