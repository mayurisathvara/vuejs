<template>
  <div class="page-container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-title">
          <h3 class="page-title">Users Management</h3>
        </div>
        <div class="header-actions">
          <button type="button" class="btn btn-light border btn-sm me-2" @click="refreshUsers">
            <i class="fas fa-sync-alt me-2"></i>
            <span class="btn-text">Refresh</span>
          </button>
          <button type="button" class="btn btn-primary btn-sm" @click="openCreateModal">
            <i class="fas fa-plus me-2"></i>
            <span class="btn-text">Add</span>
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
              placeholder="Search users by name, email, or mobile..."
              @input="handleSearch"
            />
          </div>
        </div>
        <div v-if="isAdmin" class="filter-item">
          <div class="select-wrapper">
            <i class="fas fa-building filter-icon"></i>
            <select v-model="organizationFilter" class="form-select select-modern" @change="handleOrganizationFilter">
              <option value="">All Organizations</option>
              <option v-for="org in organizations" :key="org.id" :value="org.id">
                {{ org.name }}
              </option>
            </select>
          </div>
        </div>
        <div class="filter-item">
          <div class="select-wrapper">
            <i class="fas fa-sitemap filter-icon"></i>
            <select v-model="teamFilter" class="form-select select-modern" @change="handleTeamFilter">
              <option value="">All Teams</option>
              <option v-for="dept in teams" :key="dept.id" :value="dept.id">
                {{ dept.name }}
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

    <!-- Users Table -->
    <div class="card card-round">
      <div class="card-body p-0">
        <div class="table-responsive-wrapper">
          <div class="simple-table">
            <Table
              :data="usersStore.users"
              :headers="userHeaders"
              :loading="usersStore.loading"
              :actions="true"
            >
          <template #actions="{ row }">
            <div class="action-buttons">
              <button
                type="button"
                class="assign-sim-link"
                @click="goToAssignSims(row)"
                title="Assign SIMs"
              >
                <i class="fas fa-link"></i>
                <span>Assign SIM</span>
              </button>
              <button
                class="action-btn edit-btn"
                @click="openEditModal(row)"
                title="Edit User"
              >
                <i class="fas fa-edit"></i>
              </button>
              <button
                class="action-btn delete-btn"
                @click="openDeleteModal(row)"
                title="Delete User"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </template>
          <template #cell-name="{ row }">
            <div class="d-flex align-items-center">
              <div class="avatar-sm me-2">
                <div class="avatar-img rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="width: 32px; height: 32px;">
                  {{ row.name?.charAt(0) || 'U' }}
                </div>
              </div>
              <div>
                <div class="fw-bold">{{ row.name }}</div>
                <small class="text-muted">{{ row.email }}</small>
              </div>
            </div>
          </template>
          <template #cell-mobile="{ value }">
            {{ value || 'N/A' }}
          </template>
          <template #cell-role="{ value }">
            <span class="role-text">{{ String(value || '').toUpperCase() || 'N/A' }}</span>
          </template>
          <template #cell-organization_name="{ row }">
            {{ row.organization ? row.organization.name : 'N/A' }}
          </template>
          <template #cell-team_name="{ row }">
            <span v-if="row.team" class="badge bg-info">{{ row.team.name }}</span>
            <span v-else class="text-muted">N/A</span>
          </template>
          <template #cell-status="{ value, row }">
            <div class="status-dropdown">
              <button 
                class="status-badge" 
                :class="`status-${value}`"
                @click="toggleStatusDropdown(row.id)"
                type="button"
              >
                <span class="status-dot"></span>
                <span class="status-text">{{ value.charAt(0).toUpperCase() + value.slice(1) }}</span>
                <i class="fas fa-chevron-down status-icon"></i>
              </button>
              <div 
                v-if="activeDropdown === row.id" 
                class="status-dropdown-menu"
                :data-dropdown-id="row.id"
                @click.stop
              >
                <button 
                  class="status-dropdown-item"
                  :class="{ 'active': value === 'active' }"
                  @click="updateUserStatus(row.id, 'active')"
                  type="button"
                >
                  <span class="status-dot status-dot-active"></span>
                  <span>Active</span>
                  <i v-if="value === 'active'" class="fas fa-check ms-auto"></i>
                </button>
                <button 
                  class="status-dropdown-item"
                  :class="{ 'active': value === 'inactive' }"
                  @click="updateUserStatus(row.id, 'inactive')"
                  type="button"
                >
                  <span class="status-dot status-dot-inactive"></span>
                  <span>Inactive</span>
                  <i v-if="value === 'inactive'" class="fas fa-check ms-auto"></i>
                </button>
              </div>
            </div>
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
        Showing {{ (usersStore.pagination.current_page - 1) * usersStore.pagination.per_page + 1 }} to 
        {{ Math.min(usersStore.pagination.current_page * usersStore.pagination.per_page, usersStore.pagination.total) }} 
        of {{ usersStore.pagination.total }} entries
      </div>
      <Pagination
        :current-page="usersStore.pagination.current_page"
        :total-pages="usersStore.pagination.last_page"
        @page-change="handlePageChange"
      />
    </div>

    <!-- Create/Edit User Modal -->
    <Modal
      :show="showUserModal"
      :title="isEditing ? 'Edit User' : 'Create User'"
      size="lg"
      @close="closeUserModal"
    >
      <form @submit.prevent="handleSubmit">
        <div class="row">
          <div class="col-md-6 mb-3">
            <InputField
              v-model="userForm.name"
              type="text"
              label="Full Name"
              placeholder="Enter full name"
              :error="errors.name"
              required
            />
          </div>
          <div class="col-md-6 mb-3">
            <InputField
              v-model="userForm.email"
              type="email"
              label="Email Address"
              placeholder="Enter email address"
              :error="errors.email"
              required
            />
          </div>
          <div class="col-md-6 mb-3">
            <InputField
              v-model="userForm.mobile"
              type="tel"
              label="Mobile Number"
              placeholder="Enter 10-digit mobile number"
              :error="errors.mobile"
              maxlength="10"
              required
            />
          </div>
          <div v-if="isAdmin" class="col-md-6 mb-3">
            <div class="form-group">
              <label class="form-label">
                Organization
                <span class="text-danger">*</span>
              </label>
              <select v-model="userForm.organization_id" class="form-select" :class="{ 'is-invalid': errors.organization_id }" @change="handleOrganizationChange" required>
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
          <div class="col-md-6 mb-3">
            <div class="form-group">
              <label class="form-label">
                Team
                <span class="text-danger">*</span>
              </label>
              <select v-model="userForm.team_id" class="form-select" :class="{ 'is-invalid': errors.team_id }" :disabled="!userForm.organization_id" required>
                <option value="">Select Team</option>
                <option v-for="dept in availableTeams" :key="dept.id" :value="dept.id">
                  {{ dept.name }}
                </option>
              </select>
              <div v-if="errors.team_id" class="invalid-feedback d-block">
                {{ getErrorMessage(errors.team_id) }}
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <InputField
              v-model="userForm.password"
              type="password"
              label="Password"
              placeholder="Enter password (minimum 6 characters)"
              :error="errors.password"
              :required="!isEditing"
              autocomplete="new-password"
            />
          </div>
          <div v-if="!isManager" class="col-md-6 mb-3">
            <div class="form-group">
              <label class="form-label">
                Role
                <span class="text-danger">*</span>
              </label>
              <select v-model="userForm.role" class="form-select" :class="{ 'is-invalid': errors.role }" required>
                <option value="user">User</option>
                <option value="manager">Manager</option>
              </select>
              <div v-if="errors.role" class="invalid-feedback d-block">
                {{ getErrorMessage(errors.role) }}
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="form-group">
              <label class="form-label">Status</label>
              <select v-model="userForm.status" class="form-select" :class="{ 'is-invalid': errors.status }">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
              <div v-if="errors.status" class="invalid-feedback d-block">
                {{ getErrorMessage(errors.status) }}
              </div>
            </div>
          </div>
        </div>

        <div v-if="errorMessage" class="alert alert-danger" role="alert">
          {{ errorMessage }}
        </div>
      </form>

      <template #footer>
        <Button variant="secondary" @click="closeUserModal">Cancel</Button>
        <Button
          variant="primary"
          :loading="usersStore.loading"
          @click="handleSubmit"
        >
          {{ isEditing ? 'Update User' : 'Create User' }}
        </Button>
      </template>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="showDeleteModal"
      title="Delete User"
      @close="closeDeleteModal"
      @confirm="handleDelete"
    >
      <div class="delete-modal-content">
        <p class="delete-question">Are you sure you want to delete this user?</p>
        <div v-if="userToDelete" class="user-info-card">
          <div class="user-name">{{ userToDelete.name }}</div>
          <div class="user-email">{{ userToDelete.email }}</div>
        </div>
        <p class="warning-text">
          <i class="fas fa-exclamation-triangle me-2"></i>
          This action cannot be undone.
        </p>
      </div>
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
.role-text {
  font-weight: 700;
  letter-spacing: 0.04em;
  font-size: 12px;
  color: #4b5563;
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
  color: white !important;
  background-color: #6c757d;
  border-color: #6c757d;
}

