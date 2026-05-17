<template>
  <div class="page-container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-title">
          <h3 class="page-title">Teams Management</h3>
        </div>
        <div class="header-actions">
          <button type="button" class="hdr-btn hdr-btn--ghost" @click="refreshTeams">
            <i class="fas fa-sync-alt"></i><span>Refresh</span>
          </button>
          <button type="button" class="hdr-btn hdr-btn--add" @click="openCreateModal">
            <i class="fas fa-plus"></i><span>Add</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Filters Card -->
    <div class="filters-card">
      <div class="filters-container">
        <div class="filter-item filter-search">
          <div class="search-box-modern">
            <i class="fas fa-search search-icon-modern"></i>
            <input
              v-model="searchQuery"
              type="text"
              class="form-control search-input-modern"
              placeholder="Search teams by name..."
              @input="handleSearch"
            />
          </div>
        </div>
        <div v-if="isAdmin" class="filter-item">
          <div class="select-wrapper">
            <i class="fas fa-building filter-icon"></i>
            <select v-model="selectedOrganization" class="form-select select-modern" @change="handleOrganizationFilter">
              <option value="">All Organizations</option>
              <option v-for="org in organizations" :key="org.id" :value="org.id">
                {{ org.name }}
              </option>
            </select>
          </div>
        </div>
        <div class="filter-item filter-per-page">
          <div class="select-wrapper">
            <i class="fas fa-list filter-icon"></i>
            <select v-model="perPage" class="form-select select-modern" @change="handlePerPageChange">
              <option value="10">10 per page</option>
              <option value="25">25 per page</option>
              <option value="50">50 per page</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Teams Table -->
    <div class="card card-round users-table-card">
      <div class="card-body p-0">
        <div class="table-responsive-wrapper">
          <div class="simple-table">
            <Table
              :data="teams"
              :headers="teamHeaders"
              :loading="loading"
              :actions="true"
            >
          <template #actions="{ row }">
            <div class="action-buttons">
              <button
                class="action-menu-trigger"
                type="button"
                @click.stop="toggleActionMenu(row.id)"
                aria-label="Open actions"
                title="More actions"
              >
                <i class="fas fa-ellipsis-h"></i>
              </button>
              <div v-if="activeActionMenu === row.id" class="action-menu" @click.stop>
                <button type="button" class="action-menu-item" @click="openEditFromMenu(row)">
                  <i class="fas fa-pen"></i>
                  <span>Edit</span>
                </button>
                <button type="button" class="action-menu-item action-menu-item-danger" @click="openDeleteFromMenu(row)">
                  <i class="fas fa-trash"></i>
                  <span>Delete</span>
                </button>
              </div>
            </div>
          </template>
          <template #cell-name="{ row }">
            <div class="user-cell">
              <div class="user-info">
                <div class="user-name">{{ row.name }}</div>
                <small class="user-email">{{ row.organization?.name || 'N/A' }}</small>
              </div>
            </div>
          </template>
          <template #cell-organization="{ value }">
            <span class="badge bg-info">{{ value?.name || 'N/A' }}</span>
          </template>
          <template #cell-created_at="{ value }">
            {{ formatDate(value) }}
          </template>
            </Table>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to 
        {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} 
        of {{ pagination.total }} entries
      </div>
      <Pagination
        :current-page="pagination.current_page"
        :total-pages="pagination.last_page"
        @page-change="handlePageChange"
      />
    </div>

    <!-- Create/Edit Team Modal -->
    <Modal
      :show="showTeamModal"
      :title="isEditing ? 'Edit Team' : 'Create Team'"
      size="lg"
      @close="closeTeamModal"
    >
      <form @submit.prevent="handleSubmit">
        <div class="row">
          <div class="col-md-6 mb-3">
            <InputField
              v-model="teamForm.name"
              type="text"
              label="Team Name"
              placeholder="Enter team name"
              :error="errors.name"
              required
            />
          </div>
          <div v-if="isAdmin" class="col-md-6 mb-3">
            <div class="form-group">
              <label class="form-label">Organization</label>
              <select v-model="teamForm.organization_id" class="form-select" :class="{ 'is-invalid': errors.organization_id }">
                <option value="">Select Organization</option>
                <option v-for="org in organizations" :key="org.id" :value="org.id">
                  {{ org.name }}
                </option>
              </select>
              <div v-if="errors.organization_id" class="invalid-feedback d-block">
                {{ getErrorMessage(errors.organization_id) }}
              </div>
            </div>
          </div>
        </div>

        <div v-if="errorMessage" class="alert alert-danger" role="alert">
          {{ errorMessage }}
        </div>
      </form>

      <template #footer>
        <Button variant="secondary" @click="closeTeamModal">Cancel</Button>
        <Button
          variant="primary"
          :loading="loading"
          @click="handleSubmit"
        >
          {{ isEditing ? 'Update Team' : 'Create Team' }}
        </Button>
      </template>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="showDeleteModal"
      title="Delete Team"
      @close="closeDeleteModal"
      @confirm="handleDelete"
    >
      <p>Are you sure you want to delete this team?</p>
      <div v-if="teamToDelete" class="alert alert-warning">
        <strong>{{ teamToDelete.name }}</strong><br>
        <small>{{ teamToDelete.organization?.name || 'N/A' }}</small>
      </div>
      <p class="text-danger">This action cannot be undone.</p>
    </Modal>
  </div>
