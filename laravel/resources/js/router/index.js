import { createRouter, createWebHistory } from 'vue-router';
import Login from '../pages/views/Login.vue';
import Schedule from '../pages/views/admin/Schedule.vue';
import Create from '../pages/views/admin/Create.vue';
import Export from '../pages/views/admin/Export.vue';
import AdminAccount from '../pages/views/admin/AdminAccount.vue';
import ErrorLog from '../pages/views/admin/ErrorLog.vue';
import ModifySchedule from '../pages/views/admin/ModifySchedule.vue';
import Manage from '../pages/views/admin/Manage.vue';
import Dashboard from '../pages/views/admin/Dashboard.vue';
import FacultyDashboard from '../pages/views/faculty/Dashboard.vue';
import FacultyAccount from '../pages/views/faculty/FacultyAccount.vue';

const routes = [
  { path: '/login', component: Login },
  { path: '/schedule', component: Schedule, meta: { roles: ['admin', 'superadmin'] } },
  { path: '/create', component: Create, meta: { roles: ['admin', 'superadmin'] } },
  { path: '/export', component: Export, meta: { roles: ['admin', 'superadmin'] } },
  { path: '/admin-account', component: AdminAccount, meta: { roles: ['admin', 'superadmin'] } },
  { path: '/error-log', component: ErrorLog, meta: { roles: ['admin', 'superadmin'] } },
  { path: '/schedule/modify', component: ModifySchedule, meta: { roles: ['admin', 'superadmin'] } },
  { path: '/manage', component: Manage, meta: { roles: ['admin', 'superadmin'] } },
  { path: '/dashboard', component: Dashboard, meta: { roles: ['admin', 'superadmin'] } },
  { path: '/faculty/dashboard', component: FacultyDashboard, meta: { roles: ['faculty'] } },
  { path: '/faculty-account', component: FacultyAccount, meta: { roles: ['faculty'] } },
  {
    path: '/faculty/calendar',
    name: 'faculty-calendar',
    component: () => import('../pages/views/faculty/Calendar.vue'),
    meta: { roles: ['faculty'] }
  },
  { path: '/', redirect: '/dashboard' },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Route guard
router.beforeEach((to, from, next) => {
  const publicPages = ['/login'];
  const authRequired = !publicPages.includes(to.path);
  const isAuthenticated = !!localStorage.getItem('authToken');
  const userType = localStorage.getItem('userType');

  if (authRequired && !isAuthenticated) {
    return next('/login');
  }

  if (to.meta.roles && !to.meta.roles.includes(userType)) {
    if (userType === 'admin' || userType === 'superadmin') {
      return next('/dashboard');
    } else if (userType === 'faculty') {
      return next('/faculty/dashboard');
    } else {
      return next('/login');
    }
  }

  next();
});

export default router;