.modal-footer .btn-secondary:hover {
  color: white !important;
  background-color: #5a6268;
  border-color: #545b62;
}

/* Delete modal specific styling */
.delete-modal-content {
  padding: 8px 0;
}

.delete-question {
  font-size: 15px;
  color: #3a3b45;
  margin-bottom: 20px;
  font-weight: 500;
  line-height: 1.5;
}

.user-info-card {
  background-color: #f8f9fc;
  border: 1px solid #e3e6f0;
  border-radius: 8px;
  padding: 16px 20px;
  margin-bottom: 20px;
}

.user-name {
  font-size: 16px;
  font-weight: 600;
  color: #2c2d3a;
  margin-bottom: 6px;
}

.user-email {
  font-size: 14px;
  color: #6c757d;
}

.warning-text {
  color: #dc3545;
  font-size: 14px;
  font-weight: 500;
  margin: 0;
  display: flex;
  align-items: center;
}

.warning-text i {
  color: #dc3545;
}

.modal-footer .btn-secondary:has(+ .btn-primary) {
  background-color: #6f42c1 !important;
  border-color: #6f42c1 !important;
  color: white !important;
}

.modal-footer .btn-secondary:has(+ .btn-primary):hover {
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

/* Status badge styling */
.badge {
  font-size: 0.75rem;
  font-weight: 500;
  padding: 0.375rem 0.75rem;
  border-radius: 0.375rem;
}

.badge.bg-success {
  background-color: #28a745 !important;
}

.badge.bg-secondary {
  background-color: #6c757d !important;
}

/* Status Dropdown Styles */
/* Status Dropdown Styles */
.status-dropdown {
  position: relative;
  display: inline-flex;
  justify-content: center;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border: none;
  border-radius: 16px;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  min-width: 80px;
  justify-content: center;
}

.status-badge .status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-badge .status-text {
  flex: 1;
  text-align: center;
}

.status-badge .status-icon {
  font-size: 8px;
  opacity: 0.7;
  transition: transform 0.2s ease;
}

.status-badge:hover .status-icon {
  opacity: 1;
}

.status-badge.status-active {
  background-color: #d4edda;
  color: #155724;
}

.status-badge.status-active .status-dot {
  background-color: #28a745;
}

.status-badge.status-active:hover {
  background-color: #c3e6cb;
  box-shadow: 0 2px 6px rgba(40, 167, 69, 0.2);
}

.status-badge.status-inactive {
  background-color: #f8d7da;
  color: #721c24;
}

.status-badge.status-inactive .status-dot {
  background-color: #dc3545;
}

.status-badge.status-inactive:hover {
  background-color: #f5c6cb;
  box-shadow: 0 2px 6px rgba(220, 53, 69, 0.2);
}

.status-dropdown-menu {
  position: fixed;
  background: white;
  border: 1px solid #e3e6f0;
  border-radius: 8px;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
  z-index: 9999;
  overflow: hidden;
  min-width: 140px;
  padding: 4px;
  animation: slideDown 0.2s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.status-dropdown-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 13px;
  font-weight: 500;
  border: none;
  background: transparent;
  border-radius: 6px;
  width: 100%;
  text-align: left;
  color: #495057;
}

.status-dropdown-item .status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-dropdown-item .status-dot-active {
  background-color: #28a745;
}

.status-dropdown-item .status-dot-inactive {
  background-color: #dc3545;
}

.status-dropdown-item:hover {
  background-color: #f8f9fa;
  color: #212529;
}

.status-dropdown-item.active {
  background-color: #e7f3ff;
  color: #0066cc;
  font-weight: 600;
}

.status-dropdown-item .fa-check {
  font-size: 12px;
  color: #0066cc;
}

/* Action Button Styles */
.action-buttons {
  display: flex;
  gap: 6px;
  justify-content: flex-end;
  align-items: center;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: 1px solid #e3e6f0;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
  background: #fff;
  position: relative;
  padding: 0;
  margin: 0;
  color: #6c757d;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.action-btn:active {
  transform: translateY(0);
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.assign-sim-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: none;
  background: transparent;
  padding: 0;
  margin-right: 4px;
  color: var(--bs-primary);
  font-weight: 600;
  text-decoration: underline;
  text-underline-offset: 2px;
  line-height: 1;
  cursor: pointer;
  white-space: nowrap;
}

.assign-sim-link i {
  font-size: 14px;
}

.assign-sim-link:hover {
  color: var(--bs-primary);
  opacity: 0.9;
}

.edit-btn {
  color: #4e73df;
}

.edit-btn:hover {
  background: #4e73df;
  border-color: #4e73df;
  color: #fff;
}

.delete-btn {
  color: #e74a3b;
}

.delete-btn:hover {
  background: #e74a3b;
  border-color: #e74a3b;
  color: #fff;
}
</style>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useUsersStore } from '@/stores/users'
import { useAuthStore } from '@/stores/auth'
import { encryptId } from '@/utils/encryption'
import Button from '@/components/Button.vue'
import Table from '@/components/Table.vue'
import Modal from '@/components/Modal.vue'
import InputField from '@/components/InputField.vue'
import Pagination from '@/components/Pagination.vue'
import api from '@/services/api'
import { formatDateDisplay } from '@/utils/dateFormatter'

