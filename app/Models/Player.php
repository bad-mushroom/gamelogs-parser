<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'match_id',
        'match_player',
        'match_join_time',
        'match_connect_time',
        'name',
        'team',
    ];

    public function match()
    {
        return $this->belongsTo(GameMatch::class);
    }
}

