<template>
    <div>
        <div class="row">
            <div class="col-md-6">
                <p>
                    Game session: {{ gameSessionUuid }}
                </p>
            </div>
        </div>
        <div class="row game-grid-container">
            <div class="col-xs-8">
                <div class="table-responsive">
                  <table class="table table-bordered">
                      <tbody>
                          <tr v-for="row in 3">
                              <td
                                 v-on:click="onCellClick($event, (cell + (3 * (row - 1)) - 1))"
                                 v-bind:id="'cell-' + (cell + (3 * (row - 1)) - 1)"
                                 v-for="cell in 3" class="game-cell">
                                 <div class="content"></div>
                              </td>
                          </tr>
                      </tbody>
                  </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import uuid from 'uuid';

    export default {
        data: () => {
            return {
                cellIdCounter: 0,
                gameSessionUuid: uuid.v4(),
                playerCellValue: 'X'
            }
        },
        methods: {
            onCellClick(e, cellIndex) {
                console.log('cell clicked', e.target.id, cellIndex);
                e.target.innerHTML = 'X';

                axios.post('/api/game-cell-click',{
                    game_session_uuid: this.gameSessionUuid,
                    cell_index: cellIndex,
                    cell_value: this.playerCellValue
                })
                .then(response => {
                    // computer turn
                    console.log(response.data);
                })
                .catch(e => {
                    console.log('error occured', e);
                });
            }
        }
    }
</script>
