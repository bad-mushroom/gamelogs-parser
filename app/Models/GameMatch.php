<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'id',
        'hash',
        'game_id',
        'flags',
        'start_time',
        'end_time',
        'hostname',
        'gametype',
        'event_limit',
        'time_limit',
        'mapname',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * PK Does not increments
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * PK is a string
     *
     * @return string
     */
    public function getKeyType()
    {
        return 'string';
    }
}
