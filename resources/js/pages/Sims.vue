<template>
  <div class="page-container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-title">
          <h3 class="page-title">SIM Management</h3>
        </div>
        <div class="header-actions">
          <Button variant="outline-info" class="btn-modern me-2" @click="refreshSims">
            <i class="fas fa-sync-alt me-2"></i>
            <span class="btn-text">Refresh</span>
          </Button>
          <Button 
            v-if="selectedSims.length > 0" 
            variant="danger" 
            class="btn-modern me-2" 
            @click="openBulkDeleteModal"
          >
            <i class="fas fa-trash-alt me-2"></i>
            <span class="btn-text">Delete Selected ({{ selectedSims.length }})</span>
          </Button>
          <button type="button" class="btn btn-sm sim-pill-btn sim-pill-success me-2" @click="handleExportCsv">
            <span class="btn-text">Export</span>
          </button>
          <button type="button" class="btn btn-sm sim-pill-btn sim-pill-info me-2" @click="openImportModal">
            <i class="fas fa-file-import me-2"></i>
            <span class="btn-text">Import</span>
          </button>
          <Button variant="primary" class="btn-modern" @click="openCreateModal">
            <i class="fas fa-plus me-2"></i>
            <span class="btn-text">Add SIM</span>
          </Button>
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
              placeholder="Search by mobile number or name..."
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
            <select v-model="departmentFilter" class="form-select select-modern" @change="handleDepartmentFilter">
              <option value="">All Departments</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
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

    <!-- SIMs Table -->
    <div class="card card-round">
      <div class="card-body p-0">
        <Table
          :data="simsStore.sims"
          :headers="simHeaders"
          :loading="simsStore.loading"
          :actions="{ edit: true, delete: true }"
          :checkboxes="true"
          :selectedRows="selectedSims"
          @edit="openEditModal"
          @delete="openDeleteModal"
          @selection-change="handleSelectionChange"
        >
          <template #cell-mobile="{ value }">
            <div class="d-flex align-items-center">
              <i class="fas fa-mobile-alt me-2 text-primary"></i>
              <span class="fw-bold">{{ value }}</span>
            </div>
          </template>
          <template #cell-name="{ value }">
            {{ value || 'N/A' }}
          </template>
          <template #cell-organization_name="{ row }">
            {{ row.organization ? row.organization.name : 'N/A' }}
          </template>
          <template #cell-department_name="{ row }">
            <span v-if="row.department" class="badge bg-info">{{ row.department.name }}</span>
            <span v-else class="text-muted">N/A</span>
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
        Showing {{ (simsStore.pagination.current_page - 1) * simsStore.pagination.per_page + 1 }} to 
        {{ Math.min(simsStore.pagination.current_page * simsStore.pagination.per_page, simsStore.pagination.total) }} 
        of {{ simsStore.pagination.total }} entries
      </div>
      <Pagination
        :current-page="simsStore.pagination.current_page"
        :total-pages="simsStore.pagination.last_page"
        @page-change="handlePageChange"
      />
    </div>

    <!-- Create/Edit SIM Modal -->
    <Modal
      :show="showSimModal"
      :title="isEditing ? 'Edit SIM' : 'Create SIM'"
      size="lg"
      @close="closeSimModal"
    >
      <form @submit.prevent="handleSubmit">
        <div class="row">
          <div class="col-md-6 mb-3">
            <InputField
              v-model="simForm.mobile"
              type="text"
              label="Mobile Number"
              placeholder="Enter mobile number"
              :error="errors.mobile"
              required
            />
          </div>
          <div class="col-md-6 mb-3">
            <InputField
              v-model="simForm.name"
              type="text"
              label="Employee Name"
              placeholder="Enter employee name"
              :error="errors.name"
              required
            />
          </div>
          <div v-if="isAdmin" class="col-md-6 mb-3">
            <div class="form-group">
              <label class="form-label">
                Organization
                <span class="text-danger">*</span>
              </label>
              <select v-model="simForm.organization_id" class="form-select" :class="{ 'is-invalid': errors.organization_id }" @change="handleOrganizationChange" required>
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
                Department
                <span class="text-danger">*</span>
              </label>
              <select v-model="simForm.department_id" class="form-select" :class="{ 'is-invalid': errors.department_id }" :disabled="!simForm.organization_id" required>
                <option value="">Select Department</option>
                <option v-for="dept in availableDepartments" :key="dept.id" :value="dept.id">
                  {{ dept.name }}
                </option>
              </select>
              <div v-if="errors.department_id" class="invalid-feedback d-block">
                {{ getErrorMessage(errors.department_id) }}
              </div>
            </div>
          </div>
        </div>

        <div v-if="errorMessage" class="alert alert-danger" role="alert">
          {{ errorMessage }}
        </div>
      </form>

      <template #footer>
        <Button variant="secondary" @click="closeSimModal">Cancel</Button>
        <Button
          variant="primary"
          :loading="simsStore.loading"
          @click="handleSubmit"
        >
          {{ isEditing ? 'Update SIM' : 'Create SIM' }}
        </Button>
      </template>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="showDeleteModal"
      title="Delete SIM"
      @close="closeDeleteModal"
      @confirm="handleDelete"
    >
      <div class="delete-modal-content">
        <p class="delete-question">Are you sure you want to delete this SIM?</p>
        <div v-if="simToDelete" class="sim-info-card">
          <div class="sim-mobile">{{ simToDelete.mobile }}</div>
          <div class="sim-name">{{ simToDelete.name }}</div>
        </div>
        <p class="warning-text">
          <i class="fas fa-exclamation-triangle me-2"></i>
          This action cannot be undone.
        </p>
      </div>
    </Modal>

    <!-- Bulk Delete Confirmation Modal -->
    <Modal
      :show="showBulkDeleteModal"
      title="Confirm Bulk Delete"
      @close="closeBulkDeleteModal"
      size="md"
      :showFooter="false"
    >
      <div class="delete-modal-content">
        <p class="delete-question">Are you sure you want to delete <strong>{{ selectedSims.length }}</strong> selected SIM(s)?</p>
        <div class="bulk-info-card">
          <i class="fas fa-exclamation-triangle icon-warning"></i>
          <span>This will permanently delete all selected SIM records.</span>
        </div>
        <p class="warning-text">
          <i class="fas fa-exclamation-triangle me-2"></i>
          This action cannot be undone.
        </p>
        
        <div class="d-flex justify-content-end gap-2 mt-4">
          <Button variant="secondary" @click="closeBulkDeleteModal">Cancel</Button>
          <Button variant="danger" @click="handleBulkDelete">
            <i class="fas fa-trash-alt me-2"></i>Delete {{ selectedSims.length }} SIM(s)
          </Button>
        </div>
      </div>
    </Modal>

    <!-- Import CSV Modal -->
    <Modal
      :show="showImportModal"
      title="Import SIMs from CSV"
      size="lg"
      @close="closeImportModal"
      :showFooter="false"
    >
      <div class="import-modal-wrapper">
        <!-- CSV Requirements Info -->
        <div class="alert alert-info mb-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center">
              <i class="fas fa-info-circle me-2" style="font-size: 1.25rem;"></i>
              <h6 class="mb-0 fw-bold">CSV Format Requirements</h6>
            </div>
            <button class="btn btn-success btn-sm fw-semibold" @click="downloadSampleCsv">
              <i class="fas fa-download me-2"></i>
              Download Sample CSV
            </button>
          </div>
          <p class="mb-3">Your CSV file must include the following columns:</p>
          <div class="row g-3">
            <div class="col-md-6" v-if="isAdmin">
              <div class="csv-column-info">
                <i class="fas fa-building me-2"></i>
                <div>
                  <strong class="d-block">organization</strong>
                  <small class="text-muted">Organization name</small>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="csv-column-info">
                <i class="fas fa-sitemap me-2"></i>
                <div>
                  <strong class="d-block">department</strong>
                  <small class="text-muted">Department name</small>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="csv-column-info">
                <i class="fas fa-mobile-alt me-2"></i>
                <div>
                  <strong class="d-block">mobile</strong>
                  <small class="text-muted">Unique mobile number</small>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="csv-column-info">
                <i class="fas fa-tag me-2"></i>
                <div>
                  <strong class="d-block">name</strong>
                  <small class="text-muted">SIM card name</small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- File Upload Section -->
        <div class="file-upload-section">
          <label class="section-label">Select CSV File <span class="required-mark">*</span></label>
          <div class="file-drop-area" :class="{ 'has-file': selectedFile }">
            <input
              type="file"
              class="file-input-hidden"
              accept=".csv"
              @change="handleFileSelect"
              ref="fileInput"
            />
            <div v-if="!selectedFile" class="drop-placeholder">
              <i class="fas fa-cloud-upload-alt"></i>
              <p class="drop-text">Click to browse or drag & drop CSV file</p>
              <p class="drop-hint">Maximum size: 10MB</p>
            </div>
            <div v-else class="file-selected">
              <div class="file-icon">
                <i class="fas fa-file-alt"></i>
              </div>
              <div class="file-info">
                <div class="file-name">{{ selectedFile.name }}</div>
                <div class="file-size">{{ (selectedFile.size / 1024).toFixed(2) }} KB</div>
              </div>
              <button class="btn-remove-file" @click.prevent="clearFile" type="button">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Progress Section -->
        <div v-if="importProgress.uploading" class="progress-section">
          <div class="progress-info">
            <span><i class="fas fa-spinner fa-spin me-2"></i>Processing CSV...</span>
            <strong>{{ importProgress.percent }}%</strong>
          </div>
          <div class="progress" style="height: 8px; border-radius: 10px;">
            <div
              class="progress-bar bg-success progress-bar-striped progress-bar-animated"
              :style="{ width: importProgress.percent + '%' }"
            ></div>
          </div>
        </div>

        <!-- Results Section -->
        <div v-if="importResults" class="results-section">
          <div v-if="importResults.imported > 0" class="success-card">
            <div class="result-icon success-icon">
              <i class="fas fa-check-circle"></i>
            </div>
            <div class="result-content">
              <div class="result-title">Import Successful!</div>
              <div class="result-subtitle">
                Imported {{ importResults.imported }} of {{ importResults.total_rows }} SIM(s)
              </div>
            </div>
          </div>

          <div v-if="importResults.errors && importResults.errors.length > 0" class="error-card">
            <div class="error-header">
              <i class="fas fa-exclamation-circle"></i>
              <strong>{{ importResults.errors.length }} Record(s) Failed</strong>
            </div>
            <div class="error-body">
              <div class="error-download-section">
                <div class="error-summary">
                  <i class="fas fa-info-circle me-2"></i>
                  <span>Some records failed to import. Download the error log to see details.</span>
                </div>
                <button class="btn btn-danger btn-sm mt-3" @click="downloadFailedRecords">
                  <i class="fas fa-download me-2"></i>
                  Download Failed Logs
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="modal-footer-actions">
          <Button variant="secondary" @click="closeImportModal">
            {{ importResults ? 'Close' : 'Cancel' }}
          </Button>
          <Button
            v-if="!importResults"
            variant="warning"
            @click="handleImportCsv"
            :disabled="!selectedFile || importProgress.uploading"
          >
            <i class="fas fa-upload me-2"></i>Start Import
          </Button>
          <Button
            v-else
            variant="success"
            @click="resetImport"
          >
            <i class="fas fa-redo me-2"></i>Import Another
          </Button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<style scoped>
