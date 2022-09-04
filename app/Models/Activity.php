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
