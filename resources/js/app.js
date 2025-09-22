import './bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import axios from 'axios';

import App from './App.vue';
import Upload from './pages/Upload.vue';
import Status from './pages/Status.vue';

window.axios = axios;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

const routes = [
    { path: '/', redirect: '/app/envio' },
    { path: '/app/envio', name: 'upload', component: Upload },
    { path: '/app/status', name: 'status', component: Status },
    { path: '/app/status/:id', name: 'status-detail', component: Status, props: true }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});
const app = createApp(App);
app.use(router);
app.mount('#app');
