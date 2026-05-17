import axios from 'axios'

// Create axios instance
const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: true
})

// Request interceptor
api.interceptors.request.use(
  (config) => {
    // Always attach Bearer token from localStorage if available.
    // This prevents refresh-time 401 when app boot order calls APIs before store init finishes.
    const authToken = localStorage.getItem('token')
    if (authToken && !config.headers['Authorization']) {
      config.headers['Authorization'] = `Bearer ${authToken}`
    }

    // Add CSRF token for Laravel Sanctum
    const token = document.querySelector('meta[name="csrf-token"]')
    if (token) {
      config.headers['X-CSRF-TOKEN'] = token.getAttribute('content')
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    // Handle 401 errors (unauthorized)
    if (error.response?.status === 401) {
      // Clear auth data
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      delete api.defaults.headers.common['Authorization']
      
      // Redirect to login if not already there
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    
    // Handle 403 errors (forbidden - insufficient permissions)
    // Skip global handling for cases the calling code manages itself
    // (email_not_verified, plan_expired, SIM_LIMIT_REACHED, etc.)
    if (error.response?.status === 403) {
      const data = error.response?.data || {}

      if (!data.email_not_verified && !data.error && !data.code) {
        const message = data.message || 'You do not have permission to access this resource.'
        if (window.$toast) {
          window.$toast.error(message)
        }
        if (window.location.pathname !== '/dashboard') {
          window.location.href = '/dashboard'
        }
      }
    }
    
    return Promise.reject(error)
  }
)

export default api
