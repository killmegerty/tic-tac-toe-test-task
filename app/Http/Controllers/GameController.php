<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Game;
use App\GameHistory;

class GameController extends Controller
{
    public function cellClick(Request $request)
    {
        $request->validate([
            'game_session_uuid' => 'required',
            'cell_index' => 'required',
            'cell_value' => 'required'
        ]);

        // create game if not exists
        $game = Game::firstOrCreate(
            ['game_session_uuid' => $request->game_session_uuid]
        );

        // get last turn if exists
        $currentGameHistory = GameHistory::where(
            'game_id', $game->id
        )->orderBy('turn', 'desc')->first();

        // save human turn in DB
        $gameHistoryTurn = new GameHistory();
        $gameHistoryTurn->game_id = $game->id;
        $gameHistoryTurn->turn = $currentGameHistory ? $currentGameHistory->turn + 1 : 0;
        $gameHistoryTurn->cell_index = $request->cell_index;
        $gameHistoryTurn->cell_value = $request->cell_value;
        if ($gameHistoryTurn->save()) {
            $computerTurn = $this->_computerTurn($request->game_session_uuid);
            return compact('computerTurn');
        }
    }

    protected function _updateGameBoard($gameSessionUuid) {
        $gameBoard = [null, null, null, null, null, null, null, null, null];
        $game = Game::with('gameHistory')
                ->where('game_session_uuid', $gameSessionUuid)
                ->first();
        foreach ($game->gameHistory as $gameHistory) {
            $gameBoard[$gameHistory->cell_index] = $gameHistory->cell_value;
        };
        return $gameBoard;
    }

    protected function _getGameBoard($gameSessionUuid) {
        return $this->_updateGameBoard($gameSessionUuid);
    }

    protected function _checkWinner() {

    }

    protected function _computerTurn($gameSessionUuid)
    {
        $game = Game::with('gameHistory')
                ->where('game_session_uuid', $gameSessionUuid)
                ->first();

        // init current status of game board
        // $gameBoard = [null, null, null, null, null, null, null, null, null];
        // $turn = 0;
        // foreach ($game->gameHistory as $gameHistory) {
        //     $gameBoard[$gameHistory->cell_index] = $gameHistory->cell_value;
        //     if ($gameHistory->turn > $turn) {
        //         $turn = $gameHistory->turn;
        //     }
        // };
        // $turn++;
        $gameBoard = $this->_getGameBoard($gameSessionUuid);


        // select all indexes with null value
        $nullIndexes = [];
        foreach ($gameBoard as $index => $value) {
            if (!isset($value)) {
                $nullIndexes[] = $index;
            }
        };

        // select random index and use it for writing value of computer return
        $computerTurn = [
            'game_session_uuid' => $gameSessionUuid,
            'turn' => Game::getMaxTurnGameHistoryBySessionUuid($gameSessionUuid) + 1,
            'cell_index' => $nullIndexes[array_rand($nullIndexes)],
            'cell_value' => 'O'
        ];

        // save computer turn in DB
        $gameHistoryTurn = new GameHistory();
        $gameHistoryTurn->game_id = $game->id;
        $gameHistoryTurn->turn = $computerTurn['turn'];
        $gameHistoryTurn->cell_index = $computerTurn['cell_index'];
        $gameHistoryTurn->cell_value = $computerTurn['cell_value'];
        if ($gameHistoryTurn->save()) {
            return $computerTurn;
        }
    }
}
