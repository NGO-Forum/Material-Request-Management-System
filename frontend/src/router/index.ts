// src/router/index.ts
import { createRouter, createWebHistory } from 'vue-router';
import MainRoutes from './MainRoutes';
import PublicRoutes from './PublicRoutes';
import { useAuthStore } from '@/stores/auth';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/login' },
    MainRoutes,
    PublicRoutes,
    { path: '/:pathMatch(.*)*', component: () => import('@/views/pages/maintenance/error/Error404Page.vue') },
  ],
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  // Initialize on first load
  if (!authStore.user && localStorage.getItem('token')) {
    authStore.initializeAuth();
    await authStore.fetchUser();
  }

  const publicPages = ['/login', '/register', '/forgot-password'];
  const authRequired = !publicPages.includes(to.path);

  if (authRequired && !authStore.isLoggedIn) {
    authStore.returnUrl = to.fullPath;
    return next('/login');
  }

  if (authStore.isLoggedIn && publicPages.includes(to.path)) {
    return next('/main/dashboard/default');
  }

  next();
});

export default router; // ← Must be default export!