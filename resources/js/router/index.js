import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Layouts
import LoginLayout from '@/layouts/LoginLayout.vue'
import RegisterLayout from '@/layouts/RegisterLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'

// Pages
import Login from '@/pages/Login.vue'
import Register from '@/pages/Register.vue'
import VerifyEmail from '@/pages/VerifyEmail.vue'
import ForgotPassword from '@/pages/ForgotPassword.vue'
import ResetPassword from '@/pages/ResetPassword.vue'
import Dashboard from '@/pages/Dashboard.vue'
import Users from '@/pages/Users.vue'
import AssignSims from '@/pages/AssignSims.vue'
import Organizations from '@/pages/Organizations.vue'
import OrganizationSettings from '@/pages/OrganizationSettings.vue'
import OrganizationDateFormatSettings from '@/pages/OrganizationDateFormatSettings.vue'
import Teams from '@/pages/Teams.vue'
import Sims from '@/pages/Sims.vue'
import ExcludedNumbers from '@/pages/ExcludedNumbers.vue'
import Profile from '@/pages/Profile.vue'
import ChangePassword from '@/pages/ChangePassword.vue'
import CallReports from '@/pages/CallReports.vue'
import SummaryReport from '@/pages/SummaryReport.vue'
import DeveloperApi from '@/pages/DeveloperApi.vue'
import Subscription from '@/pages/Subscription.vue'
import SubscriptionRenew from '@/pages/SubscriptionRenew.vue'
import Plans from '@/pages/Plans.vue'

const APP_NAME = 'Callytics'

const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/login',
    component: LoginLayout,
    children: [
      {
        path: '',
        name: 'Login',
        component: Login,
        meta: {
          requiresGuest: true,
          title: 'Sign In',
          seo: {
            description: 'Sign in to your Callytics account to access your call analytics dashboard, SIM management, and team reports.',
            ogTitle: 'Sign In to Callytics',
            ogDescription: 'Access your call analytics dashboard, manage SIMs, and monitor team performance with Callytics.',
            robots: 'noindex, nofollow',
          },
        },
      }
    ]
  },
  {
    path: '/register',
    component: RegisterLayout,
    children: [
      {
        path: '',
        name: 'Register',
        component: Register,
        meta: {
          requiresGuest: true,
          title: 'Start Your Free Trial',
          seo: {
            description: 'Sign up for Callytics and start your 14-day free trial. Track call logs, manage SIMs, monitor team performance, and grow your business — no credit card required.',
            ogTitle:     'Create Your Free Callytics Account',
            ogDescription: 'Join thousands of teams using Callytics to track calls, manage SIMs, and generate powerful call analytics reports. Free 14-day trial.',
            robots:    'index, follow',
          },
        },
      }
    ]
  },
  {
    path: '/verify-email',
    component: VerifyEmail,
    meta: { title: 'Verify Email' },
  },
  {
    path: '/forgot-password',
    component: ForgotPassword,
    meta: { requiresGuest: true, title: 'Forgot Password' },
  },
  {
    path: '/reset-password',
    component: ResetPassword,
    meta: { requiresGuest: true, title: 'Reset Password' },
  },
  {
    path: '/dashboard',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: Dashboard,
        meta: { requiresAuth: true, title: 'Dashboard' }
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
        meta: { requiresAuth: true, roles: ['admin', 'organization', 'manager'], title: 'User Management' }
      },
      {
        path: ':userId/assign-sims',
        name: 'assign-sims',
        component: AssignSims,
        meta: { requiresAuth: true, roles: ['admin', 'organization', 'manager'], title: 'Assign SIMs' }
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
        meta: { requiresAuth: true, roles: ['admin'], title: 'Organizations' }
      },
      {
        path: ':organizationId/settings',
        name: 'OrganizationSettings',
        component: OrganizationSettings,
        meta: { requiresAuth: true, roles: ['admin'], title: 'Organization Settings' }
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
        meta: { requiresAuth: true, roles: ['admin', 'organization'], title: 'Team Management' }
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
        meta: { requiresAuth: true, roles: ['admin', 'organization'], title: 'SIM Management' }
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
        meta: { requiresAuth: true, title: 'My Profile' }
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
        meta: { requiresAuth: true, title: 'Change Password' }
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
        meta: { requiresAuth: true, roles: ['admin', 'organization', 'manager', 'user'], title: 'Call Reports' }
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
        meta: { requiresAuth: true, roles: ['admin', 'organization', 'manager', 'user'], title: 'Summary Report' }
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
        meta: { requiresAuth: true, roles: ['organization'], title: 'Settings' }
      }
    ]
  },
  {
    path: '/excluded-numbers',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'ExcludedNumbers',
        component: ExcludedNumbers,
        meta: { requiresAuth: true, roles: ['admin', 'organization', 'manager'], title: 'Excluded Numbers' }
      }
    ]
  },
  {
    path: '/developer-api',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'DeveloperApi',
        component: DeveloperApi,
        meta: { requiresAuth: true, roles: ['admin', 'organization'], title: 'Developer API' }
      }
    ]
  },
  {
    path: '/plans',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'Plans',
        component: Plans,
        meta: { requiresAuth: true, roles: ['admin'], title: 'Plan Management' }
      }
    ]
  },
  {
    path: '/subscription',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'Subscription',
        component: Subscription,
        meta: { requiresAuth: true, roles: ['organization'], title: 'Subscription' }
      },
      {
        path: 'renew',
        name: 'SubscriptionRenew',
        component: SubscriptionRenew,
        meta: { requiresAuth: true, roles: ['organization'], title: 'Renew Subscription' }
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

// Update document title and meta tags on each navigation
router.afterEach((to) => {
  const APP_URL    = window.location.origin
  const pageTitle  = to.meta.title
  const seo        = to.meta.seo || {}

  // Title
  document.title = pageTitle ? `${pageTitle} | ${APP_NAME}` : APP_NAME

  // Helper – find or create a <meta> tag
  const setMeta = (selector, attr, value) => {
    let el = document.querySelector(selector)
    if (!el) {
      el = document.createElement('meta')
      const [attrName, attrVal] = selector.replace('meta[', '').replace(']', '').split('=')
      el.setAttribute(attrName.trim(), attrVal.replace(/"/g, '').trim())
      document.head.appendChild(el)
    }
    el.setAttribute(attr, value)
  }

  // Description
  const description = seo.description || 'Callytics is a powerful call analytics and management platform. Track call logs, manage SIMs, monitor team performance, and generate insightful reports.'
  setMeta('meta[name="description"]', 'content', description)

  // Robots
  const robots = seo.robots || 'index, follow'
  setMeta('meta[name="robots"]', 'content', robots)

  // Open Graph
  const ogTitle = seo.ogTitle || (pageTitle ? `${pageTitle} | ${APP_NAME}` : APP_NAME)
  const ogDesc  = seo.ogDescription || description
  setMeta('meta[property="og:title"]',       'content', ogTitle)
  setMeta('meta[property="og:description"]', 'content', ogDesc)
  setMeta('meta[property="og:url"]',         'content', APP_URL + to.fullPath)

  // Twitter
  setMeta('meta[name="twitter:title"]',       'content', ogTitle)
  setMeta('meta[name="twitter:description"]', 'content', ogDesc)

  // Canonical
  let canonical = document.querySelector('link[rel="canonical"]')
  if (!canonical) {
    canonical = document.createElement('link')
    canonical.setAttribute('rel', 'canonical')
    document.head.appendChild(canonical)
  }
  canonical.setAttribute('href', APP_URL + to.fullPath)
})

export default router
