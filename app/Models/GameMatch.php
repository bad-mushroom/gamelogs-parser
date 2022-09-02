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
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
