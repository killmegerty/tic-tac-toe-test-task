<?php

use Illuminate\Http\Request;
use App\Game;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


// Route::post('/game-cell-click', 'GameController@cellClick');

Route::post('/game-history', 'GameController@cellClickHandler');

Route::get('/game', function (Request $request) {
    return Game::where('game_session_uuid', $request->game_session_uuid)
               ->with('GameHistory')
               ->first();
});

Route::post('/game', function (Request $request) {
    $game = new Game();
    $game->game_session_uuid = $request->game_session_uuid;
    $game->mode = $request->mode;
    $game->save();

    return $game;
});
