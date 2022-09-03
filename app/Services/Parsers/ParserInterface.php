<?php

namespace App\Services\Parsers;

use App\Models\GameMatch;

interface ParserInterface
{
    /**
     * Set match activity on parser.
     *
     * @param array $matchEvents
     * @return void
     */
    public function matchEvents(array $matchEvents);

    /**
     * Parse command.
     *
     * @param GameMatch $gameMatch
     * @param array $config
     * @return void
     */
    public function parse(GameMatch $gameMatch, array $config);
}
