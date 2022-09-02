<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'match_id',
        'match_player',
        'target_player',
        'action',
        'action_type',
        'time',
    ];

    public function match()
    {
        return $this->belongsTo(GameMatch::class);
    }
}
