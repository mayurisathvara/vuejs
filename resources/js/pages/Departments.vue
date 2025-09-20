<template>
  <div>
    <!-- Page Header -->
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
      <div>
        <h3 class="fw-bold mb-3">Departments Management</h3>
      </div>
      <div class="ms-md-auto py-2 py-md-0">
        <Button variant="outline-info" class="me-2" @click="refreshDepartments">
          <i class="fas fa-sync-alt me-1"></i>
          Refresh
        </Button>
        <Button variant="primary" @click="openCreateModal">
          <i class="fas fa-plus me-1"></i>
          Add Department
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
            placeholder="Search departments by name..."
            @input="handleSearch"
          />
        </div>
      </div>
      <div class="col-md-3">
        <select v-model="selectedOrganization" class="form-select" @change="handleOrganizationFilter">
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

    <!-- Departments Table -->
    <div class="card card-round">
      <div class="card-body p-0">
        <Table
          :data="departments"
          :headers="departmentHeaders"
          :loading="loading"
          :actions="{ edit: true, delete: true }"
          @edit="openEditModal"
          @delete="openDeleteModal"
        >
          <template #cell-name="{ row }">
            <div class="d-flex align-items-center">
              <div class="avatar-sm me-2">
                <div class="avatar-img rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="width: 32px; height: 32px;">
                  {{ row.name?.charAt(0) || 'D' }}
                </div>
              </div>
              <div>
                <div class="fw-bold">{{ row.name }}</div>
                <small class="text-muted">{{ row.organization?.name || 'N/A' }}</small>
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

    <!-- Create/Edit Department Modal -->
    <Modal
      :show="showDepartmentModal"
      :title="isEditing ? 'Edit Department' : 'Create Department'"
      size="lg"
      @close="closeDepartmentModal"
    >
      <form @submit.prevent="handleSubmit">
        <div class="row">
          <div class="col-md-6 mb-3">
            <InputField
              v-model="departmentForm.name"
              type="text"
              label="Department Name"
              placeholder="Enter department name"
              :error="errors.name"
              required
            />
          </div>
          <div class="col-md-6 mb-3">
            <div class="form-group">
              <label class="form-label">Organization</label>
              <select v-model="departmentForm.organization_id" class="form-select" :class="{ 'is-invalid': errors.organization_id }">
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
        <Button variant="secondary" @click="closeDepartmentModal">Cancel</Button>
        <Button
          variant="primary"
          :loading="loading"
          @click="handleSubmit"
        >
          {{ isEditing ? 'Update Department' : 'Create Department' }}
        </Button>
      </template>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="showDeleteModal"
      title="Delete Department"
      @close="closeDeleteModal"
      @confirm="handleDelete"
    >
      <p>Are you sure you want to delete this department?</p>
      <div v-if="departmentToDelete" class="alert alert-warning">
        <strong>{{ departmentToDelete.name }}</strong><br>
        <small>{{ departmentToDelete.organization?.name || 'N/A' }}</small>
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

/* Badge styling */
.badge {
  font-size: 0.75rem;
  font-weight: 500;
  padding: 0.375rem 0.75rem;
  border-radius: 0.375rem;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.badge.bg-info {
  background-color: #17a2b8 !important;
}
</style>

<script setup>
import { ref, reactive, onMounted, onUnmounted, watch, nextTick } from 'vue'
import Button from '@/components/Button.vue'
import Table from '@/components/Table.vue'
import Modal from '@/components/Modal.vue'
import InputField from '@/components/InputField.vue'
import Pagination from '@/components/Pagination.vue'
import api from '@/services/api'

// Data
const departments = ref([])
const organizations = ref([])
const loading = ref(false)
const searchQuery = ref('')
const selectedOrganization = ref('')
const perPage = ref(10)
const searchTimeout = ref(null)

// Modal states
const showDepartmentModal = ref(false)
const showDeleteModal = ref(false)
const isEditing = ref(false)
const departmentToDelete = ref(null)

// Form data
const departmentForm = reactive({
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

const departmentHeaders = [
  { key: 'name', label: 'Department', class: 'text-start' },
  { key: 'organization', label: 'Organization', class: 'text-center' },
  { key: 'created_at', label: 'Created', class: 'text-center' }
]

// Methods
const fetchDepartments = async (page = 1) => {
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

    const response = await api.get(`/departments?${params}`)
    departments.value = response.data.data
    pagination.current_page = response.data.current_page
    pagination.last_page = response.data.last_page
    pagination.per_page = response.data.per_page
    pagination.total = response.data.total
  } catch (error) {
    console.error('Error fetching departments:', error)
  } finally {
    loading.value = false
  }
}

const fetchOrganizations = async () => {
  try {
    const response = await api.get('/departments/organizations')
    organizations.value = response.data
  } catch (error) {
    console.error('Error fetching organizations:', error)
  }
}

const refreshDepartments = () => {
  fetchDepartments()
}

const handleSearch = () => {
  // Clear existing timeout
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  
  // Set new timeout for debounced search
  searchTimeout.value = setTimeout(() => {
    fetchDepartments()
  }, 500) // 500ms delay
}

const handleOrganizationFilter = () => {
  fetchDepartments()
}

const handlePerPageChange = () => {
  pagination.per_page = perPage.value
  fetchDepartments()
}

const handlePageChange = (page) => {
  fetchDepartments(page)
}

const openCreateModal = () => {
  isEditing.value = false
  resetForm()
  showDepartmentModal.value = true
}

const openEditModal = (department) => {
  isEditing.value = true
  departmentForm.name = department.name
  departmentForm.organization_id = department.organization_id
  departmentToDelete.value = department
  showDepartmentModal.value = true
}

const openDeleteModal = (department) => {
  departmentToDelete.value = department
  showDeleteModal.value = true
}

const closeDepartmentModal = () => {
  showDepartmentModal.value = false
  resetForm()
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  departmentToDelete.value = null
}

const resetForm = () => {
  departmentForm.name = ''
  departmentForm.organization_id = ''
  errors.value = {}
  errorMessage.value = ''
}

const handleSubmit = async () => {
  errors.value = {}
  errorMessage.value = ''

  // Collect all validation errors
  const validationErrors = {}
  
  if (!departmentForm.name) {
    validationErrors.name = 'Department name is required'
  } else if (departmentForm.name.length < 2) {
    validationErrors.name = 'Department name must be at least 2 characters'
  }
  
  if (!departmentForm.organization_id) {
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
      await api.put(`/departments/${departmentToDelete.value.id}`, departmentForm)
    } else {
      await api.post('/departments', departmentForm)
    }
    closeDepartmentModal()
    fetchDepartments()
  } catch (error) {
    console.error('Error saving department:', error)
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = 'Error saving department. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

const handleDelete = async () => {
  try {
    loading.value = true
    await api.delete(`/departments/${departmentToDelete.value.id}`)
    closeDeleteModal()
    fetchDepartments()
  } catch (error) {
    console.error('Error deleting department:', error)
  } finally {
    loading.value = false
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

// Watch for changes in perPage
watch(perPage, () => {
  fetchDepartments()
})

onMounted(() => {
  fetchDepartments()
  fetchOrganizations()
})

onUnmounted(() => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
})
</script>
