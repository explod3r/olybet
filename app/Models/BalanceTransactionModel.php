<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BalanceTransactionModel extends Model
{
    protected $table = 'balance_transaction';
    public $timestamps = false;

    protected $fillable = [
    	'player_id',
    	'amount',
    	'amount_before'
    ];
}
