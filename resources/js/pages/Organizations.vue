<template>
  <div class="page-container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-title">
          <h3 class="page-title">Organizations Management</h3>
        </div>
        <div class="header-actions">
          <button type="button" class="btn btn-light border btn-sm me-2" @click="refreshOrganizations">
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
      <div class="filters-container filters-container-simple">
        <div class="filter-item filter-search">
          <div class="search-box-modern">
            <i class="fas fa-search search-icon-modern"></i>
            <input
              v-model="searchQuery"
              type="text"
              class="form-control search-input-modern"
              placeholder="Search organizations by name, email, or mobile..."
              @input="handleSearch"
            />
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

    <!-- Organizations Table -->
    <div class="card card-round">
      <div class="card-body p-0">
        <div class="table-responsive-wrapper">
          <div class="simple-table">
            <Table
              :data="organizationsStore.organizations"
              :headers="organizationHeaders"
              :loading="organizationsStore.loading"
              :actions="{ edit: true, delete: true }"
              @edit="openEditModal"
              @delete="openDeleteModal"
            >
          <template #actions="{ row }">
            <div class="action-buttons">
              <button
                class="action-btn settings-btn"
                type="button"
                @click="goToSettings(row.id)"
                title="Settings"
              >
                <i class="fas fa-cog"></i>
              </button>
              <button
                class="action-btn edit-btn"
                type="button"
                @click="openEditModal(row)"
                title="Edit"
              >
                <i class="fas fa-edit"></i>
              </button>
              <button
                class="action-btn delete-btn"
                type="button"
                @click="openDeleteModal(row)"
                title="Delete"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </template>
          <template #cell-name="{ row }">
            <div>
              <div class="fw-bold">{{ row.name }}</div>
              <small class="text-muted">{{ row.email || 'N/A' }}</small>
            </div>
          </template>
          <template #cell-contact_person="{ row }">
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
          <template #cell-app_login_code="{ value }">
            {{ value || '—' }}
          </template>
          <template #cell-sims_count="{ value }">
            {{ Number(value || 0).toLocaleString() }}
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
                  @click="updateOrganizationStatus(row.id, 'active')"
                  type="button"
                >
                  <span class="status-dot status-dot-active"></span>
                  <span>Active</span>
                  <i v-if="value === 'active'" class="fas fa-check ms-auto"></i>
                </button>
                <button 
                  class="status-dropdown-item"
                  :class="{ 'active': value === 'inactive' }"
                  @click="updateOrganizationStatus(row.id, 'inactive')"
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
            <div class="form-group">
              <label class="form-label">App Login Code <span class="text-danger">*</span></label>
              <div class="input-group app-login-code-group">
                <input
                  v-model="organizationForm.app_login_code"
                  type="text"
                  class="form-control"
                  readonly
                  :class="{ 'is-invalid': errors.app_login_code }"
                />
                <button
                  v-if="isEditing"
                  type="button"
                  class="btn btn-light border"
                  @click="regenerateAppLoginCode"
                >
                  Regenerate
                </button>
              </div>
              <div v-if="errors.app_login_code" class="invalid-feedback d-block">
                {{ getErrorMessage(errors.app_login_code) }}
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <InputField
              v-model="organizationForm.mobile"
              type="tel"
              label="Mobile Number"
              placeholder="Enter 10-digit mobile number"
              :error="errors.mobile"
              maxlength="10"
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
      <div class="delete-modal-content">
        <p class="delete-question">Are you sure you want to delete this organization?</p>
        <div v-if="organizationToDelete" class="info-card">
          <div class="info-name">{{ organizationToDelete.name }}</div>
          <div class="info-email">{{ organizationToDelete.email }}</div>
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

.info-card {
  background-color: #f8f9fc;
  border: 1px solid #e3e6f0;
  border-radius: 8px;
  padding: 16px 20px;
  margin-bottom: 20px;
}

.info-name {
  font-size: 16px;
  font-weight: 600;
  color: #2c2d3a;
  margin-bottom: 6px;
}

