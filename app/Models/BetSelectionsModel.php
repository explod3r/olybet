<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BetSelectionsModel extends Model
{
    protected $table = 'bet_selections';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'bet_id',
        'selection_id',
        'odds'
    ];
}
