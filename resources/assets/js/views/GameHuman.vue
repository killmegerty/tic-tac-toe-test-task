<template>
    <div>
        <home-button-component></home-button-component>
        <div class="row">
            <div class="col-md-12 text-center">
                <h3>Game VS Human</h3>
            </div>
        </div>
        <tic-tac-toe-component gameMode="vs_human"></tic-tac-toe-component>
    </div>
</template>


<script>
    import HomeButtonComponent from '../components/HomeButtonComponent.vue';
    import TicTacToeComponent from '../components/TicTacToeComponent.vue';

    export default {
        data: () => {
            return {
            }
        },
        components: {
            HomeButtonComponent,
            TicTacToeComponent
        }
    }
</script>
