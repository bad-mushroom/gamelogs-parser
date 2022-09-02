<?php

namespace App\Parsers;

use App\Models\Activity;
use App\Models\GameMatch;
use App\Models\Player;
use Illuminate\Support\Str;

class IdTech3Base extends AbstractParser
{
    protected $actions = [
        'InitGame',
        'ClientConnect',
        'ClientBegin',
        'ClientUserinfoChanged',
        'Item',
        'Kill',
        'say',
        'Exit',
        'ShutdownGame',
    ];

    protected GameMatch $match;

    public function parse(GameMatch $match, array $config)
    {
        $this->match = $match;

        foreach ($this->matchEvents as $event) {
            preg_match('/[0-9]+:[0-9]+ ([^:]+)/', $event, $eventDetails);
            $time = $this->stringToArray($eventDetails[0])[0];
            $action = $eventDetails[1];
            $method = 'parse' . ucwords(strtolower($action));

            if (in_array($action, $this->actions) && method_exists($this, $method)) {
                $this->$method($time, $event);
            }
        }
    }

    // -- Match

    public function parseInitgame(string $time, string $event)
    {
        $this->match->update([
            'start_time' => $time,
        ]);
    }

    public function parseShutdowngame(string $time, string $event)
    {
        $this->match->update([
            'end_time' => $time,
        ]);
    }

    public function parseExit(string $time, string $event)
    {
        // Match limit hit
    }

    // -- Players

    public function parseClientconnect(string $time, string $event): Player
    {
        $details = $this->stringToArray($event);
        $matchPlayerId = $details[2];

        return Player::create([
            'id'                 => Str::uuid(),
            'match_id'           => $this->match->id,
            'match_player'       => $matchPlayerId,
            'match_connect_time' => $time,
        ]);
    }

    public function parseClientbegin(string $time, string $event)
    {
        $details = $this->stringToArray($event);
        $matchPlayerId = $details[2];

        Player::query()
            ->where('match_id', $this->match->id)
            ->where('match_player', $matchPlayerId)
            ->update([
                'match_join_time' => $time,
            ]);
    }

    public function parseClientuserinfochanged(string $time, string $event)
    {
        $details = $this->stringToArray($event);
        $matchPlayerId = $details[2];
        $playerInfo = $this->mapConfigValues('\\' . $details[3]);

        Player::query()
            ->where('match_id', $this->match->id)
            ->where('match_player', $matchPlayerId)
            ->update([
                'name' => $playerInfo['n'],
            ]);
    }

    // -- Match Events

    public function parseKill(string $time, string $event): Activity
    {
        $eventDetails = $this->stringToArray($event);

        return Activity::create([
            'id'            => Str::uuid(),
            'match_id'      => $this->match->id,
            'time'          => $time,
            'action'        => 'kill',
            'match_player'  => $eventDetails[2],
            'target_player' => $eventDetails[3],
            'action_type'   => strtolower($eventDetails[9]),
        ]);
    }

    public function parseItem(string $time, string $event)
    {
        $eventDetails = $this->stringToArray($event);

        return Activity::create([
            'id'            => Str::uuid(),
            'match_id'      => $this->match->id,
            'time'          => $time,
            'action'        => 'item',
            'match_player'  => $eventDetails[2],
            'action_type'   => strtolower($eventDetails[3]),
        ]);
    }

    // -- Misc

    public function parseSay(string $time, string $event)
    {
        $eventDetails = $this->stringToArray($event, ':');

        $player = Player::query()
            ->where('name', trim($eventDetails[2]))
            ->where('match_id', $this->match->id)
            ->first();

        return Activity::create([
            'id'            => Str::uuid(),
            'match_id'      => $this->match->id,
            'time'          => $time,
            'action'        => 'chat',
            'match_player'  => $player->match_player,
            'action_type'   => $eventDetails[3],
        ]);
    }
}
