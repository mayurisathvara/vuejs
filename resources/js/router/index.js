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
import Organizations from '@/pages/Organizations.vue'
import Departments from '@/pages/Departments.vue'
import Profile from '@/pages/Profile.vue'
import ChangePassword from '@/pages/ChangePassword.vue'

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
        meta: { requiresAuth: true }
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
        meta: { requiresAuth: true }
      }
    ]
  },
  {
    path: '/departments',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'Departments',
        component: Departments,
        meta: { requiresAuth: true }
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

  if (requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (requiresGuest && authStore.isAuthenticated) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router