const router = useRouter()
const usersStore = useUsersStore()
const authStore = useAuthStore()

// Role-based access
const userRole = computed(() => authStore.user?.role || 'user')
const isAdmin = computed(() => userRole.value === 'admin')
const isOrganization = computed(() => userRole.value === 'organization')
const isManager = computed(() => userRole.value === 'manager')

// Search and filters
const searchQuery = ref('')
const organizationFilter = ref('')
const teamFilter = ref('')
const perPage = ref(10)
const searchTimeout = ref(null)

// Modal states
const showUserModal = ref(false)
const showDeleteModal = ref(false)
const isEditing = ref(false)
const userToDelete = ref(null)

// Form data
const userForm = reactive({
  name: '',
  email: '',
  mobile: '',
  password: '',
  role: 'user',
  organization_id: '',
  team_id: '',
  status: 'active'
})

// Organizations data
const organizations = ref([])
const teams = ref([])
const availableTeams = ref([])

// Helper function to get error message
const getErrorMessage = (error) => {
  if (Array.isArray(error)) {
    return error[0]
  }
  return error
}

// Status dropdown state
const activeDropdown = ref(null)

const errors = ref({})
const errorMessage = ref('')

const userHeaders = computed(() => {
  const headers = [
    { key: 'name', label: 'User', class: 'text-start' },
    { key: 'mobile', label: 'Mobile', class: 'text-center' },
    { key: 'role', label: 'Role', class: 'text-center' },
  ]
  
  // Show organization column only for admin
  if (isAdmin.value) {
    headers.push({ key: 'organization_name', label: 'Organization', class: 'text-center' })
  }
  
  headers.push(
    { key: 'team_name', label: 'Team', class: 'text-center' },
    { key: 'status', label: 'Status', class: 'text-center' }
  )
  
  return headers
})

