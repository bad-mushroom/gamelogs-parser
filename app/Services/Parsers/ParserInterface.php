<?php

namespace App\Services\Parsers;

use App\Models\Game;
use App\Models\GameMatch;

interface ParserInterface
{
    public function game(Game $game);

    public function matchEvents(array $matchEvents);

    public function parse(GameMatch $gameMatch, array $config);
}
