<template>
  <div class="page-container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-title">
          <h3 class="page-title">Excluded Numbers</h3>
        </div>
        <div class="header-actions">
          <button type="button" class="btn btn-light border btn-sm me-2" @click="refreshList">
            <i class="fas fa-sync-alt me-2"></i>
            <span class="btn-text">Refresh</span>
          </button>
          <button type="button" class="btn btn-success btn-sm me-2" @click="openImportModal">
            <i class="fas fa-file-csv me-2"></i>
            <span class="btn-text">Import CSV</span>
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
              placeholder="Search by phone number or label..."
              @input="handleSearch"
            />
          </div>
        </div>
        <div v-if="isAdmin" class="filter-item">
          <div class="select-wrapper">
            <i class="fas fa-building filter-icon"></i>
            <select v-model="selectedOrganization" class="form-select select-modern" @change="fetchNumbers()">
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
            <select v-model="perPage" class="form-select select-modern" @change="fetchNumbers()">
              <option value="10">10 per page</option>
              <option value="25">25 per page</option>
              <option value="50">50 per page</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card card-round">
      <div class="card-body p-0">
        <div class="table-responsive-wrapper">
          <div class="simple-table">
            <Table
              :data="numbers"
              :headers="tableHeaders"
              :loading="loading"
              :actions="{ edit: true, delete: true }"
              @edit="openEditModal"
              @delete="openDeleteModal"
            >
              <template #cell-phone_number="{ value }">
                <span class="fw-semibold">{{ value }}</span>
              </template>
              <template #cell-label="{ value }">
                <span v-if="value" class="badge bg-secondary">{{ value }}</span>
                <span v-else class="text-muted">—</span>
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

    <!-- Create / Edit Modal -->
    <Modal
      :show="showFormModal"
      :title="isEditing ? 'Edit Excluded Number' : 'Add Excluded Number'"
      size="lg"
      @close="closeFormModal"
    >
      <form @submit.prevent="handleSubmit">
        <div class="row">
          <div class="col-md-6 mb-3">
            <InputField
              v-model="form.phone_number"
              type="text"
              label="Phone Number"
              placeholder="e.g. +923001234567"
              :error="errors.phone_number"
              required
            />
          </div>
          <div class="col-md-6 mb-3">
            <InputField
              v-model="form.label"
              type="text"
              label="Label"
              placeholder="e.g. Spam, Test line"
              :error="errors.label"
            />
          </div>
          <div v-if="isAdmin" class="col-md-6 mb-3">
            <div class="form-group">
              <label class="form-label">Organization <span class="text-danger">*</span></label>
              <select
                v-model="form.organization_id"
                class="form-select"
                :class="{ 'is-invalid': errors.organization_id }"
              >
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
        <Button variant="secondary" @click="closeFormModal">Cancel</Button>
        <Button variant="primary" :loading="saving" @click="handleSubmit">
          {{ isEditing ? 'Update' : 'Add Number' }}
        </Button>
      </template>
    </Modal>

    <!-- Import CSV Modal -->
    <Modal
      :show="showImportModal"
      title="Import Excluded Numbers"
      size="lg"
      @close="closeImportModal"
    >
      <div class="mb-3">
        <p class="text-muted mb-3">
          Upload a CSV file with the following columns:
          <br><code>phone_number</code> (required), <code>label</code> (optional)
          <br><small class="text-info">Duplicate phone numbers within your organization will be skipped automatically.</small>
        </p>
        <a href="#" class="btn btn-sm btn-outline-secondary mb-3" @click.prevent="downloadTemplate">
          <i class="fas fa-download me-1"></i> Download Template
        </a>
      </div>

      <div v-if="isAdmin" class="mb-3">
        <label class="form-label">Organization <span class="text-danger">*</span></label>
        <select v-model="importOrganizationId" class="form-select" :class="{ 'is-invalid': importErrors.organization_id }">
          <option value="">Select Organization</option>
          <option v-for="org in organizations" :key="org.id" :value="org.id">{{ org.name }}</option>
        </select>
        <div v-if="importErrors.organization_id" class="invalid-feedback d-block">{{ importErrors.organization_id }}</div>
      </div>

      <div class="mb-3">
        <label class="form-label">CSV File <span class="text-danger">*</span></label>
        <input
          ref="importFileInput"
          type="file"
          class="form-control"
          :class="{ 'is-invalid': importErrors.file }"
          accept=".csv,.txt"
          @change="handleFileSelect"
        />
        <div v-if="importErrors.file" class="invalid-feedback d-block">{{ importErrors.file }}</div>
      </div>

      <div v-if="importResult" class="mt-3">
        <div class="alert" :class="importResult.errors?.length ? 'alert-warning' : 'alert-success'">
          <strong>{{ importResult.message }}</strong>
          <ul v-if="importResult.errors?.length" class="mb-0 mt-2">
            <li v-for="(err, i) in importResult.errors.slice(0, 10)" :key="i">{{ err }}</li>
            <li v-if="importResult.errors.length > 10">...and {{ importResult.errors.length - 10 }} more</li>
          </ul>
        </div>
      </div>

      <template #footer>
        <Button variant="secondary" @click="closeImportModal">Close</Button>
        <Button variant="primary" :loading="importing" @click="handleImport">
          <i class="fas fa-upload me-1"></i> Import
        </Button>
      </template>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="showDeleteModal"
      title="Delete Excluded Number"
      @close="closeDeleteModal"
      @confirm="handleDelete"
    >
      <p>Are you sure you want to delete this excluded number?</p>
      <div v-if="selectedItem" class="alert alert-warning">
        <strong>{{ selectedItem.phone_number }}</strong>
        <span v-if="selectedItem.label"> — {{ selectedItem.label }}</span>
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

