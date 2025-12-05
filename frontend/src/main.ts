// src/main.ts
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router'; // ← Fixed: default import
import vuetify from './plugins/vuetify';
import '@/scss/style.scss';
import { PerfectScrollbarPlugin } from 'vue3-perfect-scrollbar';
import VueApexCharts from 'vue3-apexcharts';
import VueTablerIcons from 'vue-tabler-icons';
import print from 'vue3-print-nb';

// Remove fake backend if using real Laravel API
// import { fakeBackend } from '@/utils/helpers/fake-backend';

import { useAuthStore } from '@/stores/auth'; // ← Import here

const app = createApp(App);

// Install plugins
app.use(createPinia());        // ← Pinia must be installed FIRST
app.use(router);
app.use(vuetify);
app.use(PerfectScrollbarPlugin);
app.use(VueTablerIcons);
app.use(VueApexCharts);
app.use(print);

// fakeBackend(); // ← Remove this line if using real backend

// Now it's safe to use useAuthStore()
const authStore = useAuthStore();
authStore.initializeAuth(); // ← Initialize auth after Pinia

app.mount('#app');