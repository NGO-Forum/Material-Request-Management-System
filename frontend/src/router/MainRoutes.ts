// src/router/MainRoutes.ts
const MainRoutes = {
  path: '/main',
  meta: { requiresAuth: true },
  component: () => import('@/layouts/full/FullLayout.vue'),
  redirect: '/main/dashboard/default',
  children: [
    {
      name: 'Default',                          
      path: 'dashboard/default',                
      component: () => import('@/views/dashboards/default/DefaultDashboard.vue')
    },
    {
      name: 'Users',
      path: 'users',
      component: () => import('@/views/users/UsersView.vue')
    },
    {
      name: 'Roles',
      path: 'roles',
      component: () => import('@/views/roles/RolesView.vue')
    },
    {
      name: 'Departments',
      path: 'departments',
      component: () => import('@/views/departments/DepartmentsView.vue')
    },
  ]
};

export default MainRoutes;