// Methods
const fetchUsers = async () => {
  try {
    await usersStore.fetchUsers(1, searchQuery.value, organizationFilter.value, teamFilter.value)
  } catch (error) {
    console.error('Error fetching users:', error)
  }
}

const fetchOrganizations = async () => {
  try {
    const response = await api.get('/users/organizations')
    organizations.value = response.data
  } catch (error) {
    console.error('Error fetching organizations:', error)
  }
}

const refreshUsers = () => {
  fetchUsers()
}

const handleSearch = () => {
  // Clear existing timeout
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  
  // Set new timeout for debounced search
  searchTimeout.value = setTimeout(() => {
    fetchUsers()
  }, 500) // 500ms delay
}

const handleOrganizationFilter = async () => {
  // Reset team filter when organization changes
  teamFilter.value = ''
  // Fetch teams for the selected organization
  if (organizationFilter.value) {
    await fetchTeamsByOrganization(organizationFilter.value)
  } else {
    teams.value = []
  }
  fetchUsers()
}

const handleTeamFilter = () => {
  fetchUsers()
}

const fetchTeamsByOrganization = async (organizationId) => {
  try {
    const response = await api.get(`/users/teams?organization_id=${organizationId}`)
    teams.value = response.data
  } catch (error) {
    console.error('Error fetching teams:', error)
    teams.value = []
  }
}