/* Badge styles are defined globally in app.css */

/* Header pill action buttons (Export/Import) */
.sim-pill-btn {
  border-radius: 999px;
  padding: 8px 16px;
  font-weight: 600;
  border: 0;
  box-shadow: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.sim-pill-success {
  background-color: rgba(var(--bs-success-rgb), 0.15);
  color: var(--bs-success);
}

.sim-pill-success:hover {
  background-color: rgba(var(--bs-success-rgb), 0.22);
  color: var(--bs-success);
}

.sim-pill-info {
  background-color: rgba(var(--bs-info-rgb), 0.15);
  color: var(--bs-info);
}

.sim-pill-info:hover {
  background-color: rgba(var(--bs-info-rgb), 0.22);
  color: var(--bs-info);
}

/* Delete Modal Styling */
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

.sim-info-card {
  background-color: #f8f9fc;
  border: 1px solid #e3e6f0;
  border-radius: 8px;
  padding: 16px 20px;
  margin-bottom: 20px;
}

.sim-mobile {
  font-size: 16px;
  font-weight: 600;
  color: #2c2d3a;
  margin-bottom: 6px;
}

.sim-name {
  font-size: 14px;
  color: #6c757d;
}

.bulk-info-card {
  background-color: #fff3cd;
  border: 1px solid #ffc107;
  border-radius: 8px;
  padding: 14px 16px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.bulk-info-card .icon-warning {
  color: #856404;
  font-size: 18px;
  flex-shrink: 0;
}

.bulk-info-card span {
  color: #856404;
  font-size: 14px;
  font-weight: 500;
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

/* Import Modal Styles */
.import-modal-wrapper {
  padding: 0.5rem 0;
}

.csv-column-info {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  padding: 0.75rem;
  background: rgba(13, 110, 253, 0.05);
  border-radius: 6px;
  border-left: 3px solid #0d6efd;
}

.csv-column-info i {
  color: #0d6efd;
  font-size: 1.1rem;
  margin-top: 0.25rem;
}

.file-upload-section {
  margin-bottom: 1.5rem;
}

.section-label {
  display: block;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #495057;
  font-size: 0.95rem;
}

.required-mark {
  color: #dc3545;
}

.file-drop-area {
  position: relative;
  border: 2px dashed #cbd5e0;
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  background: #f8f9fa;
  transition: all 0.3s ease;
  cursor: pointer;
}

.file-drop-area:hover {
  border-color: #667eea;
  background: #f0f4ff;
}

.file-drop-area.has-file {
  border-style: solid;
  border-color: #28a745;
  background: #f0fff4;
  padding: 1.25rem;
}

.file-input-hidden {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.drop-placeholder {
  pointer-events: none;
}

.drop-placeholder i {
  font-size: 3rem;
  color: #667eea;
  margin-bottom: 1rem;
}

.drop-text {
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.25rem;
  font-size: 1rem;
}

.drop-hint {
  color: #6c757d;
  font-size: 0.85rem;
  margin: 0;
}

.file-selected {
  display: flex;
  align-items: center;
  gap: 1rem;
  pointer-events: none;
}

.file-icon {
  background: #28a745;
  color: white;
  width: 50px;
  height: 50px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.file-icon i {
  font-size: 2rem;
  line-height: 1;
  color: white;
}

.file-info {
  flex: 1;
  text-align: left;
}

.file-name {
  font-weight: 600;
  color: #212529;
  font-size: 0.95rem;
  margin-bottom: 0.25rem;
}

.file-size {
  color: #6c757d;
  font-size: 0.85rem;
}

.btn-remove-file {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: none;
  background: #dc3545;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  pointer-events: all;
  flex-shrink: 0;
}

.btn-remove-file:hover {
  background: #c82333;
  transform: scale(1.1);
}

.progress-section {
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 12px;
  padding: 1.25rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.progress-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
  color: #495057;
}

.results-section {
  margin-bottom: 1.5rem;
}

.success-card {
  background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  color: white;
  box-shadow: 0 4px 12px rgba(17, 153, 142, 0.3);
  margin-bottom: 1rem;
}

.result-icon {
  width: 60px;
  height: 60px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  flex-shrink: 0;
}

.result-content {
  flex: 1;
}

.result-title {
  font-size: 1.15rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.result-subtitle {
  font-size: 1rem;
  opacity: 0.95;
}

.error-card {
  background: white;
  border: 1px solid #f5c2c7;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(220, 53, 69, 0.15);
}

.error-header {
  background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
  color: white;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1rem;
}

.error-header i {
  font-size: 1.3rem;
}

.error-body {
  padding: 1.25rem;
}

.error-list {
  max-height: 250px;
  overflow-y: auto;
}

.error-list::-webkit-scrollbar {
  width: 6px;
}

.error-list::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.error-list::-webkit-scrollbar-thumb {
  background: #dc3545;
  border-radius: 10px;
}

.error-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.65rem;
  margin-bottom: 0.5rem;
  background: #fff5f5;
  border-left: 3px solid #dc3545;
  border-radius: 6px;
  font-size: 0.9rem;
  color: #721c24;
}

.error-item:last-of-type {
  margin-bottom: 0;
}

.error-item i {
  color: #dc3545;
  margin-top: 2px;
  flex-shrink: 0;
}

.error-download-section {
  text-align: center;
}

.error-summary {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  background: #fff5f5;
  border: 1px solid #f5c2c7;
  border-radius: 8px;
  color: #721c24;
  font-size: 0.95rem;
}

.modal-footer-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding-top: 1rem;
  border-top: 1px solid #e9ecef;
}
</style>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useSimsStore } from '@/stores/sims'
import { useAuthStore } from '@/stores/auth'
import Button from '@/components/Button.vue'
import Table from '@/components/Table.vue'
import Modal from '@/components/Modal.vue'
import InputField from '@/components/InputField.vue'
import Pagination from '@/components/Pagination.vue'
import api from '@/services/api'
import { showSuccess, showError } from '@/services/toast'

const simsStore = useSimsStore()
const authStore = useAuthStore()

// Role-based access
const userRole = computed(() => authStore.user?.role || 'user')
const isAdmin = computed(() => userRole.value === 'admin')
const isOrganization = computed(() => userRole.value === 'organization')
const isManager = computed(() => userRole.value === 'manager')

// Search and filters
const searchQuery = ref('')
const organizationFilter = ref('')
const departmentFilter = ref('')
const perPage = ref(10)
const searchTimeout = ref(null)

// Modal states
const showSimModal = ref(false)
const showDeleteModal = ref(false)
const showBulkDeleteModal = ref(false)
const showImportModal = ref(false)
const isEditing = ref(false)
const simToDelete = ref(null)

// Bulk selection
const selectedSims = ref([])

// Import state
const selectedFile = ref(null)
const fileInput = ref(null)
const importProgress = reactive({
  uploading: false,
  percent: 0
})
const importResults = ref(null)

// Form data
const simForm = reactive({
  mobile: '',
  name: '',
  organization_id: '',
  department_id: ''
})

// Organizations and departments data
const organizations = ref([])
const departments = ref([])
const availableDepartments = ref([])

const errors = ref({})
const errorMessage = ref('')

// Helper function to get error message
const getErrorMessage = (error) => {
  if (Array.isArray(error)) {
    return error[0]
  }
  return error
}

const simHeaders = computed(() => {
  const headers = [
    { key: 'mobile', label: 'Mobile Number', class: 'text-start' },
    { key: 'name', label: 'Name', class: 'text-start' },
  ]
  
  // Show organization column only for admin
  if (isAdmin.value) {
    headers.push({ key: 'organization_name', label: 'Organization', class: 'text-start' })
  }
  
  headers.push(
    { key: 'department_name', label: 'Department', class: 'text-start' },
    { key: 'created_at', label: 'Created At', class: 'text-start' }
  )
  
  return headers
})

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const fetchSims = async () => {
  await simsStore.fetchSims(
    simsStore.pagination.current_page,
    searchQuery.value,
    organizationFilter.value,
    departmentFilter.value,
    perPage.value
  )
}

const refreshSims = () => {
  searchQuery.value = ''
  organizationFilter.value = ''
  departmentFilter.value = ''
  fetchSims()
}

const handleSearch = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  
  searchTimeout.value = setTimeout(() => {
    fetchSims()
  }, 500)
}

const handleOrganizationFilter = async () => {
  departmentFilter.value = ''
  
  if (organizationFilter.value) {
    await fetchDepartmentsByOrganization(organizationFilter.value)
  } else {
    departments.value = []
  }
  
  fetchSims()
}

const handleDepartmentFilter = () => {
  fetchSims()
}

const handlePerPageChange = () => {
  fetchSims()
}

const handlePageChange = (page) => {
  simsStore.fetchSims(page, searchQuery.value, organizationFilter.value, departmentFilter.value, perPage.value)
}

const fetchDepartmentsByOrganization = async (organizationId) => {
  try {
    const response = await api.get(`/sims/departments/by-organization?organization_id=${organizationId}`)
    departments.value = response.data
  } catch (error) {
    console.error('Error fetching departments:', error)
    departments.value = []
  }
}

const openCreateModal = async () => {
  isEditing.value = false
  resetForm()
  
  // For non-admin users, fetch departments after organization is auto-set
  if ((isOrganization.value || isManager.value) && organizations.value.length > 0) {
    simForm.organization_id = organizations.value[0].id
    await handleOrganizationChange()
  }
  
  showSimModal.value = true
}

const openEditModal = async (sim) => {
  isEditing.value = true
  simForm.mobile = sim.mobile
  simForm.name = sim.name
  simForm.organization_id = sim.organization_id
  simForm.department_id = sim.department_id || ''
  simToDelete.value = sim
  
  // Fetch departments for the sim's organization
  if (simForm.organization_id) {
    try {
      const response = await api.get(`/sims/departments/by-organization?organization_id=${simForm.organization_id}`)
      availableDepartments.value = response.data
    } catch (error) {
      console.error('Error fetching departments:', error)
      availableDepartments.value = []
    }
  }
  
  showSimModal.value = true
}

const openDeleteModal = (sim) => {
  simToDelete.value = sim
  showDeleteModal.value = true
}

const closeSimModal = () => {
  showSimModal.value = false
  resetForm()
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  simToDelete.value = null
}

// Import CSV functions
const openImportModal = () => {
  showImportModal.value = true
  selectedFile.value = null
  importResults.value = null
  importProgress.uploading = false
  importProgress.percent = 0
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const closeImportModal = () => {
  const shouldRefresh = importResults.value && importResults.value.imported > 0
  
  showImportModal.value = false
  selectedFile.value = null
  importResults.value = null
  importProgress.uploading = false
  importProgress.percent = 0
  
  if (fileInput.value) {
    fileInput.value.value = ''
  }
  
  // Refresh the SIMs list if any were imported
  if (shouldRefresh) {
    fetchSims()
  }
}

const clearFile = () => {
  selectedFile.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const resetImport = () => {
  selectedFile.value = null
  importResults.value = null
  importProgress.uploading = false
  importProgress.percent = 0
  
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (!file.name.endsWith('.csv')) {
      showError('Please select a CSV file')
      event.target.value = ''
      return
    }
    selectedFile.value = file
    importResults.value = null
  }
}

const handleImportCsv = async () => {
  if (!selectedFile.value) {
    showError('Please select a CSV file')
    return
  }

  const formData = new FormData()
  formData.append('file', selectedFile.value)

  try {
    importProgress.uploading = true
    importProgress.percent = 0

    const progressInterval = setInterval(() => {
      if (importProgress.percent < 90) {
        importProgress.percent += 10
      }
    }, 100)

    const response = await api.post('/sims/import-csv', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    clearInterval(progressInterval)
    importProgress.percent = 100
    importProgress.uploading = false

    importResults.value = response.data

    if (response.data.imported > 0) {
      showSuccess(response.data.message)
    } else if (response.data.errors && response.data.errors.length > 0) {
      showError('Import completed with errors. Please check the error list below.')
    }

  } catch (error) {
    importProgress.uploading = false
    console.error('Error importing CSV:', error)
    
    if (error.response?.data?.message) {
      showError(error.response.data.message)
    } else {
      showError('Error importing CSV file')
    }
    
    if (error.response?.data?.required_columns) {
      importResults.value = {
        imported: 0,
        total_rows: 0,
        errors: [`Required columns: ${error.response.data.required_columns.join(', ')}`]
      }
    }
  }
}

// Export CSV function
const handleExportCsv = async () => {
  try {
    // Build params for the current filters - use very large per_page to get all records
    const params = {
      search: searchQuery.value || '',
      organization_id: organizationFilter.value || '',
      department_id: departmentFilter.value || '',
      per_page: 999999 // Get all records matching filters, not just current page
    }

    // Fetch all SIMs based on current filters
    const response = await api.get('/sims', { params })
    const simsData = response.data.data || response.data

    if (!simsData || simsData.length === 0) {
      showError('No data to export')
      return
    }

    // Prepare CSV content
    let csvContent = ''
    
    // CSV headers based on role
    if (isAdmin.value) {
      csvContent = 'Mobile Number,Name,Organization,Department,Created At\n'
      
      simsData.forEach(sim => {
        const mobile = sim.mobile || ''
        const name = (sim.name || '').replace(/"/g, '""') // Escape quotes
        const organization = (sim.organization_name || '').replace(/"/g, '""')
        const department = (sim.department_name || '').replace(/"/g, '""')
        const createdAt = sim.created_at ? new Date(sim.created_at).toLocaleDateString('en-US') : ''
        
        csvContent += `"${mobile}","${name}","${organization}","${department}","${createdAt}"\n`
      })
    } else {
      csvContent = 'Mobile Number,Name,Department,Created At\n'
      
      simsData.forEach(sim => {
        const mobile = sim.mobile || ''
        const name = (sim.name || '').replace(/"/g, '""') // Escape quotes
        const department = (sim.department_name || '').replace(/"/g, '""')
        const createdAt = sim.created_at ? new Date(sim.created_at).toLocaleDateString('en-US') : ''
        
        csvContent += `"${mobile}","${name}","${department}","${createdAt}"\n`
      })
    }

    // Create and download the file
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    
    const timestamp = new Date().toISOString().slice(0, 10)
    link.setAttribute('href', url)
    link.setAttribute('download', `sims_export_${timestamp}.csv`)
    link.style.visibility = 'hidden'
    
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    
    showSuccess(`Exported ${simsData.length} SIM(s) successfully`)
  } catch (error) {
    console.error('Error exporting SIMs:', error)
    showError('Failed to export SIM cards')
  }
}

const downloadSampleCsv = () => {
  let csvContent = ''
  
  if (isAdmin.value) {
    csvContent = 'organization,department,mobile,name\n'
    csvContent += 'Dosti Enterprise,Sales,9876543210,Sales SIM 1\n'
    csvContent += 'Dosti Enterprise,Marketing,9876543211,Marketing SIM 1\n'
  } else {
    csvContent = 'department,mobile,name\n'
    csvContent += 'Sales,9876543210,Sales SIM 1\n'
    csvContent += 'Marketing,9876543211,Marketing SIM 1\n'
  }

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  const url = URL.createObjectURL(blob)
  
  link.setAttribute('href', url)
  link.setAttribute('download', 'sim_import_sample.csv')
  link.style.visibility = 'hidden'
  
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const downloadFailedRecords = () => {
  if (!importResults.value || !importResults.value.errors || importResults.value.errors.length === 0) {
    return
  }

  // Create CSV content with failed records
  let csvContent = 'Error\n'
  
  importResults.value.errors.forEach(error => {
    // Escape quotes and wrap in quotes
    const escapedError = error.replace(/"/g, '""')
    csvContent += `"${escapedError}"\n`
  })

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  const url = URL.createObjectURL(blob)
  
  const timestamp = new Date().toISOString().slice(0, 10)
  link.setAttribute('href', url)
  link.setAttribute('download', `sim_import_failed_records_${timestamp}.csv`)
  link.style.visibility = 'hidden'
  
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  
  showSuccess('Failed records CSV downloaded successfully')
}

const resetForm = () => {
  simForm.mobile = ''
  simForm.name = ''
  simForm.organization_id = ''
  simForm.department_id = ''
  availableDepartments.value = []
  errors.value = {}
  errorMessage.value = ''
}

const handleOrganizationChange = async () => {
  simForm.department_id = ''
  availableDepartments.value = []
  
  if (simForm.organization_id) {
    try {
      const response = await api.get(`/sims/departments/by-organization?organization_id=${simForm.organization_id}`)
      availableDepartments.value = response.data
    } catch (error) {
      console.error('Error fetching departments:', error)
      availableDepartments.value = []
    }
  }
}

const handleSubmit = async () => {
  errors.value = {}
  errorMessage.value = ''

  try {
    if (isEditing.value) {
      await simsStore.updateSim(simToDelete.value.id, simForm)
      showSuccess('SIM updated successfully')
    } else {
      await simsStore.createSim(simForm)
      showSuccess('SIM created successfully')
    }
    
    closeSimModal()
    await fetchSims()
  } catch (error) {
    console.error('Error submitting form:', error)
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'An error occurred'
    }
  }
}

const handleDelete = async () => {
  try {
    await simsStore.deleteSim(simToDelete.value.id)
    showSuccess('SIM deleted successfully')
    closeDeleteModal()
    await fetchSims()
  } catch (error) {
    console.error('Error deleting sim:', error)
    showError('Failed to delete SIM')
  }
}

const handleSelectionChange = (selected) => {
  selectedSims.value = selected
}

const openBulkDeleteModal = () => {
  if (selectedSims.value.length === 0) {
    showError('Please select at least one SIM to delete')
    return
  }
  showBulkDeleteModal.value = true
}

const closeBulkDeleteModal = () => {
  showBulkDeleteModal.value = false
}

const handleBulkDelete = async () => {
  try {
    const ids = selectedSims.value.map(sim => sim.id)
    await api.post('/sims/bulk-delete', { ids })
    showSuccess(`Successfully deleted ${selectedSims.value.length} SIM(s)`)
    selectedSims.value = []
    closeBulkDeleteModal()
    await fetchSims()
  } catch (error) {
    console.error('Error deleting SIMs:', error)
    showError('Failed to delete selected SIMs')
  }
}

const fetchOrganizations = async () => {
  // Only admin users need to fetch organizations list
  if (!isAdmin.value) {
    return
  }
  
  try {
    const response = await api.get('/organizations', { params: { per_page: 1000 } })
    organizations.value = response.data.data || []
  } catch (error) {
    console.error('Error fetching organizations:', error)
    organizations.value = []
  }
}

onMounted(() => {
  // Start fetching sims immediately (non-blocking)
  fetchSims()
  
  // For admin users, fetch organizations first
  if (isAdmin.value) {
    fetchOrganizations()
  } else if (isOrganization.value) {
    // For organization users, fetch departments directly using their organization
    fetchDepartmentsByOrganization()
  }
})
</script>
