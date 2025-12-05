// src/router/PublicRoutes.ts
const PublicRoutes = {
  path: '/',
  component: () => import('@/layouts/blank/BlankLayout.vue'),
  meta: { requiresAuth: false },
  children: [
    {
      name: 'Authentication',
      path: '/login',
      component: () => import('@/views/authentication/LoginPage.vue'),
    },
    {
      name: 'Register',
      path: '/register',
      component: () => import('@/views/authentication/auth/RegisterPage.vue'),
    },
    {
      name: 'Forgot Password',
      path: '/forgot-password',
      component: () => import('@/views/authentication/auth/ForgotPasswordPage.vue'),
    },
    {
      path: ':pathMatch(.*)*',
      redirect: '/login',
    },
  ],
};

export default PublicRoutes;