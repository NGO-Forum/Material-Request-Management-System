const MainRoutes = {
  path: '/main',
  meta: {
    requiresAuth: true
  },
  redirect: '/main/dashboard/default',
  component: () => import('@/layouts/full/FullLayout.vue'),
  children: [
    {
      name: 'LandingPage',
      path: '/',
      component: () => import('@/views/dashboards/default/DefaultDashboard.vue')
    },
    {
      name: 'Default',
      path: '/main/dashboard/default',
      component: () => import('@/views/dashboards/default/DefaultDashboard.vue')
    },
    {
    name: 'Users',
    path: '/main/users',  
    component: () => import('@/views/users/UsersView.vue')
  },
  {
    name: 'Roles',
    path: '/main/roles',
    component: () => import('@/views/roles/RolesView.vue')
  },
  {
      name: 'Departments',
      path: '/main/departments',
      component: () => import('@/views/departments/DepartmentsView.vue')
    },
  ]
};

export default MainRoutes;