</template>

<style scoped>
.table-responsive-wrapper {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

/* Simple table look (match Call Reports) */
.simple-table :deep(.table-responsive) {
  border: 1px solid #e3e6f0;
  border-radius: 10px;
  overflow: hidden;
}

.simple-table :deep(table.table) {
  width: 100%;
  min-width: 1200px;
  border-collapse: collapse;
  margin: 0;
  font-size: 14px;
}

.simple-table :deep(table.table thead th) {
  background: #f8f9fa;
  color: #111827;
  font-weight: 600;
  text-transform: none !important;
  letter-spacing: normal !important;
  border-bottom: 1px solid #e3e6f0;
  border-right: 1px solid #e3e6f0;
  padding: 10px 12px !important;
  vertical-align: middle;
  white-space: nowrap;
}

.simple-table :deep(table.table tbody td) {
  border-top: 1px solid #e3e6f0;
  border-right: 1px solid #e3e6f0;
  padding: 10px 12px !important;
  vertical-align: middle;
  background: #fff;
}

.simple-table :deep(table.table thead th:last-child),
.simple-table :deep(table.table tbody td:last-child) {
  border-right: none;
}

.simple-table :deep(table.table tbody tr:hover) {
  background: transparent;
}
.search-box-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 12px;
  z-index: 10;
  color: #6c757d;
  font-size: 14px;
  pointer-events: none;
}

.search-input {
  padding-left: 40px !important;
  border: 1px solid #e3e6f0;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s ease;
  background-color: #fff;
}

.search-input:focus {
  border-color: #4e73df;
  box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
  outline: none;
}

.search-input::placeholder {
  color: #a0a6b1;
  font-size: 14px;
}

/* Hover effect */
.search-box-wrapper:hover .search-input {
  border-color: #d1d3e2;
}

/* Focus effect for search icon */
.search-box-wrapper:focus-within .search-icon,
.search-box-wrapper:hover .search-icon {
  color: #4e73df;
}

/* Modal button styling */
.modal-footer .btn-secondary {
  background-color: #6f42c1 !important;
  border-color: #6f42c1 !important;
  color: white !important;
}

.modal-footer .btn-secondary:hover {
  background-color: #5a32a3 !important;
  border-color: #5a32a3 !important;
  color: white !important;
}

.modal-footer .btn-primary {
  background-color: #4e73df !important;
  border-color: #4e73df !important;
  color: white !important;
}

.modal-footer .btn-primary:hover {
  background-color: #3d5fc7 !important;
  border-color: #3d5fc7 !important;
  color: white !important;
}

/* Badge styling */
.badge {
  font-size: 0.75rem;
  font-weight: 500;
  padding: 0.375rem 0.75rem;
  border-radius: 0.375rem;
}

.badge.bg-info {
  background-color: #17a2b8 !important;
}

.users-table-card {
  background: #ffffff;
  border: 0;
  border-radius: 14px;
  box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
}

.simple-table :deep(.table-responsive) {
  border: 1px solid #eef2f7;
  border-radius: 14px;
}

.simple-table :deep(table.table) {
  border-collapse: separate;
  border-spacing: 0;
  font-size: 13.5px;
}

.simple-table :deep(table.table thead th) {
  background: #f9fafb;
  color: #374151;
  letter-spacing: 0.03em !important;
  border-bottom: 1px solid #edf1f7;
  border-right: 1px solid #edf1f7;
  padding: 10px 14px !important;
  font-size: 12px;
}

.simple-table :deep(table.table tbody td) {
  border-top: 1px solid #f1f5f9;
  border-right: 1px solid #f1f5f9;
  padding: 9px 14px !important;
  transition: background-color 0.2s ease;
}

.simple-table :deep(table.table tbody tr:hover),
.simple-table :deep(table.table tbody tr:hover td) {
  background: #f9fafb;
}

.filters-card {
  background: #ffffff;
  border: 1px solid #eef2f7;
  border-radius: 14px;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
  padding: 14px;
}

.filters-container {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: center;
}

.filter-item {
  min-width: 180px;
  flex: 1 1 180px;
}

