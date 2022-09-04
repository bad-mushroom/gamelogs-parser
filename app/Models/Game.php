<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'identifiers' => 'json',
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
