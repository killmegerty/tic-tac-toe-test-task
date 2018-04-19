<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\GameHistoryCreated;
use App\Game;
use App\GameHistory;

class GameController extends Controller
{
    public function cellClickHandler(Request $request) {
        $request->validate([
            'game_session_uuid' => 'required',
            'cell_index' => 'required',
            'cell_value' => 'required'
        ]);

        $game = Game::where('game_session_uuid', $request->game_session_uuid)
                    ->first();

        if (!$game) {
            return [
                'status' => 'error',
                'error' => 'game not exists'
            ];
        }

        if ($game->status != null) {
            return [
                'status' => 'error',
                'error' => 'game already finished'
            ];
        }

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
            // announce that value saved in DB
            broadcast(new GameHistoryCreated($gameHistoryTurn, $game))->toOthers();

            $winner = $this->_checkWinner(
                $this->_getGameBoard($request->game_session_uuid),
                $request->game_session_uuid
            );
            if ($winner) {
                return ['status' => 'OK'];
            }

            if ($game->mode == Game::MODE_VS_AI) {
                $aiTurn = $this->_aiDoTurn($request->game_session_uuid);
                $status = 'OK';
                return compact('aiTurn', 'status');
            } else {
                return ['status' => 'OK'];
            }
        }
    }

    // public function cellClick(Request $request)
    // {
    //     $request->validate([
    //         'game_session_uuid' => 'required',
    //         'cell_index' => 'required',
    //         'cell_value' => 'required'
    //     ]);
    //
    //     // create game if not exists
    //     $game = Game::firstOrCreate(
    //         ['game_session_uuid' => $request->game_session_uuid]
    //     );
    //
    //     if ($game->status != null) {
    //         return [
    //             'status' => 'error',
    //             'error' => 'game already finished'
    //         ];
    //     }
    //
    //     // get last turn if exists
    //     $currentGameHistory = GameHistory::where(
    //         'game_id', $game->id
    //     )->orderBy('turn', 'desc')->first();
    //
    //     // save human turn in DB
    //     $gameHistoryTurn = new GameHistory();
    //     $gameHistoryTurn->game_id = $game->id;
    //     $gameHistoryTurn->turn = $currentGameHistory ? $currentGameHistory->turn + 1 : 0;
    //     $gameHistoryTurn->cell_index = $request->cell_index;
    //     $gameHistoryTurn->cell_value = $request->cell_value;
    //     if ($gameHistoryTurn->save()) {
    //         // announce that value saved in DB
    //         broadcast(new GameHistoryCreated($gameHistoryTurn, $game))->toOthers();
    //
    //         $winner = $this->_checkWinner(
    //             $this->_getGameBoard($request->game_session_uuid),
    //             $request->game_session_uuid
    //         );
    //         if ($winner) {
    //             return ['status' => 'OK'];
    //         }
    //
    //         $aiTurn = $this->_aiDoTurn($request->game_session_uuid);
    //         $status = 'OK';
    //         return compact('aiTurn', 'status');
    //     }
    // }

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

    protected function _checkWinner($gameBoard, $gameSessionUuid) {
        if (
            ($gameBoard[0] == 'O' && $gameBoard[1] == 'O' && $gameBoard[2] == 'O') ||
            ($gameBoard[0] == 'O' && $gameBoard[3] == 'O' && $gameBoard[6] == 'O') ||
            ($gameBoard[0] == 'O' && $gameBoard[4] == 'O' && $gameBoard[8] == 'O') ||
            ($gameBoard[2] == 'O' && $gameBoard[5] == 'O' && $gameBoard[8] == 'O') ||
            ($gameBoard[6] == 'O' && $gameBoard[7] == 'O' && $gameBoard[8] == 'O') ||
            ($gameBoard[6] == 'O' && $gameBoard[4] == 'O' && $gameBoard[2] == 'O') ||
            ($gameBoard[1] == 'O' && $gameBoard[4] == 'O' && $gameBoard[7] == 'O') ||
            ($gameBoard[3] == 'O' && $gameBoard[4] == 'O' && $gameBoard[5] == 'O')
        ) {
            $game = Game::where('game_session_uuid', $gameSessionUuid)->first();
            $game->status = Game::STATUS_O_WIN;
            $game->save();
            return 'O';
        } else if (
            ($gameBoard[0] == 'X' && $gameBoard[1] == 'X' && $gameBoard[2] == 'X') ||
            ($gameBoard[0] == 'X' && $gameBoard[3] == 'X' && $gameBoard[6] == 'X') ||
            ($gameBoard[0] == 'X' && $gameBoard[4] == 'X' && $gameBoard[8] == 'X') ||
            ($gameBoard[2] == 'X' && $gameBoard[5] == 'X' && $gameBoard[8] == 'X') ||
            ($gameBoard[6] == 'X' && $gameBoard[7] == 'X' && $gameBoard[8] == 'X') ||
            ($gameBoard[6] == 'X' && $gameBoard[4] == 'X' && $gameBoard[2] == 'X') ||
            ($gameBoard[1] == 'X' && $gameBoard[4] == 'X' && $gameBoard[7] == 'X') ||
            ($gameBoard[3] == 'X' && $gameBoard[4] == 'X' && $gameBoard[5] == 'X')
        ) {
            $game = Game::where('game_session_uuid', $gameSessionUuid)->first();
            $game->status = Game::STATUS_X_WIN;
            $game->save();
            return 'X';
        }

        // draw check
        $isCellsHaveNull = false;
        foreach ($gameBoard as $cellVal) {
            if (is_null($cellVal)) {
                $isCellsHaveNull = true;
                break;
            }
        }
        if (!$isCellsHaveNull) {
            $game = Game::where('game_session_uuid', $gameSessionUuid)->first();
            $game->status = Game::STATUS_DRAW;
            $game->save();
            return 'DRAW';
        }

        return false;
    }

    protected function _aiDoTurn($gameSessionUuid)
    {
        $game = Game::with('gameHistory')
                ->where('game_session_uuid', $gameSessionUuid)
                ->first();

        // init gameBoard array
        $gameBoard = $this->_getGameBoard($gameSessionUuid);

        // select all indexes with null value
        $nullIndexes = [];
        foreach ($gameBoard as $index => $value) {
            if (!isset($value)) {
                $nullIndexes[] = $index;
            }
        };

        // draw
        if (sizeof($nullIndexes) == 0) {
            return false;
        }

        // select random index and use it for writing value of AI return
        $aiTurn = [
            'game_session_uuid' => $gameSessionUuid,
            'turn' => Game::getMaxTurnGameHistoryBySessionUuid($gameSessionUuid) + 1,
            'cell_index' => $nullIndexes[array_rand($nullIndexes)],
            'cell_value' => 'O'
        ];

        // save AI turn in DB
        $gameHistoryTurn = new GameHistory();
        $gameHistoryTurn->game_id = $game->id;
        $gameHistoryTurn->turn = $aiTurn['turn'];
        $gameHistoryTurn->cell_index = $aiTurn['cell_index'];
        $gameHistoryTurn->cell_value = $aiTurn['cell_value'];
        if ($gameHistoryTurn->save()) {
            return $aiTurn;
        }
    }
}
