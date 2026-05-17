<template>
  <div class="page-container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-title">
          <h3 class="page-title">Excluded Numbers</h3>
        </div>
        <div class="header-actions">
          <button type="button" class="hdr-btn hdr-btn--ghost" @click="refreshList">
            <i class="fas fa-sync-alt"></i><span>Refresh</span>
          </button>
          <button type="button" class="hdr-btn hdr-btn--export" @click="handleExportCsv">
            <i class="fas fa-file-export"></i><span>Export</span>
          </button>
          <button type="button" class="hdr-btn hdr-btn--import" @click="openImportModal">
            <i class="fas fa-file-import"></i><span>Import CSV</span>
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
    <div class="card card-round users-table-card">
      <div class="card-body p-0">
        <div class="table-responsive-wrapper">
          <div class="simple-table">
            <Table
              :data="numbers"
              :headers="tableHeaders"
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
              type="tel"
              label="Phone Number"
              placeholder="Enter 10-digit phone number"
              :error="errors.phone_number"
              maxlength="10"
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
.hdr-btn--export { background: #dcfce7; color: #15803d; }
.hdr-btn--export:hover { background: #bbf7d0; }
.hdr-btn--import { background: #dbeafe; color: #1d4ed8; }
.hdr-btn--import:hover { background: #bfdbfe; }
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
import { formatDateDisplay, formatExportDate } from '@/utils/dateFormatter'

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
const activeActionMenu = ref(null)

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

const escapeCsv = (value) => {
  if (value === null || value === undefined) return ''
  return String(value).replace(/"/g, '""')
}

const fetchAllNumbersForExport = async () => {
  const allRows = []
  let page = 1
  let lastPage = 1

  do {
    const params = new URLSearchParams({
      page: String(page),
      per_page: '1000'
    })

    if (searchQuery.value) params.append('search', searchQuery.value)
    if (selectedOrganization.value) params.append('organization_id', selectedOrganization.value)

    const response = await api.get(`/excluded-numbers?${params}`)
    const pageRows = response.data?.data || []
    allRows.push(...pageRows)

    lastPage = Number(response.data?.last_page || 1)
    page += 1
  } while (page <= lastPage)

  return allRows
}

const handleExportCsv = async () => {
  try {
    const rows = await fetchAllNumbersForExport()

    if (!rows.length) {
      showError('No excluded numbers available to export.')
      return
    }

    const headers = isAdmin.value
      ? ['Phone Number', 'Label', 'Organization', 'Created']
      : ['Phone Number', 'Label', 'Created']

    const csvLines = [headers.join(',')]

    rows.forEach((row) => {
      const baseColumns = [
        `"${escapeCsv(row.phone_number || '')}"`,
        `"${escapeCsv(row.label || '')}"`
      ]

      const createdDate = formatExportDate(row.created_at, '')

      if (isAdmin.value) {
        baseColumns.push(`"${escapeCsv(row.organization?.name || 'N/A')}"`)
      }

      baseColumns.push(`"${escapeCsv(createdDate)}"`)
      csvLines.push(baseColumns.join(','))
    })

    const csvContent = csvLines.join('\n')
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    const stamp = formatExportDate(new Date(), 'export')

    link.setAttribute('href', url)
    link.setAttribute('download', `excluded_numbers_export_${stamp}.csv`)
    link.style.visibility = 'hidden'

    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)

    showSuccess(`Exported ${rows.length} excluded number(s) successfully.`)
  } catch (error) {
    showError(error.response?.data?.message || 'Failed to export excluded numbers.')
  }
}

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

const openEditFromMenu = (item) => {
  activeActionMenu.value = null
  openEditModal(item)
}

const openDeleteFromMenu = (item) => {
  activeActionMenu.value = null
  openDeleteModal(item)
}

const toggleActionMenu = (itemId) => {
  activeActionMenu.value = activeActionMenu.value === itemId ? null : itemId
}

const handleClickOutside = (event) => {
  if (!event.target.closest('.action-buttons')) {
    activeActionMenu.value = null
  }
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
  } else if (!/^\d{10}$/.test(form.phone_number)) {
    validationErrors.phone_number = 'Phone number must be exactly 10 digits'
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

const formatDate = (date) => formatDateDisplay(date)

watch(perPage, () => fetchNumbers())

onMounted(() => {
  fetchNumbers()
  fetchOrganizations()
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  document.removeEventListener('click', handleClickOutside)
})
</script>
