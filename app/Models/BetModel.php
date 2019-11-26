<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BetModel extends Model
{
    protected $table = 'bet';

    protected $fillable = [
    	'id',
    	'stake_amount',
    	'created_at'
    ];
}