.filter-search {
  flex: 2.4 1 360px;
}

.filter-per-page {
  flex: 0 1 170px;
}

.search-box-modern,
.select-wrapper {
  position: relative;
}

.filter-icon,
.search-icon-modern {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  pointer-events: none;
  z-index: 1;
}

.search-input-modern,
.select-modern {
  height: 42px;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  font-size: 13px;
  box-shadow: none;
}

.search-input-modern {
  padding-left: 38px;
}

.select-modern {
  padding-left: 36px;
}

.search-input-modern:focus,
.select-modern:focus {
  border-color: #cbd5e1;
  box-shadow: 0 0 0 4px rgba(148, 163, 184, 0.16);
}

.header-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.hdr-btn { display: inline-flex; align-items: center; gap: 7px; padding: 0 14px; height: 36px; border-radius: 10px; font-size: 0.82rem; font-weight: 700; border: none; cursor: pointer; transition: background 0.15s, box-shadow 0.15s, border-color 0.15s; white-space: nowrap; line-height: 1; }
.hdr-btn i { font-size: 0.8rem; }
.hdr-btn--ghost { background: #fff; color: #374151; border: 1.5px solid #e5e7eb; }
.hdr-btn--ghost:hover { background: #f9fafb; border-color: #d1d5db; }
.hdr-btn--add { background: linear-gradient(135deg, #f97316, #ffb454); color: #fff; box-shadow: 0 3px 12px rgba(249,115,22,0.28); }
.hdr-btn--add:hover { box-shadow: 0 5px 16px rgba(249,115,22,0.38); }

.action-buttons {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  align-items: center;
  position: relative;
}

.action-menu-trigger {
  width: 32px;
  height: 32px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #ffffff;
  color: #6b7280;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-menu-trigger:hover {
  background: #f8fafc;
  border-color: #d1d5db;
  color: #111827;
}

.action-menu {
  position: absolute;
  top: calc(100% + 6px);
  right: 0;
  min-width: 142px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
  padding: 6px;
  z-index: 1050;
}

.action-menu-item {
  width: 100%;
  border: 0;
  background: transparent;
  color: #374151;
  border-radius: 8px;
  padding: 8px 10px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 600;
  text-align: left;
  transition: all 0.2s ease;
}

.action-menu-item:hover {
  background: #f9fafb;
  color: #111827;
}

.action-menu-item-danger {
  color: #dc2626;
}

.action-menu-item-danger:hover {
  background: #fef2f2;
  color: #b91c1c;
}

.user-cell {
  display: flex;
  align-items: center;
}

.user-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.user-name {
  font-weight: 700;
  color: #111827;
  line-height: 1.2;
}

.user-email {
  color: #6b7280;
  font-size: 12px;
  line-height: 1.3;
}
</style>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth'
import Button from '@/components/Button.vue'
import Table from '@/components/Table.vue'
import Modal from '@/components/Modal.vue'
import InputField from '@/components/InputField.vue'
import Pagination from '@/components/Pagination.vue'
import api from '@/services/api'
import { showSuccess, showError } from '@/services/toast'
import { formatDateDisplay } from '@/utils/dateFormatter'

const authStore = useAuthStore()

// Role-based access
const userRole = computed(() => authStore.user?.role || 'user')
const isAdmin = computed(() => userRole.value === 'admin')
const isOrganization = computed(() => userRole.value === 'organization')
const isManager = computed(() => userRole.value === 'manager')

// Data
const teams = ref([])
const organizations = ref([])
const loading = ref(false)
const searchQuery = ref('')
const selectedOrganization = ref('')
const perPage = ref(10)
const searchTimeout = ref(null)

// Modal states
const showTeamModal = ref(false)
const showDeleteModal = ref(false)
const isEditing = ref(false)
const teamToDelete = ref(null)
const activeActionMenu = ref(null)

// Form data
const teamForm = reactive({
  name: '',
  organization_id: ''
})

const errors = ref({})
const errorMessage = ref('')

// Pagination
const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0
})

// Helper function to get error message
const getErrorMessage = (error) => {
  if (Array.isArray(error)) {
    return error[0]
  }
  return error
}

const teamHeaders = computed(() => {
  const headers = [
    { key: 'name', label: 'Team', class: 'text-start' },
  ]
  
  // Show organization column only for admin
  if (isAdmin.value) {
    headers.push({ key: 'organization', label: 'Organization', class: 'text-center' })
  }
  
  headers.push({ key: 'created_at', label: 'Created', class: 'text-center' })
  
  return headers
})

// Methods
const fetchTeams = async (page = 1) => {
  try {
    loading.value = true
    const params = new URLSearchParams({
      page: page,
      per_page: perPage.value
    })
    
    if (searchQuery.value) {
      params.append('search', searchQuery.value)
    }
    
    if (selectedOrganization.value) {
      params.append('organization_id', selectedOrganization.value)
    }

    const response = await api.get(`/teams?${params}`)
    teams.value = response.data.data
    pagination.current_page = response.data.current_page
    pagination.last_page = response.data.last_page
    pagination.per_page = response.data.per_page
    pagination.total = response.data.total
  } catch (error) {
    console.error('Error fetching teams:', error)
    showError('Failed to load teams. Please try again.')
  } finally {
    loading.value = false
  }
}

const fetchOrganizations = async () => {
  try {
    const response = await api.get('/teams/organizations')
    organizations.value = response.data
    
    // Auto-set organization for organization and manager roles
    if ((isOrganization.value || isManager.value) && organizations.value.length > 0) {
      teamForm.organization_id = organizations.value[0].id
    }
  } catch (error) {
    console.error('Error fetching organizations:', error)
    showError('Failed to load organizations. Please try again.')
  }
}

const refreshTeams = () => {
  fetchTeams()
}

const handleSearch = () => {
  // Clear existing timeout
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  
  // Set new timeout for debounced search
  searchTimeout.value = setTimeout(() => {
    fetchTeams()
  }, 500) // 500ms delay
}

const handleOrganizationFilter = () => {
  fetchTeams()
}

const handlePerPageChange = () => {
  pagination.per_page = perPage.value
  fetchTeams()
}

const handlePageChange = (page) => {
  fetchTeams(page)
}

const openCreateModal = () => {
  isEditing.value = false
  resetForm()
  
  // For non-admin users, auto-set organization
  if ((isOrganization.value || isManager.value) && organizations.value.length > 0) {
    teamForm.organization_id = organizations.value[0].id
  }
  
  showTeamModal.value = true
}

const openEditModal = (team) => {
  isEditing.value = true
  teamForm.name = team.name
  teamForm.organization_id = team.organization_id
  teamToDelete.value = team
  showTeamModal.value = true
}

const openDeleteModal = (team) => {
  teamToDelete.value = team
  showDeleteModal.value = true
}

const openEditFromMenu = (team) => {
  activeActionMenu.value = null
  openEditModal(team)
}

const openDeleteFromMenu = (team) => {
  activeActionMenu.value = null
  openDeleteModal(team)
}

const toggleActionMenu = (teamId) => {
  activeActionMenu.value = activeActionMenu.value === teamId ? null : teamId
}

const handleClickOutside = (event) => {
  if (!event.target.closest('.action-buttons')) {
    activeActionMenu.value = null
  }
}

const closeTeamModal = () => {
  showTeamModal.value = false
  resetForm()
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  teamToDelete.value = null
}

const resetForm = () => {
  teamForm.name = ''
  teamForm.organization_id = ''
  errors.value = {}
  errorMessage.value = ''
}

const handleSubmit = async () => {
  errors.value = {}
  errorMessage.value = ''

  // Collect all validation errors
  const validationErrors = {}
  
  if (!teamForm.name) {
    validationErrors.name = 'Team name is required'
  } else if (teamForm.name.length < 2) {
    validationErrors.name = 'Team name must be at least 2 characters'
  }
  
  // Only validate organization for admin
  if (isAdmin.value && !teamForm.organization_id) {
    validationErrors.organization_id = 'Organization is required'
  }

  // If there are validation errors, show them all and return
  if (Object.keys(validationErrors).length > 0) {
    errors.value = validationErrors
    return
  }

  try {
    loading.value = true
    if (isEditing.value) {
      await api.put(`/teams/${teamToDelete.value.id}`, teamForm)
      showSuccess('Team updated successfully!')
    } else {
      await api.post('/teams', teamForm)
      showSuccess('Team created successfully!')
    }
    closeTeamModal()
    fetchTeams()
  } catch (error) {
    console.error('Error saving team:', error)
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = 'Error saving team. Please try again.'
      showError('Failed to save team. Please try again.')
    }
  } finally {
    loading.value = false
  }
}

const handleDelete = async () => {
  try {
    loading.value = true
    await api.delete(`/teams/${teamToDelete.value.id}`)
    showSuccess('Team deleted successfully!')
    closeDeleteModal()
    fetchTeams()
  } catch (error) {
    console.error('Error deleting team:', error)
    showError('Failed to delete team. Please try again.')
  } finally {
    loading.value = false
  }
}

const formatDate = (date) => {
  return formatDateDisplay(date)
}

// Watch for changes in perPage
watch(perPage, () => {
  fetchTeams()
})

onMounted(() => {
  fetchTeams()
  fetchOrganizations()
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  document.removeEventListener('click', handleClickOutside)
})
</script>
