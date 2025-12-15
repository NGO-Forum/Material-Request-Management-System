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

  if (!authStore.user && localStorage.getItem('token')) {
    authStore.initializeAuth();
    await authStore.fetchUser();
  }

  const publicPages = ['/login', '/register', '/forgot-password'];
  const authRequired = !publicPages.includes(to.path);

  // Redirect if not logged in
  if (authRequired && !authStore.isLoggedIn) {
    authStore.returnUrl = to.fullPath;
    return next('/login');
  }

  // Prevent Employees / Managers from accessing Admin pages
  const role = authStore.userRole;

  const adminPages = ['/main/dashboard/default', '/main/users', '/main/roles', '/main/departments', '/main/categories/list', '/main/materials/list', '/main/inventory/reports'];
  if ((role === 'Employee' || role === 'Manager') && adminPages.includes(to.path)) {
    return next('/main/requests/list');
  }

  // Redirect logged-in users trying to access public pages
  if (authStore.isLoggedIn && publicPages.includes(to.path)) {
    authStore.redirectAfterLogin();
    return;
  }

  next();
});

export default router;
