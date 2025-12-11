// src/main.ts
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import vuetify from './plugins/vuetify'
import '@/scss/style.scss'

import VueTablerIcons from 'vue-tabler-icons'
import VueApexCharts from 'vue3-apexcharts'
import print from 'vue3-print-nb'

import { useAuthStore } from '@/stores/auth'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(vuetify)
app.use(VueTablerIcons)
app.use(VueApexCharts)
app.use(print)

// CRITICAL: Initialize auth BEFORE mounting
const authStore = useAuthStore()
authStore.initializeAuth()

app.mount('#app')