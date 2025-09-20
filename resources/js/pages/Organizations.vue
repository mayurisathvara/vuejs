<template>
  <div>
    <!-- Page Header -->
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
      <div>
        <h3 class="fw-bold mb-3">Organizations Management</h3>
      </div>
      <div class="ms-md-auto py-2 py-md-0">
        <Button variant="outline-info" class="me-2" @click="refreshOrganizations">
          <i class="fas fa-sync-alt me-1"></i>
          Refresh
        </Button>
        <Button variant="primary" @click="openCreateModal">
          <i class="fas fa-plus me-1"></i>
          Add Organization
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
            placeholder="Search organizations by name, email, or mobile..."
            @input="handleSearch"
          />
        </div>
      </div>
      <div class="col-md-2">
        <select v-model="perPage" class="form-select" @change="handlePerPageChange">
          <option value="10">10 per page</option>
          <option value="25">25 per page</option>
          <option value="50">50 per page</option>
        </select>
      </div>
    </div>

    <!-- Organizations Table -->
    <div class="card card-round">
      <div class="card-body p-0">
        <Table
          :data="organizationsStore.organizations"
          :headers="organizationHeaders"
          :loading="organizationsStore.loading"
          :actions="{ edit: true, delete: true }"
          @edit="openEditModal"
          @delete="openDeleteModal"
        >
          <template #cell-name="{ row }">
            <div class="d-flex align-items-center">
              <div class="avatar-sm me-2">
                <div class="avatar-img rounded-circle bg-success d-flex align-items-center justify-content-center text-white fw-bold" style="width: 32px; height: 32px;">
                  {{ row.name?.charAt(0) || 'O' }}
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
                  @click="updateOrganizationStatus(row.id, 'active')"
                >
                  <div class="status-dot active"></div>
                  <span>Active</span>
                </div>
                <div 
                  class="status-option"
                  :class="{ active: value === 'inactive' }"
                  @click="updateOrganizationStatus(row.id, 'inactive')"
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
        Showing {{ (organizationsStore.pagination.current_page - 1) * organizationsStore.pagination.per_page + 1 }} to 
        {{ Math.min(organizationsStore.pagination.current_page * organizationsStore.pagination.per_page, organizationsStore.pagination.total) }} 
        of {{ organizationsStore.pagination.total }} entries
      </div>
      <Pagination
        :current-page="organizationsStore.pagination.current_page"
        :total-pages="organizationsStore.pagination.last_page"
        @page-change="handlePageChange"
      />
    </div>

    <!-- Create/Edit Organization Modal -->
    <Modal
      :show="showOrganizationModal"
      :title="isEditing ? 'Edit Organization' : 'Create Organization'"
      size="lg"
      @close="closeOrganizationModal"
    >
      <form @submit.prevent="handleSubmit">
        <div class="row">
          <div class="col-md-6 mb-3">
            <InputField
              v-model="organizationForm.name"
              type="text"
              label="Organization Name"
              placeholder="Enter organization name"
              :error="errors.name"
              required
            />
          </div>
          <div class="col-md-6 mb-3">
            <InputField
              v-model="organizationForm.email"
              type="email"
              label="Email Address"
              placeholder="Enter email address"
              :error="errors.email"
              required
            />
          </div>
          <div class="col-md-6 mb-3">
            <InputField
              v-model="organizationForm.mobile"
              type="tel"
              label="Mobile Number"
              placeholder="Enter mobile number"
              :error="errors.mobile"
              required
            />
          </div>
          <div class="col-md-6 mb-3">
            <div class="form-group">
            <label class="form-label">Status</label>
            <select v-model="organizationForm.status" class="form-select" :class="{ 'is-invalid': errors.status }">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
            <div v-if="errors.status" class="invalid-feedback d-block">
              {{ getErrorMessage(errors.status) }}
            </div>
          </div>
        </div>
          <div class="col-md-6 mb-3">
            <InputField
              v-model="organizationForm.password"
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
          <label class="form-label">Description</label>
          <textarea
            v-model="organizationForm.description"
            class="form-control"
            rows="3"
            placeholder="Enter organization description"
            :class="{ 'is-invalid': errors.description }"
          ></textarea>
          <div v-if="errors.description" class="invalid-feedback d-block">
            {{ getErrorMessage(errors.description) }}
              </div>
            </div>
          </div>
        </div>

        <div v-if="errorMessage" class="alert alert-danger" role="alert">
          {{ errorMessage }}
        </div>
      </form>

      <template #footer>
        <Button variant="secondary" @click="closeOrganizationModal">Cancel</Button>
        <Button
          variant="primary"
          :loading="organizationsStore.loading"
          @click="handleSubmit"
        >
          {{ isEditing ? 'Update Organization' : 'Create Organization' }}
        </Button>
      </template>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="showDeleteModal"
      title="Delete Organization"
      @close="closeDeleteModal"
      @confirm="handleDelete"
    >
      <p>Are you sure you want to delete this organization?</p>
      <div v-if="organizationToDelete" class="alert alert-warning">
        <strong>{{ organizationToDelete.name }}</strong><br>
        <small>{{ organizationToDelete.email }}</small>
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
import { useOrganizationsStore } from '@/stores/organizations'
import Button from '@/components/Button.vue'
import Table from '@/components/Table.vue'
import Modal from '@/components/Modal.vue'
import InputField from '@/components/InputField.vue'
import Pagination from '@/components/Pagination.vue'
import api from '@/services/api'

const organizationsStore = useOrganizationsStore()

