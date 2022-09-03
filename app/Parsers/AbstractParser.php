<?php

namespace App\Parsers;

abstract class AbstractParser
{
    /**
     * Match activities.
     *
     * @var array
     */
    protected array $matchEvents;

    /**
     * Set match events.
     *
     * @param array $matchEvents
     * @return void
     */
    public function matchEvents(array $matchEvents)
    {
        $this->matchEvents = $matchEvents;

        return $this;
    }

    /**
     * Utitlty to split a string by a delimiting character.
     *
     * @param string $string
     * @param string $delimiter
     * @return array
     */
    public function stringToArray(string $string, $delimiter = ' '): array
    {
        return explode($delimiter, trim($string));
    }

    /**
     * Create key-value pairs of config data.
     *
     * @param string $params
     * @return array
     */
    public function mapConfigValues(string $params): array
    {
        $configInfo = [];
        $info = explode('\\', $params);
        array_shift($info);

        foreach (array_chunk($info, 2) as $keys => $value) {
            $configInfo[$value[0]] = $value[1];
        }

        return $configInfo;
    }

}
