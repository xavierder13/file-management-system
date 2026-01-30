import Vue from 'vue';
import Router from 'vue-router';
import store from './store'; // import your Vuex store
import apiBaseUrl from './apiBaseUrl';
import axiosMain from './axiosMain';

import Home from './views/Home.vue';
import Login from './auth/Login.vue';
import Dashboard from './views/dashboard/Dashboard.vue';
import UserIndex from './views/user/UserIndex.vue';
import UserCreate from './views/user/UserCreate.vue';
import UserProfile from './views/user/UserProfile.vue';
import Permission from './views/permission/PermissionIndex.vue';
import RoleIndex from './views/role/RoleIndex.vue';
import RoleCreate from './views/role/RoleCreate.vue';
import RoleView from './views/role/RoleView.vue';
import BranchIndex from './views/branch/BranchIndex.vue';
import CompanyIndex from './views/company/CompanyIndex.vue';
import PositionIndex from './views/position/PositionIndex.vue';
import DepartmentIndex from './views/department/DepartmentIndex.vue';
import DivisionIndex from './views/division/DivisionIndex.vue';
import FileExplorer from './views/file_management/FileExplorer.vue';
import FileUpload from './views/file_management/FileUpload.vue';
import ActivityLogs from './views/activity_logs/ActivityLogs.vue';
import PageNotFound from './404/PageNotFound.vue';
import Unauthorize from './401/Unauthorize.vue';

Vue.use(Router);

const routes = [
  {
    path: '/',
    name: 'home',
    component: Home,
    children: [
      {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: { requiresAuth: true, permission: 'dashboard' }
      },
      {
        path: '/user/index',
        name: 'user.index',
        component: UserIndex,
        meta: { requiresAuth: true, permission: 'user-list' }
      },
      {
        path: '/user/create',
        name: 'user.create',
        component: UserCreate,
        meta: { requiresAuth: true, permission: 'user-create' }
      },
      {
        path: '/user/profile',
        name: 'user.profile',
        component: UserProfile
      },
      {
        path: '/permission/index',
        name: 'permission.index',
        component: Permission,
        meta: { requiresAuth: true, permission: 'permission-list' }
      },
      {
        path: '/role/index',
        name: 'role.index',
        component: RoleIndex,
        meta: { requiresAuth: true, permission: 'role-list' }
      },
      {
        path: '/role/create',
        name: 'role.create',
        component: RoleCreate,
        meta: { requiresAuth: true, permission: 'role-create' }
      },
      {
        path: '/role/view/:roleid',
        name: 'role.view',
        component: RoleView,
        meta: { requiresAuth: true, permission: 'role-edit' }
      },
      {
        path: '/branch/index',
        name: 'branch.index',
        component: BranchIndex,
        meta: { requiresAuth: true, permission: 'branch-list' }
      },
      {
        path: '/company/index',
        name: 'company.index',
        component: CompanyIndex,
        meta: { requiresAuth: true, permission: 'company-list' }
      },
      {
        path: '/position/index',
        name: 'position.index',
        component: PositionIndex,
        meta: { requiresAuth: true, permission: 'position-list' }
      },
      {
        path: '/department/index',
        name: 'department.index',
        component: DepartmentIndex,
        meta: { requiresAuth: true, permission: 'department-list' }
      },
      {
        path: '/division/index',
        name: 'division.index',
        component: DivisionIndex,
        meta: { requiresAuth: true, permission: 'division-list' }
      },
      {
        path: '/file-explorer',
        name: 'file.explorer',
        component: FileExplorer,
        meta: { requiresAuth: true, permission: 'file-list' }
      },
      {
        path: '/activity_logs',
        name: 'activity_logs',
        component: ActivityLogs,
        meta: { requiresAuth: true, permission: 'activity-logs' }
      },
      {
        path: '/unauthorize',
        name: 'unauthorize',
        component: Unauthorize,
        meta: { requiresAuth: true, permission: '' }
      }
    ],
  },
  {
    path: '/login',
    name: 'login',
    component: Login, 
    meta: { public: true, onlyGuest: true }
  },
  {
    path: '*',
    component: PageNotFound,
  },
  {
    path: '/file-upload/:token',
    name: 'file.upload',
    component: FileUpload,
    meta: { public: true },
    beforeEnter: async (to, from, next) => {
      const qrToken = to.params.token;

      try {
        // Await the API call
        const response = await axiosMain.get(`/api/validate-qr-token/${qrToken}`);
        
        // Token is valid, allow the page to load
        next();
      } catch (error) {
        // Hide backend console errors
        const status = error.response?.status;

        if (status === 401) {
          window.location.href = apiBaseUrl + '/401';
        } else if (status === 404) {
          window.location.href = apiBaseUrl + '/404';
        } else {
          window.location.href = apiBaseUrl + '/500';
        }
      }
    }
  },
];

const router = new Router({
  routes: routes,
  mode: 'history',
});

// ✅ Global auth + permission guard
router.beforeEach(async (to, from, next) => {
  const token = localStorage.getItem('access_token');
  
  if (to.meta.public) {
    // Only redirect logged-in users if the page is meant for guests
    if (token && to.meta.onlyGuest) {
      return next({ name: 'dashboard' });
    }
    return next(); // allow public route
  }

  // Protected route → requires auth
  if (to.meta.requiresAuth && !token) {
    return next('/login');
  }

  // load user + roles/permissions if not loaded
  if (!store.state.auth.isLoaded) {
    await store.dispatch('auth/getUser');
  }

  if (to.meta.permission && !store.getters['auth/hasPermission'](to.meta.permission)) {
    return next({ name: 'unauthorize' });
  }

  if (to.meta.role && !store.getters['auth/hasRole'](to.meta.role)) {
    return next({ name: 'unauthorize' });
  }

  next();
});

export default router;