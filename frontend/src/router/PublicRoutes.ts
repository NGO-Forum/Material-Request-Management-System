// src/router/PublicRoutes.ts
const PublicRoutes = {
  path: '/',
  component: () => import('@/layouts/blank/BlankLayout.vue'),
  meta: {
    requiresAuth: false,
  },
  children: [
    {
      name: 'Authentication',
      path: '/login',
      component: () => import('@/views/authentication/LoginPage.vue'),
    },
    {
      name: 'Login',
      path: '/login1',
      component: () => import('@/views/authentication/auth/LoginPage.vue'),
    },
    {
      name: 'Register',
      path: '/register',
      component: () => import('@/views/authentication/auth/RegisterPage.vue'),
    },
    // ← THIS COMMA WAS MISSING → BROKE EVERYTHING!
    {
      name: 'Forgot Password',
      path: '/forgot-password',
      component: () => import('@/views/authentication/auth/ForgotPasswordPage.vue'),
    },
    {
      name: 'Error 404',
      path: '/error',
      component: () => import('@/views/pages/maintenance/error/Error404Page.vue'),
    },
    // Optional: catch-all for blank layout
    {
      path: ':pathMatch(.*)*',
      redirect: '/login',
    },
  ],
};

export default PublicRoutes;