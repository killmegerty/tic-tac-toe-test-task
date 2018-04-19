require('./bootstrap');

import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

import App from './views/App';
import Index from './views/Index';
import GameHuman from './views/GameHuman';
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
            path: '/game-ai',
            name: 'game-ai',
            component: GameAi
        },
        {
            path: '/game-human/:gameSessionUuid?',
            name: 'game-human',
            component: GameHuman
        },
    ],
});

const app = new Vue({
    el: '#app',
    components: { App },
    router,
});
