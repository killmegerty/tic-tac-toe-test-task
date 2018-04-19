<template>
    <div>
        <div class="row">
            <div class="col-md-6">
                <p>
                    Game session: {{ gameObj.gameSessionUuid }}
                </p>
            </div>
        </div>
        <div class="row" v-show="gameObj.isGameFinished">
            <div class="col-md-12 text-center">
                <div v-show="!gameObj.isDraw">
                    <h2>Winner, winner chicken dinner</h2>
                    <h3>Player '{{ gameObj.winner }}' win</h3>
                </div>
                <div v-show="gameObj.isDraw">
                    <h2>Draw!</h2>
                </div>
                <div class="row mb-15">
                    <div class="col-md-3 col-center">
                        <button v-on:click="onRestartGameBtnClick" type="button" class="btn btn-default btn-lg btn-block">Restart</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="game-grid-container mb-15">
            <div class="row" v-for="row in 3">
                <div class="col-md-12">
                    <div class="square-box game-cell"
                         v-on:click="onCellClick($event, (cell + (3 * (row - 1)) - 1))"
                         v-bind:id="'cell-' + (cell + (3 * (row - 1)) - 1)"
                         v-for="cell in 3"
                         ref="gameCells">
                     </div>
                </div>
            </div>
        </div>



    </div>
</template>

<style media="screen">

</style>

<script>
    import axios from 'axios';
    import uuid from 'uuid';

    export default {
        data: () => {
            return {
                cellIdCounter: 0,
                playerCellValue: 'X',
                gameObj: {
                    gameSessionUuid: uuid.v4(),
                    gameBoard: [null, null, null, null, null, null, null, null, null],
                    loadingData: false,
                    isGameFinished: false,
                    winner: null,
                    isDraw: false
                }
            }
        },
        methods: {
            _initGameObj() {
                this.gameObj = {
                    gameSessionUuid: uuid.v4(),
                    gameBoard: [null, null, null, null, null, null, null, null, null],
                    isGameFinished: false,
                    winner: null,
                    isDraw: false
                };
            },
            _clearGameBoardView() {
                for (let gameCell of this.$refs.gameCells) {
                    gameCell.classList.remove('x-cell');
                    gameCell.classList.remove('o-cell');
                }
            },
            onRestartGameBtnClick() {
                this._clearGameBoardView();
                this._initGameObj();
            },
            onCellClick(e, cellIndex) {
                if (this.gameObj.loadingData || this.gameObj.isGameFinished) {
                    return;
                }
                if (!this._doTurn(cellIndex, this.playerCellValue)) {
                    return;
                }

                this.gameObj.loadingData = true;
                axios.post('/api/game-cell-click', {
                    game_session_uuid: this.gameObj.gameSessionUuid,
                    cell_index: cellIndex,
                    cell_value: this.playerCellValue
                })
                .then(response => {
                    // computer turn
                    this._aiTurnHandler(response.data);
                    this.gameObj.loadingData = false;
                })
                .catch(e => {
                    console.log('error occured', e);
                    this.gameObj.loadingData = false;
                });
            },
            _doTurn(cellIndex, cellValue) {
                if (this.gameObj.gameBoard[cellIndex] !== null) {
                    return false;
                }

                this.gameObj.gameBoard[cellIndex] = cellValue;
                // draw on screen
                if (cellValue == 'X') {
                    this.$refs.gameCells[cellIndex].classList.add('x-cell');
                } else {
                    this.$refs.gameCells[cellIndex].classList.add('o-cell');
                }

                this._checkWinner();
                return true;
            },
            _checkWinner() {
                if (
                    (this.gameObj.gameBoard[0] == 'O' && this.gameObj.gameBoard[1] == 'O' && this.gameObj.gameBoard[2] == 'O') ||
                    (this.gameObj.gameBoard[0] == 'O' && this.gameObj.gameBoard[3] == 'O' && this.gameObj.gameBoard[6] == 'O') ||
                    (this.gameObj.gameBoard[0] == 'O' && this.gameObj.gameBoard[4] == 'O' && this.gameObj.gameBoard[8] == 'O') ||
                    (this.gameObj.gameBoard[2] == 'O' && this.gameObj.gameBoard[5] == 'O' && this.gameObj.gameBoard[8] == 'O') ||
                    (this.gameObj.gameBoard[6] == 'O' && this.gameObj.gameBoard[7] == 'O' && this.gameObj.gameBoard[8] == 'O') ||
                    (this.gameObj.gameBoard[6] == 'O' && this.gameObj.gameBoard[4] == 'O' && this.gameObj.gameBoard[2] == 'O') ||
                    (this.gameObj.gameBoard[1] == 'O' && this.gameObj.gameBoard[4] == 'O' && this.gameObj.gameBoard[7] == 'O') ||
                    (this.gameObj.gameBoard[3] == 'O' && this.gameObj.gameBoard[4] == 'O' && this.gameObj.gameBoard[5] == 'O')
                ) {
                    this.gameObj.winner = 'O';
                    this.gameObj.isGameFinished = true;
                    return;
                } else if (
                    (this.gameObj.gameBoard[0] == 'X' && this.gameObj.gameBoard[1] == 'X' && this.gameObj.gameBoard[2] == 'X') ||
                    (this.gameObj.gameBoard[0] == 'X' && this.gameObj.gameBoard[3] == 'X' && this.gameObj.gameBoard[6] == 'X') ||
                    (this.gameObj.gameBoard[0] == 'X' && this.gameObj.gameBoard[4] == 'X' && this.gameObj.gameBoard[8] == 'X') ||
                    (this.gameObj.gameBoard[2] == 'X' && this.gameObj.gameBoard[5] == 'X' && this.gameObj.gameBoard[8] == 'X') ||
                    (this.gameObj.gameBoard[6] == 'X' && this.gameObj.gameBoard[7] == 'X' && this.gameObj.gameBoard[8] == 'X') ||
                    (this.gameObj.gameBoard[6] == 'X' && this.gameObj.gameBoard[4] == 'X' && this.gameObj.gameBoard[2] == 'X') ||
                    (this.gameObj.gameBoard[1] == 'X' && this.gameObj.gameBoard[4] == 'X' && this.gameObj.gameBoard[7] == 'X') ||
                    (this.gameObj.gameBoard[3] == 'X' && this.gameObj.gameBoard[4] == 'X' && this.gameObj.gameBoard[5] == 'X')
                ) {
                    this.gameObj.winner = 'X';
                    this.gameObj.isGameFinished = true;
                    return;
                }

                let isCellsHaveNull = false;
                for (let cellVal of this.gameObj.gameBoard) {
                    if (cellVal === null) {
                        isCellsHaveNull = true;
                        break;
                    }
                }
                if (!isCellsHaveNull) {
                    this.gameObj.winner = null;
                    this.gameObj.isDraw = true;
                    this.gameObj.isGameFinished = true;
                }
            },
            _aiTurnHandler(data) {
                if (data.error !== undefined) {
                    console.log('Error occured while receiving AI turn:', data.error);
                    return;
                } else if (data.aiTurn === undefined) {
                    return;
                }
                console.log(data);

                this._doTurn(data.aiTurn.cell_index, data.aiTurn.cell_value);

            }
        }
    }
</script>
