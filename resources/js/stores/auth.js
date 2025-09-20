import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))
  const loading = ref(false)

  // Getters
  const isAuthenticated = computed(() => !!token.value)
  const userRole = computed(() => user.value?.role || null)

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
      const { user: newUser, token: authToken } = response.data
      
      user.value = newUser
      token.value = authToken
      localStorage.setItem('token', authToken)
      localStorage.setItem('user', JSON.stringify(newUser))
      
      // Set default axios header
      api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
      
      return response.data
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    loading.value = true
    try {
      await api.post('/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      // Clear state regardless of API call success
      user.value = null
      token.value = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      delete api.defaults.headers.common['Authorization']
      loading.value = false
    }
  }

  const fetchUser = async () => {
    if (!token.value) return

    try {
      const response = await api.get('/user')
      user.value = response.data
      localStorage.setItem('user', JSON.stringify(response.data))
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
    }
  }

  const setUser = (userData) => {
    user.value = userData
    localStorage.setItem('user', JSON.stringify(userData))
  }

  return {
    // State
    user,
    token,
    loading,
    // Getters
    isAuthenticated,
    userRole,
    // Actions
    login,
    register,
    logout,
    fetchUser,
    initializeAuth,
    setUser
  }
})
