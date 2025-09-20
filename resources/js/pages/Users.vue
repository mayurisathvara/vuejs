<template>
  <div>
    <!-- Page Header -->
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
      <div>
        <h3 class="fw-bold mb-3">Users Management</h3>
      </div>
      <div class="ms-md-auto py-2 py-md-0">
        <Button variant="outline-info" class="me-2" @click="refreshUsers">
          <i class="fas fa-sync-alt me-1"></i>
          Refresh
        </Button>
        <Button variant="primary" @click="openCreateModal">
          <i class="fas fa-plus me-1"></i>
          Add User
        </Button>
      </div>
    </div>

    <!-- Search and Filters -->
    <div class="row mb-4">
      <div class="col-md-4">
        <div class="search-box-wrapper">
          <div class="search-icon">
            <i class="fas fa-search"></i>
          </div>
          <input
            v-model="searchQuery"
            type="text"
            class="form-control search-input"
            placeholder="Search users by name, email, or mobile..."
            @input="handleSearch"
          />
        </div>
      </div>
      <div class="col-md-3">
        <select v-model="organizationFilter" class="form-select" @change="handleOrganizationFilter">
          <option value="">All Organizations</option>
          <option v-for="org in organizations" :key="org.id" :value="org.id">
            {{ org.name }}
          </option>
        </select>
      </div>
      <div class="col-md-2">
        <select v-model="perPage" class="form-select" @change="handlePerPageChange">
          <option value="10">10 per page</option>
          <option value="25">25 per page</option>
          <option value="50">50 per page</option>
        </select>
      </div>
    </div>

    <!-- Users Table -->
    <div class="card card-round">
      <div class="card-body p-0">
        <Table
          :data="usersStore.users"
          :headers="userHeaders"
          :loading="usersStore.loading"
          :actions="{ edit: true, delete: true }"
          @edit="openEditModal"
          @delete="openDeleteModal"
        >
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
          <template #cell-organization_name="{ row }">
            {{ row.organization ? row.organization.name : 'N/A' }}
          </template>
          <template #cell-status="{ value, row }">
            <div class="status-dropdown">
              <div class="status-indicator">
                <div class="status-dot" :class="getStatusDotClass(value)"></div>
                <div 
                  class="status-arrow" 
                  @click="toggleStatusDropdown(row.id)"
                >▼</div>
              </div>
              <div 
                v-if="activeDropdown === row.id" 
                class="status-options"
                :data-dropdown-id="row.id"
                @click.stop
              >
                <div 
                  class="status-option"
                  :class="{ active: value === 'active' }"
                  @click="updateUserStatus(row.id, 'active')"
                >
                  <div class="status-dot active"></div>
                  <span>Active</span>
                </div>
                <div 
                  class="status-option"
                  :class="{ active: value === 'inactive' }"
                  @click="updateUserStatus(row.id, 'inactive')"
                >
                  <div class="status-dot inactive"></div>
                  <span>Inactive</span>
                </div>
              </div>
            </div>
          </template>
          <template #cell-created_at="{ value }">
            {{ formatDate(value) }}
          </template>
        </Table>
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
              placeholder="Enter mobile number"
              :error="errors.mobile"
              required
            />
          </div>
          <div class="col-md-6 mb-3">
            <div class="form-group">
              <label class="form-label">
                Organization
                <span class="text-danger">*</span>
              </label>
              <select v-model="userForm.organization_id" class="form-select" :class="{ 'is-invalid': errors.organization_id }" required>
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
      <p>Are you sure you want to delete this user?</p>
      <div v-if="userToDelete" class="alert alert-warning">
        <strong>{{ userToDelete.name }}</strong><br>
        <small>{{ userToDelete.email }}</small>
      </div>
      <p class="text-danger">This action cannot be undone.</p>
    </Modal>
  </div>
</template>

<style scoped>
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
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.badge.bg-success {
  background-color: #28a745 !important;
}