const handleOrganizationChange = async () => {
  // Reset team selection when organization changes
  userForm.team_id = ''
  
  // Fetch teams for the selected organization
  if (userForm.organization_id) {
    try {
      const response = await api.get(`/users/teams?organization_id=${userForm.organization_id}`)
      availableTeams.value = response.data
    } catch (error) {
      console.error('Error fetching teams:', error)
      availableTeams.value = []
    }
  } else {
    availableTeams.value = []
  }
}

const handlePerPageChange = () => {
  usersStore.pagination.per_page = perPage.value
  fetchUsers()
}

const handlePageChange = (page) => {
  usersStore.fetchUsers(page, searchQuery.value, organizationFilter.value, teamFilter.value)
}

const openCreateModal = async () => {
  isEditing.value = false
  resetForm()
  
  // For non-admin users, fetch teams after organization is auto-set
  if ((isOrganization.value || isManager.value) && organizations.value.length > 0) {
    userForm.organization_id = organizations.value[0].id
    await handleOrganizationChange()
  }
  
  showUserModal.value = true
}

const openEditModal = async (user) => {
  isEditing.value = true
  userForm.name = user.name
  userForm.email = user.email
  userForm.mobile = user.mobile || ''
  userForm.role = user.role || 'user'
  userForm.organization_id = user.organization_id || ''
  userForm.team_id = user.team_id || ''
  userForm.status = user.status || 'active'
  userForm.password = ''
  userToDelete.value = user
  
  // Fetch teams for the user's organization
  if (userForm.organization_id) {
    try {
      const response = await api.get(`/users/teams?organization_id=${userForm.organization_id}`)
      availableTeams.value = response.data
    } catch (error) {
      console.error('Error fetching teams:', error)
      availableTeams.value = []
    }
  }
  
  showUserModal.value = true
}

const openDeleteModal = (user) => {
  userToDelete.value = user
  showDeleteModal.value = true
}

const closeUserModal = () => {
  showUserModal.value = false
  resetForm()
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  userToDelete.value = null
}

const resetForm = () => {
  userForm.name = ''
  userForm.email = ''
  userForm.mobile = ''
  userForm.password = ''
  userForm.role = 'user'
  userForm.organization_id = ''
  userForm.team_id = ''
  userForm.status = 'active'
  availableTeams.value = []
  errors.value = {}
  errorMessage.value = ''
}

