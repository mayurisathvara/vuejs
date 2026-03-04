import { defineStore } from 'pinia'
import api from '@/services/api'

export const useSimsStore = defineStore('sims', {
  state: () => ({
    sims: [],
    loading: false,
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0
    }
  }),

  actions: {
    async fetchSims(page = 1, search = '', organizationId = '', teamId = '', perPage = 10) {
      this.loading = true
      try {
        const params = {
          page,
          per_page: perPage,
          ...(search && { search }),
          ...(organizationId && { organization_id: organizationId }),
          ...(teamId && { team_id: teamId })
        }

        const response = await api.get('/sims', { params })
        this.sims = response.data.data
        this.pagination = {
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          per_page: response.data.per_page,
          total: response.data.total
        }
      } catch (error) {
        console.error('Error fetching sims:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async createSim(simData) {
      try {
        const response = await api.post('/sims', simData)
        return response.data
      } catch (error) {
        console.error('Error creating sim:', error)
        throw error
      }
    },

    async updateSim(id, simData) {
      try {
        const response = await api.put(`/sims/${id}`, simData)
        return response.data
      } catch (error) {
        console.error('Error updating sim:', error)
        throw error
      }
    },

    async deleteSim(id) {
      try {
        await api.delete(`/sims/${id}`)
      } catch (error) {
        console.error('Error deleting sim:', error)
        throw error
      }
    }
  }
})