.simple-table :deep(.table-responsive) {
  border: 1px solid #e3e6f0;
  border-radius: 10px;
  overflow: hidden;
}

.simple-table :deep(table.table) {
  width: 100%;
  min-width: 800px;
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

.badge {
  font-size: 0.75rem;
  font-weight: 500;
  padding: 0.375rem 0.75rem;
  border-radius: 0.375rem;
}

.badge.bg-info {
  background-color: #17a2b8 !important;
}

.badge.bg-secondary {
  background-color: #6c757d !important;
}
</style>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import Button from '@/components/Button.vue'
import Table from '@/components/Table.vue'
import Modal from '@/components/Modal.vue'
import InputField from '@/components/InputField.vue'
import Pagination from '@/components/Pagination.vue'
import api from '@/services/api'
import { showSuccess, showError } from '@/services/toast'

const authStore = useAuthStore()

const userRole = computed(() => authStore.user?.role || 'user')
const isAdmin = computed(() => userRole.value === 'admin')
const isOrganization = computed(() => userRole.value === 'organization')
const isManager = computed(() => userRole.value === 'manager')

// Data
const numbers = ref([])
const organizations = ref([])
const loading = ref(false)
const saving = ref(false)
const searchQuery = ref('')
const selectedOrganization = ref('')
const perPage = ref(10)
const searchTimeout = ref(null)

// Modal states
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const showImportModal = ref(false)
const isEditing = ref(false)
const selectedItem = ref(null)

// Import state
const importing = ref(false)
const importFileInput = ref(null)
const importFile = ref(null)
const importOrganizationId = ref('')
const importErrors = ref({})
const importResult = ref(null)

// Form
const form = reactive({
  phone_number: '',
  label: '',
  organization_id: '',
})

const errors = ref({})
const errorMessage = ref('')

// Pagination
const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
})

const tableHeaders = computed(() => {
  const headers = [
    { key: 'phone_number', label: 'Phone Number', class: 'text-start' },
    { key: 'label', label: 'Label', class: 'text-start' },
  ]
  if (isAdmin.value) {
    headers.push({ key: 'organization', label: 'Organization', class: 'text-center' })
  }
  headers.push({ key: 'created_at', label: 'Created', class: 'text-center' })
  return headers
})

const getErrorMessage = (error) => (Array.isArray(error) ? error[0] : error)

const fetchNumbers = async (page = 1) => {
  try {
    loading.value = true
    const params = new URLSearchParams({ page, per_page: perPage.value })
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (selectedOrganization.value) params.append('organization_id', selectedOrganization.value)

    const response = await api.get(`/excluded-numbers?${params}`)
    numbers.value = response.data.data
    pagination.current_page = response.data.current_page
    pagination.last_page = response.data.last_page
    pagination.per_page = response.data.per_page
    pagination.total = response.data.total
  } catch (error) {
    showError('Failed to load excluded numbers.')
  } finally {
    loading.value = false
  }
}

const fetchOrganizations = async () => {
  try {
    const response = await api.get('/excluded-numbers/organizations')
    organizations.value = response.data
    if ((isOrganization.value || isManager.value) && organizations.value.length > 0) {
      form.organization_id = organizations.value[0].id
    }
  } catch {
    showError('Failed to load organizations.')
  }
}

