<?php

namespace App\Services\Parsers;

use App\Exceptions\MatchAlreadyProcessedException;
use App\Exceptions\ParserNotFoundException;
use App\Models\Game;
use App\Models\GameMatch;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ParserService
{
    /**
     * Class namespace for log parsers.
     *
     * @var string
     */
    public const PARSER_NAMESPACE = 'App\\Parsers\\';

    /**
     * Game
     *
     * @var Game
     */
    protected Game $game;

    /**
     * Match configuration (key/value pairs).
     *
     * @var array
     */
    protected array $matchConfig;

    /**
     * Parser class.
     *
     * @var ParserInterface
     */
    protected ParserInterface $parser;

    /**
     * Match activity.
     *
     * @var array
     */
    protected array $matchEvents;

    /**
     * Prepare configuration for match parsing.
     *
     * @param string $matchInitRow
     * @return self
     */
    public function load(string $matchInitRow): self
    {
        $this->matchConfig = $this->mapConfigValues($matchInitRow);
        $this->game = $this->findGameForMatch($this->matchConfig);
        $this->parser = $this->loadParserFromGame();

        return $this;
    }

    /**
     * Set match activity.
     *
     * @param array $matchEvents
     * @return self
     */
    public function matchEvents(array $matchEvents): self
    {
        $this->matchEvents = $matchEvents;

        return $this;
    }

    /**
     * Run parser.
     *
     * @return void
     */
    public function run()
    {
        try {
            $hash = md5(serialize($this->matchEvents));
            $match = $this->createMatchRecord($hash);
        } catch (MatchAlreadyProcessedException $e) {
            Log::error($e->getMessage());

            return;
        }

        try {
            return $this->parser
                ->matchEvents($this->matchEvents)
                ->parse($match, $this->matchConfig);

            GameMatch::query()
                ->where('hash', $hash)
                ->update(['status' => 3]);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return;
        }
    }

    protected function createMatchRecord(string $hash)
    {
        $exists = GameMatch::query()
            ->where('hash', $hash)
            ->first();

        if ($exists) {
            throw new MatchAlreadyProcessedException('Match with hash ' . $hash . ' has already been procssed');
        }

        return GameMatch::create([
            'id'          => Str::uuid(),
            'hash'        => $hash,
            'game_id'     => $this->game->id,
            'mapname'     => $this->parser->getMapName($this->matchConfig),
            'hostname'    => $this->parser->getHostname($this->matchConfig),
            'gametype'    => $this->parser->getGameType($this->matchConfig),
            'time_limit'  => $this->parser->getTimeLimit($this->matchConfig),
            'event_limit' => $this->parser->getEventLimit($this->matchConfig),
            'flags'       => json_encode($this->matchConfig),
            'version'     => $this->parser->getVersion($this->matchConfig),
        ]);
    }

    protected function loadParserFromGame()
    {
        $class = self::PARSER_NAMESPACE . $this->game->parser;

        if (!class_exists($class)) {
            throw new ParserNotFoundException($class . ' is not a valid parser');
        }

        return new $class;
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

    public function findGameForMatch(array $matchConfig): Game
    {
        foreach (Game::all() as $game) {

            foreach ($game->identifiers as $identifier) {
                $result[$identifier['key']] = false;

                if (Arr::has($matchConfig, $identifier['key'])) {
                    $result[$identifier['key']] = Str::contains(Arr::get($matchConfig, $identifier['key']), $identifier['value']);
                }
            }

            if (!in_array(false, $result)) {
                return $game;
            }
        }

        throw new ParserNotFoundException();
    }
}
