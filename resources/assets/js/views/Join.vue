<template>
    <div>
        <home-button-component></home-button-component>
        <div class="row">
            <div class="col-md-12 text-center">
                <h3>Join to game</h3>
            </div>
        </div>
        <div class="row mt-15">
            <div class="col-md-6">
                <div class="form-group">
                   <label for="gameSessionUUID">Game Session UUID</label>
                   <input v-on:keyup="onEnterSubmit" v-model="gameSessionUUID" type="text" class="form-control" id="gameSessionUUID" aria-describedby="emailHelp" placeholder="ex. bfa132b2-0a65-4f73-84de-98610ed40fa7">
                </div>
                <button type="button" class="btn btn-default" v-on:click="onJoinBtnClick">Join</button>
            </div>
        </div>
    </div>
</template>


<script>
    import HomeButtonComponent from '../components/HomeButtonComponent.vue';

    export default {
        data: () => {
            return {
                gameSessionUUID: ''
            }
        },
        methods: {
            onEnterSubmit(e) {
                if (e.keyCode === 13) {
                    this.onJoinBtnClick();
                }
            },
            onJoinBtnClick() {
                this.$router.push('/game-human/' + this.gameSessionUUID);
            }
        },
        components: {
            HomeButtonComponent
        }
    }
</script>
