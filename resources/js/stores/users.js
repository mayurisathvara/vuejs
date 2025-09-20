import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { showSuccess, showError } from '@/services/toast'

export const useUsersStore = defineStore('users', () => {
  // State
  const users = ref([])
  const loading = ref(false)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0
  })

  // Actions
  const fetchUsers = async (page = 1, search = '', organization_id = '', department_id = '') => {
    loading.value = true
    try {
      const params = { page, per_page: pagination.value.per_page }
      if (search) params.search = search
      if (organization_id) params.organization_id = organization_id
      if (department_id) params.department_id = department_id
      
      const response = await api.get('/users', { params })
      users.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total
      }
      return response.data
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  const createUser = async (userData) => {
    loading.value = true
    try {
      const response = await api.post('/users', userData)
      users.value.unshift(response.data)
      showSuccess('User created successfully!')
      return response.data
    } catch (error) {
      showError('Failed to create user. Please try again.')
      throw error
    } finally {
      loading.value = false
    }
  }

  const updateUser = async (id, userData) => {
    loading.value = true
    try {
      const response = await api.put(`/users/${id}`, userData)
      const index = users.value.findIndex(user => user.id === id)
      if (index !== -1) {
        users.value[index] = response.data
      }
      showSuccess('User updated successfully!')
      return response.data
    } catch (error) {
      showError('Failed to update user. Please try again.')
      throw error
    } finally {
      loading.value = false
    }
  }

  const deleteUser = async (id) => {
    loading.value = true
    try {
      await api.delete(`/users/${id}`)
      users.value = users.value.filter(user => user.id !== id)
      showSuccess('User deleted successfully!')
      return true
    } catch (error) {
      showError('Failed to delete user. Please try again.')
      throw error
    } finally {
      loading.value = false
    }
  }

  const getUserById = (id) => {
    return users.value.find(user => user.id === id)
  }

  return {
    // State
    users,
    loading,
    pagination,
    // Actions
    fetchUsers,
    createUser,
    updateUser,
    deleteUser,
    getUserById
  }
})
