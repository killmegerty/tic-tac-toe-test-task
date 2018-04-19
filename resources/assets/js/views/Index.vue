<template>
    <div>
        <div class="row">
            <div class="col-md-6 col-center">
                <div class="row mt-5" v-for="menuItem in menuItems">
                    <div class="col-md-12">
                        <button v-on:click="onMenuBtnClick(menuItem)" type="button" class="btn btn-default btn-lg btn-block">{{ menuItem.title }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data: () => {
            return {
                menuItems: [],
                menu: [
                    {
                        title: 'vs AI',
                        link: '/game-ai',
                        childs: null
                    },
                    {
                        title: 'vs Human',
                        link: null,
                        childs: [
                            {
                                title: 'Host',
                                link: '/game-human',
                                childs: null
                            },
                            {
                                title: 'Join',
                                link: '/join',
                                childs: null
                            }
                        ]
                    }
                ]
            }
        },
        created: function () {
            this.menuItems = this.menu;
        },
        methods: {
            onMenuBtnClick(menuItem) {
                if (menuItem.childs !== null) {
                    this.menuItems = menuItem.childs;
                }
                if (menuItem.link !== null) {
                    this.$router.push(menuItem.link);
                }
            }
        }
    }
</script>
