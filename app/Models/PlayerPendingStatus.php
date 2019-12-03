<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerPendingStatus extends Model
{
    protected $table = 'player_pending_status';
    public $primaryKey = 'player_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'player_id'
    ];
}
