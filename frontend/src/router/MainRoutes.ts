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
    {
      name: 'Categories',            
      path: 'categories/list',       
      component: () => import('@/views/categories/CategoriesView.vue') 
    },
    {
      name: 'Materials',
      path: 'materials/list',
      component: () => import('@/views/materials/MaterialsView.vue')
    },
    {
      name: 'Profile',
      path: 'profile',
      component: () => import('@/views/profile/ProfileView.vue')
    },
    {
      name: 'Inventory Reports',
      path: 'inventory/reports',
      component: () => import('@/views/inventory/InventoryReportsView.vue')
    },
    // Add these to your MainRoutes children array
    {
      name: 'Request List',
      path: 'requests/list',
      component: () => import('@/views/requests/RequestListView.vue')
    },
    {
      name: 'Request Create',
      path: 'requests/create',
      component: () => import('@/views/requests/RequestCreateView.vue')
    },
    {
      name: 'Request Detail',
      path: 'requests/:id',
      component: () => import('@/views/requests/RequestDetailView.vue')
    },
    {
      name: 'Request Tracking',
      path: 'requests/tracking',
      component: () => import('@/views/requests/RequestTrackingView.vue')
    },
    {
      name: 'Error 404',
      path: 'error',
      component: () => import('@/views/pages/maintenance/error/Error404Page.vue')
    }

  ]
};

export default MainRoutes;