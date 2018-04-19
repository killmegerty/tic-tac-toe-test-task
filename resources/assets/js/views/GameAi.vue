<template>
    <div>
        <home-button-component></home-button-component>
        <div class="row">
            <div class="col-md-12 text-center">
                <h3>Game VS AI</h3>
            </div>
        </div>
        <tic-tac-toe-component></tic-tac-toe-component>
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
