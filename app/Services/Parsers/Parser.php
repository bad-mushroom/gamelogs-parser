<?php

namespace App\Services\Parsers;

use Illuminate\Support\Facades\Facade;

/**
 * @method mixed load()
 */
class Parser extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ParserService::class;
    }
}