.info-email {
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

.app-login-code-group .form-control {
  border-right: 0;
}

.app-login-code-group .btn {
  white-space: nowrap;
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

/* Actions (duplicated here because Table.vue styles are scoped) */
.action-buttons {
  display: flex;
  gap: 6px;
  justify-content: flex-end;
  align-items: center;
}

.action-btn {
  width: 30px;
  height: 30px;
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
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.action-btn:active {
  transform: translateY(0);
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.settings-btn {
  color: #6c757d;
}

.settings-btn:hover {
  background: #6c757d;
  border-color: #6c757d;
  color: #fff;
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
import { ref, reactive, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useOrganizationsStore } from '@/stores/organizations'
import { encryptId } from '@/utils/encryption'
import Button from '@/components/Button.vue'
import Table from '@/components/Table.vue'
import Modal from '@/components/Modal.vue'
import InputField from '@/components/InputField.vue'
import Pagination from '@/components/Pagination.vue'
import api from '@/services/api'

const organizationsStore = useOrganizationsStore()
const router = useRouter()

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
  app_login_code: '',
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
  { key: 'app_login_code', label: 'App Login Code', class: 'text-center' },
  { key: 'sims_count', label: 'SIMs', class: 'text-center' },
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

const goToSettings = (organizationId) => {
  router.push({ name: 'OrganizationSettings', params: { organizationId: encryptId(organizationId) } })
}

const openCreateModal = () => {
  isEditing.value = false
  resetForm()
  organizationForm.app_login_code = generateAppLoginCode('')
  showOrganizationModal.value = true
}

const openEditModal = (organization) => {
  isEditing.value = true
  organizationForm.name = organization.name
  organizationForm.email = organization.email
  organizationForm.mobile = organization.mobile || ''
  organizationForm.description = organization.description || ''
  organizationForm.status = organization.status
  organizationForm.app_login_code = organization.app_login_code || ''
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
  organizationForm.app_login_code = ''
  organizationForm.password = ''
  organizationForm.description = ''
  organizationForm.status = 'active'
  errors.value = {}
  errorMessage.value = ''
}

const generateAppLoginCode = (orgName = '') => {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'
  
  // Extract first 3 letters from organization name
  let prefix = 'ORG'
  if (orgName) {
    // Remove special characters and get only letters
    const cleanName = orgName.replace(/[^a-zA-Z]/g, '').toUpperCase()
    if (cleanName.length >= 3) {
      prefix = cleanName.slice(0, 3)
    } else if (cleanName.length > 0) {
      // If less than 3 letters, pad with random characters
      prefix = cleanName
      while (prefix.length < 3) {
        prefix += chars.charAt(Math.floor(Math.random() * chars.length))
      }
    }
  }
  
  // Generate random 8 characters for uniqueness
  let raw = ''
  for (let i = 0; i < 8; i++) {
    raw += chars.charAt(Math.floor(Math.random() * chars.length))
  }
  
  return `${prefix}-${raw.slice(0, 4)}-${raw.slice(4, 8)}`
}

const regenerateAppLoginCode = () => {
  organizationForm.app_login_code = generateAppLoginCode(organizationForm.name)
  // Clear any previous validation error once regenerated
  if (errors.value?.app_login_code) {
    const next = { ...(errors.value || {}) }
    delete next.app_login_code
    errors.value = next
  }
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
  } else if (!/^\d{10}$/.test(organizationForm.mobile)) {
    validationErrors.mobile = 'Mobile number must be exactly 10 digits'
  }
  
  if (!isEditing.value && !organizationForm.password) {
    validationErrors.password = 'Password is required'
  } else if (organizationForm.password && organizationForm.password.length < 6) {
    validationErrors.password = 'Password must be at least 6 characters'
  }

  if (!organizationForm.app_login_code) {
    validationErrors.app_login_code = 'App login code is required'
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

// Watch for organization name changes to auto-update app_login_code during creation
watch(() => organizationForm.name, (newName) => {
  // Only auto-generate when creating new organization (not editing)
  if (!isEditing.value && newName) {
    organizationForm.app_login_code = generateAppLoginCode(newName)
  }
})

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

