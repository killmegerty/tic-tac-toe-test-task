<template>
    <div>
        <div class="row" v-show="!gameLoaded">
            <div class="col-md-6">
                <p>Loading...</p>
            </div>
        </div>
        <div v-show="gameLoaded">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        Game Session UUID: {{ gameObj.gameSessionUuid }}
                    </p>
                    <div v-show="qrCode.length && currentGameMode == 'vs_human' && whoAmI == 'host'">
                        <p>
                            Use QR code for inviting your opponent:
                        </p>
                        <p>
                            <img v-bind:src="qrCode" alt="" />
                        </p>
                        <p>
                            Direct invite link: <a v-bind:href="directLink" target="_blank">{{ directLink }}</a>
                        </p>
                    </div>
                    <p>
                        My Role: {{ whoAmI }}
                    </p>
                    <p>
                        Whose Turn: {{ whoseTurn }}
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
                    <div class="row mb-15" v-show="whoAmI == 'host'">
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




    </div>
</template>

<script>
    import axios from 'axios';
    import uuid from 'uuid';
    import QRCode from 'qrcode';

    function getDefaultData() {
        return {
            whoAmI: 'host', // 'host' | 'opponent'
            whoseTurn: 'host', // 'host' | opponent
            gameLoaded: false,
            availableGameModes: ['vs_ai', 'vs_human'],
            currentGameMode: 'vs_ai',
            cellIdCounter: 0,
            playerCellValue: {
                host: 'X',
                opponent: 'O'
            },
            gameObj: {
                gameSessionUuid: uuid.v4(),
                gameBoard: [null, null, null, null, null, null, null, null, null],
                loadingData: false,
                isGameFinished: false,
                winner: null,
                isDraw: false
            },
            qrCode: '',
            directLink: ''
        }
    }

    export default {
        props: ['gameMode'], // 'vs_ai' | 'vs_human'
        data: () => {
            return getDefaultData();
        },
        created: function() {
            this._initFirstLoadGame();

            // init channel listenting
            this._initChannelListening();
        },
        methods: {
            _redrawGameBoard(gameBoard) {
                this._clearGameBoardView();

                for (let i = 0; i < gameBoard.length; i++) {
                    // draw on screen
                    if (gameBoard[i] == 'X') {
                        this.$refs.gameCells[i].classList.add('x-cell');
                    } else if (gameBoard[i] == 'O') {
                        this.$refs.gameCells[i].classList.add('o-cell');
                    }
                }
            },
            _initGameBoardByGameHistory(gameHistory) {
                for (let gameHistoryEntity of gameHistory) {
                    this.gameObj.gameBoard[gameHistoryEntity.cell_index] = gameHistoryEntity.cell_value;
                }
            },
            _initFirstLoadGame() {
                if (this.availableGameModes.indexOf(this.gameMode) >= 0) {
                    this.currentGameMode = this.gameMode;
                }

                // init game if session uuid provided
                if (this.$route.params.gameSessionUuid !== undefined &&
                    this.$route.params.gameSessionUuid !== null
                ) {
                    // opponent logic : validate gameSessionUuid on server
                    this.gameObj.gameSessionUuid = this.$route.params.gameSessionUuid;

                    axios.get('/api/game', {
                        params: {
                            game_session_uuid: this.gameObj.gameSessionUuid,
                        }
                    })
                    .then(response => {
                        if (!response.data ||
                            (response.data.game_history !== undefined &&
                            response.data.game_history.length > 1)
                        ) {
                            this.$router.push('/');
                        }
                        // prevent joining game vs ai
                        if (response.data.mode == 'vs_ai') {
                            this.$router.push('/');
                        }
                        if (response.data.game_history.length % 2 == 0) {
                            this.whoseTurn = 'host';
                        } else {
                            this.whoseTurn = 'opponent';
                        }
                        this._initGameBoardByGameHistory(response.data.game_history);
                        this._redrawGameBoard(this.gameObj.gameBoard);
                        this.whoAmI = 'opponent';
                        this.gameLoaded = true;
                    })
                    .catch(e => {
                        console.log('error occured', e);
                        this.gameLoaded = true;
                        this.$router.push('/');
                    });
                } else {
                    // host : save game in server DB
                    this._createNewGameRequest(this.gameObj.gameSessionUuid)
                        .then(response => {
                            this.whoAmI = 'host';
                            this.gameLoaded = true;
                        })
                        .catch(e => {
                            console.log('error occured', e);
                            this.whoAmI = 'host';
                            this.gameLoaded = true;
                        });
                }
                this._initQRCodeAndDirectLink();
            },
            _createNewGameRequest(gameSessionUuid) {
                return axios.post('/api/game', {
                    game_session_uuid: this.gameObj.gameSessionUuid,
                    mode: this.currentGameMode
                });
            },
            _initChannelListening() {
                if (this.currentGameMode == 'vs_human') {
                    Echo.channel('game.' + this.gameObj.gameSessionUuid)
                        .listen('GameHistoryCreated', (data) => {
                            this._doTurn(data.gameHistory.cell_index, data.gameHistory.cell_value);
                        });
                }
            },
            _toggleWhoseTurn() {
                if (this.whoseTurn === 'host') {
                    this.whoseTurn = 'opponent';
                } else {
                    this.whoseTurn = 'host';
                }
            },
            _initDefaultData() {
                Object.assign(this.$data, getDefaultData());
                if (this.availableGameModes.indexOf(this.gameMode) >= 0) {
                    this.currentGameMode = this.gameMode;
                }
            },
            _clearGameBoardView() {
                for (let gameCell of this.$refs.gameCells) {
                    gameCell.classList.remove('x-cell');
                    gameCell.classList.remove('o-cell');
                }
            },
            _initQRCodeAndDirectLink() {
                this.directLink = location.protocol + '//' + location.host + '/game-human/' + this.gameObj.gameSessionUuid;
                QRCode.toDataURL(this.directLink)
                    .then(url => {
                        this.qrCode = url;
                    })
                    .catch(err => {
                        console.error(err);
                    });
            },
            onRestartGameBtnClick() {
                this.gameLoaded = false;
                this._clearGameBoardView();
                this._initDefaultData();
                this._initQRCodeAndDirectLink();
                this._createNewGameRequest(this.gameObj.gameSessionUuid)
                    .then(response => {
                        this.gameLoaded = true;
                    })
                    .catch(e => {
                        this.gameLoaded = true;
                    });
            },
            onCellClick(e, cellIndex) {
                if (this.gameObj.loadingData || this.gameObj.isGameFinished) {
                    return;
                }
                if (this.whoAmI !== this.whoseTurn) {
                    return;
                }
                if (!this._doTurn(cellIndex, this.playerCellValue[this.whoAmI])) {
                    return;
                }

                this.gameObj.loadingData = true;
                axios.post('/api/game-history', {
                    game_session_uuid: this.gameObj.gameSessionUuid,
                    cell_index: cellIndex,
                    cell_value: this.playerCellValue[this.whoAmI]
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
                this._redrawGameBoard(this.gameObj.gameBoard);
                this._toggleWhoseTurn();

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

                this._doTurn(data.aiTurn.cell_index, data.aiTurn.cell_value);

            }
        }
    }
</script>