const handleSubmit = async () => {
  errors.value = {}
  errorMessage.value = ''

  // Collect all validation errors
  const validationErrors = {}
  
  if (!userForm.name) {
    validationErrors.name = 'Name is required'
  } else if (userForm.name.length < 2) {
    validationErrors.name = 'Name must be at least 2 characters'
  }
  
  if (!userForm.email) {
    validationErrors.email = 'Email is required'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(userForm.email)) {
    validationErrors.email = 'Please enter a valid email address'
  }
  
  if (!userForm.mobile) {
    validationErrors.mobile = 'Mobile number is required'
  } else if (!/^\d{10}$/.test(userForm.mobile)) {
    validationErrors.mobile = 'Mobile number must be exactly 10 digits'
  }
  
  if (!userForm.organization_id) {
    validationErrors.organization_id = 'Organization is required'
  }
  
  if (!userForm.team_id) {
    validationErrors.team_id = 'Team is required'
  }
  
  if (!userForm.role) {
    validationErrors.role = 'Role is required'
  }
  
  if (!isEditing.value && !userForm.password) {
    validationErrors.password = 'Password is required'
  } else if (userForm.password && userForm.password.length < 6) {
    validationErrors.password = 'Password must be at least 6 characters'
  }

  // If there are validation errors, show them all and return
  if (Object.keys(validationErrors).length > 0) {
    errors.value = validationErrors
    return
  }

  try {
    if (isEditing.value) {
      await usersStore.updateUser(userToDelete.value.id, userForm)
    } else {
      await usersStore.createUser(userForm)
    }
    closeUserModal()
    fetchUsers()
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      errorMessage.value = error.response?.data?.message || 'An error occurred'
    }
  }
}

const toggleStatusDropdown = (userId) => {
  if (activeDropdown.value === userId) {
    activeDropdown.value = null
  } else {
    activeDropdown.value = userId
    // Adjust dropdown position after it's rendered
    nextTick(() => {
      adjustDropdownPosition(userId)
    })
  }
}

const adjustDropdownPosition = (userId) => {
  const dropdown = document.querySelector(`[data-dropdown-id="${userId}"]`)
  const indicator = dropdown?.closest('.status-indicator')
  if (!dropdown || !indicator) return

  const indicatorRect = indicator.getBoundingClientRect()
  const viewportHeight = window.innerHeight
  const dropdownHeight = 80 // Approximate height of dropdown with 2 options
  
  // Calculate position
  const left = indicatorRect.left
  const top = indicatorRect.bottom + 4
  const bottom = viewportHeight - indicatorRect.top + 4
  
  // Set CSS custom properties
  dropdown.style.setProperty('--dropdown-left', `${left}px`)
  dropdown.style.setProperty('--dropdown-top', `${top}px`)
  dropdown.style.setProperty('--dropdown-bottom', `${bottom}px`)
  
  // Check if dropdown would go below viewport
  if (top + dropdownHeight > viewportHeight) {
    dropdown.setAttribute('data-position', 'up')
  } else {
    dropdown.setAttribute('data-position', 'down')
  }
}

const updateUserStatus = async (userId, newStatus) => {
  try {
    await api.put(`/users/${userId}/status`, { status: newStatus })
    // Close dropdown and refresh the users list
    activeDropdown.value = null
    fetchUsers()
  } catch (error) {
    console.error('Error updating user status:', error)
    // Optionally show error message to user
  }
}

const getStatusIndicatorClass = (status) => {
  return status === 'active' ? 'status-active' : 'status-inactive'
}

const getStatusDotClass = (status) => {
  return status === 'active' ? 'active' : 'inactive'
}

const handleDelete = async () => {
  try {
    await usersStore.deleteUser(userToDelete.value.id)
    closeDeleteModal()
    fetchUsers()
  } catch (error) {
    console.error('Error deleting user:', error)
  }
}

const goToAssignSims = (user) => {
  router.push({ name: 'assign-sims', params: { userId: encryptId(user.id) } })
}

const formatDate = (date) => {
  return formatDateDisplay(date)
}

const getStatusBadgeClass = (status) => {
  return status === 'active' 
    ? 'bg-success text-white' 
    : 'bg-secondary text-white'
}


// Watch for changes in perPage
watch(perPage, () => {
  usersStore.pagination.per_page = perPage.value
})

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.status-dropdown')) {
    activeDropdown.value = null
  }
}

onMounted(async () => {
  // Start fetching users immediately (non-blocking)
  fetchUsers()
  
  // Fetch organizations and then teams for non-admin users
  fetchOrganizations().then(() => {
    // For non-admin users, fetch teams for their organization
    if ((isOrganization.value || isManager.value) && organizations.value.length > 0) {
      userForm.organization_id = organizations.value[0].id
      fetchTeamsByOrganization(organizations.value[0].id)
    }
  })
  
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