// Search and filters
const searchQuery = ref('')
const perPage = ref(10)
const searchTimeout = ref(null)

// Status dropdown state
const activeDropdown = ref(null)

// Modal states
const showOrganizationModal = ref(false)
const showDeleteModal = ref(false)
const isEditing = ref(false)
const organizationToDelete = ref(null)

// Form data
const organizationForm = reactive({
  name: '',
  email: '',
  mobile: '',
  password: '',
  description: '',
  status: 'active'
})

const errors = ref({})
const errorMessage = ref('')

// Helper function to get error message
const getErrorMessage = (error) => {
  if (Array.isArray(error)) {
    return error[0]
  }
  return error
}

const organizationHeaders = [
  { key: 'name', label: 'Organization', class: 'text-start' },
  { key: 'mobile', label: 'Mobile', class: 'text-center' },
  { key: 'status', label: 'Status', class: 'text-center' },
  { key: 'created_at', label: 'Created', class: 'text-center' }
]

// Methods
const fetchOrganizations = async () => {
  try {
    await organizationsStore.fetchOrganizations(1, searchQuery.value)
  } catch (error) {
    console.error('Error fetching organizations:', error)
  }
}

const refreshOrganizations = () => {
  fetchOrganizations()
}

const handleSearch = () => {
  // Clear existing timeout
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  
  // Set new timeout for debounced search
  searchTimeout.value = setTimeout(() => {
    fetchOrganizations()
  }, 500) // 500ms delay
}

const handlePerPageChange = () => {
  organizationsStore.pagination.per_page = perPage.value
  fetchOrganizations()
}

const handlePageChange = (page) => {
  organizationsStore.fetchOrganizations(page, searchQuery.value)
}

const openCreateModal = () => {
  isEditing.value = false
  resetForm()
  showOrganizationModal.value = true
}

const openEditModal = (organization) => {
  isEditing.value = true
  organizationForm.name = organization.name
  organizationForm.email = organization.email
  organizationForm.mobile = organization.mobile || ''
  organizationForm.description = organization.description || ''
  organizationForm.status = organization.status
  organizationForm.password = ''
  organizationToDelete.value = organization
  showOrganizationModal.value = true
}

const openDeleteModal = (organization) => {
  organizationToDelete.value = organization
  showDeleteModal.value = true
}

const closeOrganizationModal = () => {
  showOrganizationModal.value = false
  resetForm()
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  organizationToDelete.value = null
}

const resetForm = () => {
  organizationForm.name = ''
  organizationForm.email = ''
  organizationForm.mobile = ''
  organizationForm.password = ''
  organizationForm.description = ''
  organizationForm.status = 'active'
  errors.value = {}
  errorMessage.value = ''
}

const handleSubmit = async () => {
  errors.value = {}
  errorMessage.value = ''

  // Collect all validation errors
  const validationErrors = {}
  
  if (!organizationForm.name) {
    validationErrors.name = 'Organization name is required'
  } else if (organizationForm.name.length < 2) {
    validationErrors.name = 'Organization name must be at least 2 characters'
  }
  
  if (!organizationForm.email) {
    validationErrors.email = 'Email is required'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(organizationForm.email)) {
    validationErrors.email = 'Please enter a valid email address'
  }
  
  if (!organizationForm.mobile) {
    validationErrors.mobile = 'Mobile number is required'
  } else if (organizationForm.mobile.length < 10) {
    validationErrors.mobile = 'Mobile number must be at least 10 digits'
  }
  
  if (!isEditing.value && !organizationForm.password) {
    validationErrors.password = 'Password is required'
  } else if (organizationForm.password && organizationForm.password.length < 6) {
    validationErrors.password = 'Password must be at least 6 characters'
  }

  // If there are validation errors, show them all and return
  if (Object.keys(validationErrors).length > 0) {
    errors.value = validationErrors
    return
  }

  try {
    if (isEditing.value) {
      await organizationsStore.updateOrganization(organizationToDelete.value.id, organizationForm)
    } else {
      await organizationsStore.createOrganization(organizationForm)
    }
    closeOrganizationModal()
    fetchOrganizations()
  } catch (error) {
    console.error('Error saving organization:', error)
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = 'Error saving organization. Please try again.'
    }
  }
}

const toggleStatusDropdown = (organizationId) => {
  if (activeDropdown.value === organizationId) {
    activeDropdown.value = null
  } else {
    activeDropdown.value = organizationId
    // Adjust dropdown position after it's rendered
    nextTick(() => {
      adjustDropdownPosition(organizationId)
    })
  }
}

const adjustDropdownPosition = (organizationId) => {
  const dropdown = document.querySelector(`[data-dropdown-id="${organizationId}"]`)
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

const updateOrganizationStatus = async (organizationId, newStatus) => {
  try {
    await api.put(`/organizations/${organizationId}/status`, { status: newStatus })
    // Close dropdown and refresh the organizations list
    activeDropdown.value = null
    fetchOrganizations()
  } catch (error) {
    console.error('Error updating organization status:', error)
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
    await organizationsStore.deleteOrganization(organizationToDelete.value.id)
    closeDeleteModal()
    fetchOrganizations()
  } catch (error) {
    console.error('Error deleting organization:', error)
  }
}

const getStatusBadgeClass = (status) => {
  return status === 'active' 
    ? 'bg-success text-white' 
    : 'bg-secondary text-white'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

// Watch for changes in perPage
watch(perPage, () => {
  fetchOrganizations()
})

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.status-dropdown')) {
    activeDropdown.value = null
  }
}

onMounted(() => {
  fetchOrganizations()
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

