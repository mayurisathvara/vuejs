import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Layouts
import AuthLayout from '@/layouts/AuthLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'

// Pages
import Login from '@/pages/Login.vue'
import Register from '@/pages/Register.vue'
import Dashboard from '@/pages/Dashboard.vue'
import Users from '@/pages/Users.vue'
import AssignSims from '@/pages/AssignSims.vue'
import Organizations from '@/pages/Organizations.vue'
import OrganizationSettings from '@/pages/OrganizationSettings.vue'
import OrganizationDateFormatSettings from '@/pages/OrganizationDateFormatSettings.vue'
import Teams from '@/pages/Teams.vue'
import Sims from '@/pages/Sims.vue'
import Profile from '@/pages/Profile.vue'
import ChangePassword from '@/pages/ChangePassword.vue'
import CallReports from '@/pages/CallReports.vue'
import SummaryReport from '@/pages/SummaryReport.vue'

const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/login',
    component: AuthLayout,
    children: [
      {
        path: '',
        name: 'Login',
        component: Login,
        meta: { requiresGuest: true }
      }
    ]
  },
  {
    path: '/register',
    component: AuthLayout,
    children: [
      {
        path: '',
        name: 'Register',
        component: Register,
        meta: { requiresGuest: true }
      }
    ]
  },
  {
    path: '/dashboard',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: Dashboard,
        meta: { requiresAuth: true }
      }
    ]
  },
  {
    path: '/users',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'Users',
        component: Users,
        meta: { requiresAuth: true, roles: ['admin', 'organization', 'manager'] }
      },
      {
        path: ':userId/assign-sims',
        name: 'assign-sims',
        component: AssignSims,
        meta: { requiresAuth: true, roles: ['admin', 'organization', 'manager'] }
      }
    ]
  },
  {
    path: '/organizations',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'Organizations',
        component: Organizations,
        meta: { requiresAuth: true, roles: ['admin'] }
      },
      {
        path: ':organizationId/settings',
        name: 'OrganizationSettings',
        component: OrganizationSettings,
        meta: { requiresAuth: true, roles: ['admin'] }
      }
    ]
  },
  {
    path: '/teams',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'Teams',
        component: Teams,
        meta: { requiresAuth: true, roles: ['admin', 'organization'] }
      }
    ]
  },
  {
    path: '/sims',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'Sims',
        component: Sims,
        meta: { requiresAuth: true, roles: ['admin', 'organization'] }
      }
    ]
  },
  {
    path: '/profile',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'Profile',
        component: Profile,
        meta: { requiresAuth: true }
      }
    ]
  },
  {
    path: '/change-password',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'ChangePassword',
        component: ChangePassword,
        meta: { requiresAuth: true }
      }
    ]
  },
  {
    path: '/call-reports',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'CallReports',
        component: CallReports,
        meta: { requiresAuth: true, roles: ['admin', 'organization', 'manager', 'user'] }
      }
    ]
  },
  {
    path: '/summary-report',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'SummaryReport',
        component: SummaryReport,
        meta: { requiresAuth: true, roles: ['admin', 'organization', 'manager', 'user'] }
      }
    ]
  },
  {
    path: '/settings',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'OrganizationSettingsApp',
        component: OrganizationDateFormatSettings,
        meta: { requiresAuth: true, roles: ['organization'] }
      }
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/dashboard'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Initialize auth if not already done
  if (!authStore.user && authStore.token) {
    try {
      await authStore.fetchUser()
    } catch (error) {
      // Token is invalid, will be cleared by the store
    }
  }

  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiresGuest = to.matched.some(record => record.meta.requiresGuest)
  
  // Check role-based access
  const requiredRoles = to.meta.roles
  const userRole = authStore.userRole

  if (requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (requiresGuest && authStore.isAuthenticated) {
    next('/dashboard')
  } else if (requiredRoles && !requiredRoles.includes(userRole)) {
    // User doesn't have required role, redirect to dashboard
    alert('You do not have permission to access this page.')
    next('/dashboard')
  } else {
    next()
  }
})

export default router
