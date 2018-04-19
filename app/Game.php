<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Game extends Model
{
    protected $fillable = ['game_session_uuid'];
    const MODE_VS_AI = 'vs_ai';
    const MODE_VS_HUMAN = 'vs_human';
    const STATUS_DRAW = 'draw';
    const STATUS_O_WIN = 'winner \'O\'';
    const STATUS_X_WIN = 'winner \'X\'';

    public function gameHistory()
    {
        return $this->hasMany('App\GameHistory')->orderBy('turn');
    }

    static public function getLastGameHistoryBySessionUuid($uuid)
    {
        $game = DB::table('games')
            ->leftJoin('games_history', 'games.id', '=', 'games_history.game_id')
            ->where('game_session_uuid', $uuid)
            ->orderBy('turn', 'desc')
            ->first();

        return $game;
    }

    static public function getMaxTurnGameHistoryBySessionUuid($gameSessionUuid)
    {
        $maxTurn = DB::table('games')
            ->leftJoin('games_history', 'games.id', '=', 'games_history.game_id')
            ->where('game_session_uuid', $gameSessionUuid)
            ->orderBy('turn', 'desc')
            ->max('games_history.turn');

        return $maxTurn;
    }
}
