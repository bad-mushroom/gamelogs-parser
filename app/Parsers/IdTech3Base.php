<?php

namespace App\Parsers;

use App\Models\Activity;
use App\Models\GameMatch;
use App\Models\Player;
use Illuminate\Support\Str;

class IdTech3Base extends AbstractParser
{
    /**
     * Log file events to parse.
     *
     * @var array
     */
    protected $actions = [
        'InitGame',
        'ClientConnect',
        'ClientBegin',
        'ClientUserinfoChanged',
        'Item',
        'Kill',
        'say',
        'sayteam',
        'Exit',
        'ShutdownGame',
    ];

    /**
     * Match to parse its data for.
     *
     * @var GameMatch
     */
    protected GameMatch $match;

    protected array $botIds = [];

    /**
     * Parse match activity.
     *
     * @param GameMatch $match
     * @param array $config
     * @return void
     */
    public function parse(GameMatch $match, array $config)
    {
        $this->match = $match;
        $this->match->update([
            'flags' => json_encode($config),
        ]);

        foreach ($this->matchEvents as $event) {
            preg_match('/[0-9]+:[0-9]+ ([^:]+)/', $event, $eventDetails);
            $time = $this->stringToArray($eventDetails[0])[0];
            $action = $eventDetails[1];
            $method = 'parse' . ucwords(strtolower($action));

            if (in_array($action, $this->actions) && method_exists($this, $method)) {
                $this->$method($time, $event);
            }
        }

        return true;
    }

    // -- Match

    /**
     * Start of match.
     *
     * @param string $time
     * @param string $event
     * @return void
     */
    public function parseInitgame(string $time, string $event)
    {
        $this->match->update([
            'start_time' => $time,
        ]);
    }

    /**
     * End of match.
     *
     * @param string $time
     * @param string $event
     * @return void
     */
    public function parseShutdowngame(string $time, string $event)
    {
        $this->match->update([
            'end_time' => $time,
        ]);
    }

    /**
     * End of match due to time or event limit.
     *
     * @param string $time
     * @param string $event
     * @return void
     */
    public function parseExit(string $time, string $event)
    {
        // Match limit hit
    }

    // -- Players

    /**
     * Player connected.
     *
     * @param string $time
     * @param string $event
     * @return Player
     */
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

    /**
     * Player began match.
     *
     * @param string $time
     * @param string $event
     * @return void
     */
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

    /**
     * Player changed their settings (name, model, etc).
     *
     * @param string $time
     * @param string $event
     * @return void
     */
    public function parseClientuserinfochanged(string $time, string $event)
    {
        $details = $this->stringToArray($event);
        $matchPlayerId = $details[2];
        $playerInfo = $this->mapConfigValues('\\' . $details[3]);

        $isBot = (int) array_key_exists('skill', $playerInfo) ?? false;

        if ($isBot) {
            $this->botIds[] = $matchPlayerId;
        }

        Player::query()
            ->where('match_id', $this->match->id)
            ->where('match_player', $matchPlayerId)
            ->update([
                'name'   => $playerInfo['n'],
                'is_bot' => $isBot,
            ]);
    }

    // -- Match Events

    /**
     * Played killed.
     *
     * @param string $time
     * @param string $event
     * @return Activity
     */
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
            'action_type'   => strtolower(end($eventDetails)),
        ]);
    }

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

    /**
     * Chat message.
     *
     * @param string $time
     * @param string $event
     * @return void
     */
    public function parseSay(string $time, string $event)
    {
        $eventDetails = $this->stringToArray($event, ':');

        $player = Player::query()
            ->where('name', trim($eventDetails[2]))
            ->where('match_id', $this->match->id)
            ->first();

        if (env('SETTING_IGNORE_BOT_CHAT') && in_array($player->id, $this->botIds)) {
            return;
        }

        return Activity::create([
            'id'            => Str::uuid(),
            'match_id'      => $this->match->id,
            'time'          => $time,
            'action'        => 'chat',
            'match_player'  => $player->match_player,
            'action_type'   => $eventDetails[3],
        ]);
    }

    /**
     * Team hat message.
     *
     * @param string $time
     * @param string $event
     * @return void
     */
    public function parseSayteam(string $time, string $event)
    {
        $eventDetails = $this->stringToArray($event, ':');

        $player = Player::query()
            ->where('name', trim($eventDetails[2]))
            ->where('match_id', $this->match->id)
            ->first();

        if (env('SETTING_IGNORE_BOT_CHAT') && in_array($player->id, $this->botIds)) {
            return;
        }

        return Activity::create([
            'id'            => Str::uuid(),
            'match_id'      => $this->match->id,
            'time'          => $time,
            'action'        => 'team_chat',
            'match_player'  => $player->match_player,
            'action_type'   => $eventDetails[3],
        ]);
    }
}
