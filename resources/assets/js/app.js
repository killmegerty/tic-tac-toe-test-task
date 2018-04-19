import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

import App from './views/App';
import Index from './views/Index';
import GameIndex from './views/GameIndex';
import GameAi from './views/GameAi';

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            name: 'index',
            component: Index
        },
        {
            path: '/',
            name: 'game-index',
            component: GameIndex
        },
        {
            path: '/game-ai',
            name: 'game-ai',
            component: GameAi
        },
    ],
});

const app = new Vue({
    el: '#app',
    components: { App },
    router,
});
