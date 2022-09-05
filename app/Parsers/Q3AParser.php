<?php

namespace App\Parsers;

use App\Services\Parsers\ParserInterface;

class Q3AParser extends IdTech3Base implements ParserInterface
{
    public const GAME_TYPES = [
        self::TYPE_FFA          => 'Free for All',
        self::TYPE_ONE_ON_ONE   => 'Tournament',
        self::TYPE_SINGLE       => 'Single Player',
        self::TYPE_TEAM_DM      => 'Team Deathmatch',
        self::TYPE_CTF          => 'Capture The Flag',
    ];

    public const TYPE_FFA = 0;
    public const TYPE_ONE_ON_ONE = 1;
    public const TYPE_SINGLE = 2;
    public const TYPE_TEAM_DM = 3;
    public const TYPE_CTF = 4;

    public function getMapname(array $config): string
    {
        return $config['mapname'] ?? 'Unknown';
    }

    public function getHostname(array $config): string
    {
        return $config['sv_hostname'] ?? 'Unknown';
    }

    public function getGameType(array $config): string
    {
        $type = preg_replace('~\D~', '', $config['g_gametype']);

        return self::GAME_TYPES[$type];
    }

    public function getTimeLimit(array $config): string
    {
        return $config['timelimit'];
    }

    public function getVersion(array $config): string
    {
        return $config['version'];
    }

    public function getEventLimit(array $config): ?string
    {
        if ($config['g_gametype'] < self::TYPE_CTF) {
            $limit = isset($config['capturelimit']) ? $config['capturelimit'] : 0;
        } else {
            $limit = isset($config['fraglimit']) ? $config['fraglimit'] : 0;
        }

        return $limit;
    }
}
