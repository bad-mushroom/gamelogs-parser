<?php

namespace App\Parsers;

use App\Models\Game;

abstract class AbstractParser
{
    protected array $matchEvents;

    protected Game $game;

    public function matchEvents(array $matchEvents)
    {
        $this->matchEvents = $matchEvents;

        return $this;
    }

    public function game(Game $game)
    {
        $this->game = $game;

        return $this;
    }

    public function stringToArray($string, $delimiter = ' '): array
    {
        return explode($delimiter, trim($string));
    }

    public function mapConfigValues(string $params): array
    {
        $gameInfo = [];
        $info = explode('\\', $params);
        array_shift($info);

        foreach (array_chunk($info, 2) as $keys => $value) {
            $gameInfo[$value[0]] = $value[1];
        }

        return $gameInfo;
    }
}
