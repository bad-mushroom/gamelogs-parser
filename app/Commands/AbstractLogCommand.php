<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

abstract class AbstractLogCommand extends Command
{
    public const STATUS_INCOMING = 1;
    public const STATUS_QUEUED = 2;
    public const STATUS_COMPLETE = 3;
    public const STATUS_FAILED = 4;
}
