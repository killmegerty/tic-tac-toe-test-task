<template>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p>
                    Game session: {{ gameSessionHash }}
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
                                 v-on:click="onCellClick"
                                 v-bind:id="'cell-' + row + '-' + cell"
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
                gameSessionHash: uuid.v4()
            }
        },
        methods: {
            onCellClick(e) {
                console.log('cell clicked', e.target.id);
                e.target.innerHTML = 'X';

                axios.get('https://randomuser.me/api/')
                .then(response => {
                    console.log(response.data);
                })
                .catch(e => {
                    console.log('error occured', e);
                });
            }
        }
    }
</script>
