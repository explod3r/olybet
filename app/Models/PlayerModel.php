<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerModel extends Model
{
    protected $table = 'player';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'balance'
    ];
}