.badge.bg-secondary {
  background-color: #6c757d !important;
}

/* Status Dropdown Styles */
.status-dropdown {
  position: relative;
  display: inline-block;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 2px;
  width: 24px; /* Fixed width to prevent movement */
  justify-content: space-between;
}

.status-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-dot.active {
  background-color: #28a745;
}

.status-dot.inactive {
  background-color: #dc3545;
}

.status-arrow {
  width: 12px;
  height: 12px;
  font-size: 10px;
  color: #6c757d;
  cursor: pointer;
  padding: 2px;
  border-radius: 3px;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
}

.status-arrow:hover {
  background-color: #f8f9fa;
  color: #495057;
}

.status-indicator:hover .status-arrow {
  opacity: 1;
  visibility: visible;
}

.status-options {
  position: fixed;
  background: white;
  border: 1px solid #e3e6f0;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 9999;
  overflow: hidden;
  min-width: 100px;
  max-height: 200px;
  transform: translateY(0);
}

/* Position dropdown below the indicator by default */
.status-options[data-position="down"] {
  top: var(--dropdown-top);
  left: var(--dropdown-left);
}

/* Position dropdown above the indicator when near bottom */
.status-options[data-position="up"] {
  bottom: var(--dropdown-bottom);
  left: var(--dropdown-left);
  top: auto;
}

.status-option {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  cursor: pointer;
  transition: background-color 0.2s ease;
  gap: 8px;
  font-size: 12px;
  font-weight: 500;
}

.status-option:hover {
  background-color: #f8f9fa;
}

.status-option.active {
  background-color: #e3f2fd;
  color: #1976d2;
}

.status-option.active .status-dot {
  background-color: #1976d2;
}
</style>

<script setup>
import { ref, reactive, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useUsersStore } from '@/stores/users'
import Button from '@/components/Button.vue'
import Table from '@/components/Table.vue'
import Modal from '@/components/Modal.vue'
import InputField from '@/components/InputField.vue'
import Pagination from '@/components/Pagination.vue'
import api from '@/services/api'

const usersStore = useUsersStore()

// Search and filters
const searchQuery = ref('')
const organizationFilter = ref('')
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
  organization_id: '',
  status: 'active'
})

// Organizations data
const organizations = ref([])

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

const userHeaders = [
  { key: 'name', label: 'User', class: 'text-start' },
  { key: 'mobile', label: 'Mobile', class: 'text-center' },
  { key: 'organization_name', label: 'Organization', class: 'text-center' },
  { key: 'status', label: 'Status', class: 'text-center' },
  { key: 'created_at', label: 'Created', class: 'text-center' }
]

// Methods
const fetchUsers = async () => {
  try {
    await usersStore.fetchUsers(1, searchQuery.value, organizationFilter.value)
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

const handleOrganizationFilter = () => {
  fetchUsers()
}

const handlePerPageChange = () => {
  usersStore.pagination.per_page = perPage.value
  fetchUsers()
}

const handlePageChange = (page) => {
  usersStore.fetchUsers(page, searchQuery.value, organizationFilter.value)
}

const openCreateModal = () => {
  isEditing.value = false
  resetForm()
  showUserModal.value = true
}

const openEditModal = (user) => {
  isEditing.value = true
  userForm.name = user.name
  userForm.email = user.email
  userForm.mobile = user.mobile || ''
  userForm.organization_id = user.organization_id || ''
  userForm.status = user.status || 'active'
  userForm.password = ''
  userToDelete.value = user
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
  userForm.organization_id = ''
  userForm.status = 'active'
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
  } else if (userForm.mobile.length < 10) {
    validationErrors.mobile = 'Mobile number must be at least 10 digits'
  }
  
  if (!userForm.organization_id) {
    validationErrors.organization_id = 'Organization is required'
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


const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
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

onMounted(() => {
  fetchUsers()
  fetchOrganizations()
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