const refreshList = () => fetchNumbers()

// Import handlers
const openImportModal = () => {
  importFile.value = null
  importOrganizationId.value = (isOrganization.value || isManager.value) && organizations.value.length > 0
    ? organizations.value[0].id
    : ''
  importErrors.value = {}
  importResult.value = null
  showImportModal.value = true
  if (importFileInput.value) importFileInput.value.value = ''
}

const closeImportModal = () => {
  showImportModal.value = false
  importResult.value = null
}

const handleFileSelect = (e) => {
  importFile.value = e.target.files[0] || null
  importErrors.value = { ...importErrors.value, file: null }
}

const downloadTemplate = () => {
  const csv = 'phone_number,label\n+923001234567,Spam\n+923009876543,Test'
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'excluded_numbers_template.csv'
  a.click()
  URL.revokeObjectURL(url)
}

const handleImport = async () => {
  importErrors.value = {}
  importResult.value = null

  const validationErrors = {}
  if (isAdmin.value && !importOrganizationId.value) {
    validationErrors.organization_id = 'Organization is required'
  }
  if (!importFile.value) {
    validationErrors.file = 'Please select a CSV file'
  }
  if (Object.keys(validationErrors).length) {
    importErrors.value = validationErrors
    return
  }

  try {
    importing.value = true
    const formData = new FormData()
    formData.append('file', importFile.value)
    if (isAdmin.value) {
      formData.append('organization_id', importOrganizationId.value)
    }

    const response = await api.post('/excluded-numbers/import-csv', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    importResult.value = response.data
    showSuccess(response.data.message)
    fetchNumbers()
    if (importFileInput.value) importFileInput.value.value = ''
    importFile.value = null
  } catch (error) {
    if (error.response?.data?.errors) {
      importErrors.value = Object.fromEntries(
        Object.entries(error.response.data.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      )
    } else {
      showError(error.response?.data?.message || 'Failed to import CSV.')
    }
  } finally {
    importing.value = false
  }
}

const handleSearch = () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => fetchNumbers(), 500)
}

const handlePageChange = (page) => fetchNumbers(page)

const resetForm = () => {
  form.phone_number = ''
  form.label = ''
  form.organization_id = (isOrganization.value || isManager.value) && organizations.value.length > 0
    ? organizations.value[0].id
    : ''
  errors.value = {}
  errorMessage.value = ''
}

const openCreateModal = () => {
  isEditing.value = false
  selectedItem.value = null
  resetForm()
  showFormModal.value = true
}

const openEditModal = (item) => {
  isEditing.value = true
  selectedItem.value = item
  form.phone_number = item.phone_number
  form.label = item.label || ''
  form.organization_id = item.organization_id || ''
  errors.value = {}
  errorMessage.value = ''
  showFormModal.value = true
}

const openDeleteModal = (item) => {
  selectedItem.value = item
  showDeleteModal.value = true
}

const closeFormModal = () => {
  showFormModal.value = false
  resetForm()
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  selectedItem.value = null
}

const handleSubmit = async () => {
  errors.value = {}
  errorMessage.value = ''

  const validationErrors = {}
  if (!form.phone_number) {
    validationErrors.phone_number = 'Phone number is required'
  }
  if (isAdmin.value && !form.organization_id) {
    validationErrors.organization_id = 'Organization is required'
  }

  if (Object.keys(validationErrors).length > 0) {
    errors.value = validationErrors
    return
  }

  try {
    saving.value = true
    if (isEditing.value) {
      await api.put(`/excluded-numbers/${selectedItem.value.id}`, form)
      showSuccess('Excluded number updated successfully!')
    } else {
      await api.post('/excluded-numbers', form)
      showSuccess('Excluded number added successfully!')
    }
    closeFormModal()
    fetchNumbers()
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = 'Error saving. Please try again.'
      showError('Failed to save excluded number.')
    }
  } finally {
    saving.value = false
  }
}

const handleDelete = async () => {
  try {
    loading.value = true
    await api.delete(`/excluded-numbers/${selectedItem.value.id}`)
    showSuccess('Excluded number deleted successfully!')
    closeDeleteModal()
    fetchNumbers()
  } catch {
    showError('Failed to delete excluded number.')
  } finally {
    loading.value = false
  }
}

const formatDate = (date) => new Date(date).toLocaleDateString()

watch(perPage, () => fetchNumbers())

onMounted(() => {
  fetchNumbers()
  fetchOrganizations()
})

onUnmounted(() => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
})
</script>
