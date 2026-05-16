import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const DEFAULT_ADMIN_DATE_FORMAT = 'Y-m-d'
  const DATE_FORMAT_STORAGE_KEY = 'date_formate'

  // State
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))
  const loading = ref(false)
  const dateFormat = ref(localStorage.getItem(DATE_FORMAT_STORAGE_KEY) || DEFAULT_ADMIN_DATE_FORMAT)

  // Ensure axios has auth header immediately on store creation (before component mount hooks).
  if (token.value) {
    api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }

  // Getters
  const isAuthenticated = computed(() => !!token.value)
  const userRole = computed(() => user.value?.role || null)

  const normalizeDateFormat = (value) => {
    const allowed = ['Y-m-d', 'd-m-Y', 'm-d-Y', 'd/m/Y', 'm/d/Y', 'Y/m/d']
    return allowed.includes(value) ? value : DEFAULT_ADMIN_DATE_FORMAT
  }

  const setDateFormat = (value) => {
    const normalized = normalizeDateFormat(value)
    dateFormat.value = normalized
    localStorage.setItem(DATE_FORMAT_STORAGE_KEY, normalized)
  }

  const pickDateFormatFromUser = (userData) => {
    return (
      userData?.date_formate ||
      userData?.organization?.date_formate ||
      userData?.organization?.settings?.date_formate ||
      null
    )
  }

  const syncDateFormat = async (userData = user.value) => {
    const role = userData?.role || userRole.value

    // Admin always uses fixed default.
    if (role === 'admin') {
      setDateFormat(DEFAULT_ADMIN_DATE_FORMAT)
      return DEFAULT_ADMIN_DATE_FORMAT
    }

    const fromUser = pickDateFormatFromUser(userData)
    if (fromUser) {
      setDateFormat(fromUser)
      return dateFormat.value
    }

    const orgId = userData?.organization?.id || userData?.organization_id
    if (!orgId) {
      setDateFormat(DEFAULT_ADMIN_DATE_FORMAT)
      return dateFormat.value
    }

    try {
      const res = await api.get(`/organizations/${orgId}/settings`)
      const orgDateFormat = res.data?.settings?.date_formate
      setDateFormat(orgDateFormat || DEFAULT_ADMIN_DATE_FORMAT)
    } catch {
      setDateFormat(DEFAULT_ADMIN_DATE_FORMAT)
    }

    return dateFormat.value
  }

  // Actions
  const login = async (credentials) => {
    loading.value = true
    try {
      const response = await api.post('/login', credentials)
      const { user: userData, token: authToken } = response.data
      
      user.value = userData
      token.value = authToken
      localStorage.setItem('token', authToken)
      localStorage.setItem('user', JSON.stringify(userData))
      await syncDateFormat(userData)
      
      // Set default axios header
      api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
      
      return response.data
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  const register = async (userData) => {
    loading.value = true
    try {
      const response = await api.post('/register', userData)
      const data = response.data

      // New flow: registration requires email verification — no token issued.
      if (data.requires_verification) {
        return data
      }

      // Fallback: auto-login if a token is returned (legacy / admin-created accounts).
      const { user: newUser, token: authToken } = data
      if (newUser && authToken) {
        user.value = newUser
        token.value = authToken
        localStorage.setItem('token', authToken)
        localStorage.setItem('user', JSON.stringify(newUser))
        await syncDateFormat(newUser)
        api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
      }

      return data
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    loading.value = true

    // Capture current token for best-effort server revoke.
    const currentToken = token.value

    // Clear state immediately (fast UX).
    user.value = null
    token.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    localStorage.removeItem(DATE_FORMAT_STORAGE_KEY)
    dateFormat.value = DEFAULT_ADMIN_DATE_FORMAT
    delete api.defaults.headers.common['Authorization']
    loading.value = false

    // Best-effort revoke in background; do not block UI.
    const headers = currentToken ? { Authorization: `Bearer ${currentToken}` } : undefined
    void api
      .post('/logout', null, { headers, timeout: 4000 })
      .catch((error) => {
        console.error('Logout error:', error)
      })
  }

  const fetchUser = async () => {
    if (!token.value) return

    try {
      const response = await api.get('/user')
      user.value = response.data
      localStorage.setItem('user', JSON.stringify(response.data))
      await syncDateFormat(response.data)
      return response.data
    } catch (error) {
      // If token is invalid, logout
      if (error.response?.status === 401) {
        await logout()
      }
      throw error
    }
  }

  const initializeAuth = () => {
    const storedToken = localStorage.getItem('token')
    const storedUser = localStorage.getItem('user')
    
    if (storedToken && storedUser) {
      token.value = storedToken
      user.value = JSON.parse(storedUser)
      api.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`
      // Best-effort background sync for date format.
      void syncDateFormat(user.value)
    }
  }

  const setUser = (userData) => {
    user.value = userData
    localStorage.setItem('user', JSON.stringify(userData))
    void syncDateFormat(userData)
  }

  return {
    // State
    user,
    token,
    loading,
    dateFormat,
    // Getters
    isAuthenticated,
    userRole,
    // Actions
    login,
    register,
    logout,
    fetchUser,
    initializeAuth,
    setUser,
    setDateFormat,
    syncDateFormat
  }
})
