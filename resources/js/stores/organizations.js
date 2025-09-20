import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { showSuccess, showError } from '@/services/toast'

export const useOrganizationsStore = defineStore('organizations', () => {
  // State
  const organizations = ref([])
  const loading = ref(false)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0
  })

  // Actions
  const fetchOrganizations = async (page = 1, search = '') => {
    loading.value = true
    try {
      const params = { page, per_page: pagination.value.per_page }
      if (search) params.search = search
      
      const response = await api.get('/organizations', { params })
      organizations.value = response.data.data
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

  const createOrganization = async (organizationData) => {
    loading.value = true
    try {
      const response = await api.post('/organizations', organizationData)
      organizations.value.unshift(response.data)
      showSuccess('Organization created successfully!')
      return response.data
    } catch (error) {
      showError('Failed to create organization. Please try again.')
      throw error
    } finally {
      loading.value = false
    }
  }

  const updateOrganization = async (id, organizationData) => {
    loading.value = true
    try {
      const response = await api.put(`/organizations/${id}`, organizationData)
      const index = organizations.value.findIndex(org => org.id === id)
      if (index !== -1) {
        organizations.value[index] = response.data
      }
      showSuccess('Organization updated successfully!')
      return response.data
    } catch (error) {
      showError('Failed to update organization. Please try again.')
      throw error
    } finally {
      loading.value = false
    }
  }

  const deleteOrganization = async (id) => {
    loading.value = true
    try {
      await api.delete(`/organizations/${id}`)
      organizations.value = organizations.value.filter(org => org.id !== id)
      showSuccess('Organization deleted successfully!')
      return true
    } catch (error) {
      showError('Failed to delete organization. Please try again.')
      throw error
    } finally {
      loading.value = false
    }
  }

  const getOrganizationById = (id) => {
    return organizations.value.find(org => org.id === id)
  }

  return {
    // State
    organizations,
    loading,
    pagination,
    // Actions
    fetchOrganizations,
    createOrganization,
    updateOrganization,
    deleteOrganization,
    getOrganizationById
  }
})

