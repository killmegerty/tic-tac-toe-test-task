<template>
    <div>
        <div class="row">
            <div class="col-md-2">
                <button v-on:click="onHomeBtnClick" type="button" class="btn btn-default btn-lg btn-block">Home</button>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data: () => {
            return {
            }
        },
        methods: {
            onHomeBtnClick() {
                this.$router.push('/');
            }
        }
    }
</script>
