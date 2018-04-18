<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameHistory extends Model
{
    protected $table = 'games_history';

    public function game()
    {
        return $this->belongsTo('App\Game');
    }
}
