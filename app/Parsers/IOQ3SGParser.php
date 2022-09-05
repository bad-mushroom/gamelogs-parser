<?php

namespace App\Parsers;

use App\Models\Activity;
use App\Services\Parsers\ParserInterface;
use Illuminate\Support\Str;

class IOQ3SGParser extends IdTech3Base implements ParserInterface
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

    /**
     * Item interacted with.
     *
     * @param string $time
     * @param string $event
     * @return void
     */
    public function parseItem(string $time, string $event)
    {
        $eventDetails = $this->stringToArray($event);

        $action = Str::contains($eventDetails[4], 'bought')
            ? 'item_bought'
            : 'item_found';

        return Activity::create([
            'id'            => Str::uuid(),
            'match_id'      => $this->match->id,
            'time'          => $time,
            'action'        => $action,
            'match_player'  => $eventDetails[2],
            'action_type'   => strtolower($eventDetails[3]),
        ]);
    }

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
        return $config['sg_version'];
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
