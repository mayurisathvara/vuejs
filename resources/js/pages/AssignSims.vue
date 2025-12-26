<template>
  <div class="page-container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-title">
          <div class="d-flex align-items-center">
            <Button variant="link" class="p-0 me-3" @click="goBack">
              <i class="fas fa-arrow-left"></i>
            </Button>
            <div>
              <h3 class="page-title">Assign SIMs</h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else-if="userData" class="row">
      <div class="col-lg-8">
        <!-- User Info Card -->
        <div class="card card-round mb-4">
          <div class="card-body">
            <h5 class="card-title mb-3">User Information</h5>
            <div class="row">
              <div class="col-md-6 mb-2">
                <label class="text-muted small">Name</label>
                <div class="fw-bold">{{ userData.name }}</div>
              </div>
              <div class="col-md-6 mb-2">
                <label class="text-muted small">Email</label>
                <div>{{ userData.email }}</div>
              </div>
              <div class="col-md-6 mb-2">
                <label class="text-muted small">Organization</label>
                <div>{{ userData.organization?.name || 'N/A' }}</div>
              </div>
              <div class="col-md-6 mb-2">
                <label class="text-muted small">Primary Department</label>
                <div>{{ userData.department?.name || 'N/A' }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Assignment Form -->
        <div class="card card-round">
          <div class="card-body">
            <h5 class="card-title mb-4">SIM Assignment</h5>
            <form @submit.prevent="handleSubmit">
              <!-- Allowed Departments -->
              <div class="mb-4">
                <label class="form-label">
                  Allowed Departments
                  <span class="text-danger">*</span>
                </label>
                <div class="multiselect-header">
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    @click="selectAllDepartments"
                  >
                    <i class="fas fa-check-double me-1"></i>
                    Select All
                  </button>
                  <button
                    v-if="form.allowed_department_ids.length > 0"
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    @click="clearAllDepartments"
                  >
                    <i class="fas fa-times me-1"></i>
                    Clear All
                  </button>
                  <span class="text-muted ms-auto">
                    {{ form.allowed_department_ids.length }} of {{ departments.length }} selected
                  </span>
                </div>
                <div class="department-checkboxes">
                  <div v-for="dept in departments" :key="dept.id" class="form-check mb-2">
                    <input
                      :id="`dept-${dept.id}`"
                      v-model="form.allowed_department_ids"
                      class="form-check-input"
                      type="checkbox"
                      :value="dept.id"
                      :disabled="dept.id === userData?.department_id"
                      @change="handleDepartmentChange"
                    />
                    <label class="form-check-label" :for="`dept-${dept.id}`">
                      <i class="fas fa-sitemap me-2 text-primary"></i>
                      {{ dept.name }}
                      <span v-if="dept.id === userData?.department_id" class="badge bg-info ms-2">Primary</span>
                    </label>
                  </div>
                </div>
                <div v-if="errors.allowed_department_ids" class="text-danger small mt-1">
                  {{ getErrorMessage(errors.allowed_department_ids) }}
                </div>
              </div>

              <!-- SIMs Multi-Select -->
              <div v-if="canAssignSims" class="mb-4">
                <label class="form-label">
                  Assign SIMs
                  <span class="text-danger">*</span>
                </label>
                
                <!-- Loading SIMs -->
                <div v-if="loadingSims" class="text-center py-3">
                  <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading SIMs...</span>
                  </div>
                  <span class="ms-2">Loading SIMs...</span>
                </div>

                <!-- No departments selected -->
                <div v-else-if="form.allowed_department_ids.length === 0" class="alert alert-info">
                  <i class="fas fa-info-circle me-2"></i>
                  Please select at least one department to view available SIMs
                </div>

                <!-- No SIMs available -->
                <div v-else-if="availableSims.length === 0" class="alert alert-warning">
                  <i class="fas fa-exclamation-triangle me-2"></i>
                  No SIMs available in the selected departments
                </div>

                <!-- SIMs List -->
                <div v-else>
                  <div class="multiselect-header">
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-primary"
                      @click="selectAllSims"
                    >
                      <i class="fas fa-check-double me-1"></i>
                      Select All
                    </button>
                    <button
                      v-if="form.sim_ids.length > 0"
                      type="button"
                      class="btn btn-sm btn-outline-secondary"
                      @click="clearAllSims"
                    >
                      <i class="fas fa-times me-1"></i>
                      Clear All
                    </button>
                    <span class="text-muted ms-auto">
                      {{ form.sim_ids.length }} of {{ availableSims.length }} selected
                    </span>
                  </div>
                  <div class="sims-selection-box">
                    <div v-for="sim in availableSims" :key="sim.id" class="sim-item">
                      <input
                        :id="`sim-${sim.id}`"
                        v-model="form.sim_ids"
                        class="form-check-input"
                        type="checkbox"
                        :value="sim.id"
                      />
                      <label :for="`sim-${sim.id}`" class="sim-label">
                        <div class="sim-details">
                          <i class="fas fa-mobile-alt text-primary me-2"></i>
                          <div class="sim-text">
                            <strong class="sim-mobile">{{ sim.mobile }}</strong>
                            <span v-if="sim.name" class="sim-name">{{ sim.name }}</span>
                          </div>
                        </div>
                        <span class="badge bg-info">{{ getDepartmentName(sim.department_id) }}</span>
                      </label>
                    </div>
                  </div>
                </div>
                <div v-if="errors.sim_ids" class="text-danger small mt-1">
                  {{ getErrorMessage(errors.sim_ids) }}
                </div>
              </div>

              <!-- Error Message -->
              <div v-if="errorMessage" class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ errorMessage }}
              </div>

              <!-- Success Message -->
              <div v-if="successMessage" class="alert alert-success" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ successMessage }}
              </div>

              <!-- Action Buttons -->
              <div class="d-flex gap-2">
                <Button variant="secondary" @click="goBack">
                  <i class="fas fa-times me-2"></i>
                  Cancel
                </Button>
                <Button
                  variant="primary"
                  type="submit"
                  :loading="submitting"
                  :disabled="!canSubmit"
                >
                  <i class="fas fa-save me-2"></i>
                  Assign SIMs
                </Button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Summary Sidebar -->
      <div class="col-lg-4">
        <div class="card card-round sticky-top" style="top: 20px;">
          <div class="card-body">
            <h5 class="card-title mb-3">
              <i class="fas fa-info-circle me-2 text-info"></i>
              Assignment Summary
            </h5>
            
            <div class="summary-item mb-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">Selected Departments</span>
                <span class="badge bg-primary">{{ form.allowed_department_ids.length }}</span>
              </div>
              <div v-if="form.allowed_department_ids.length > 0" class="small">
                <div v-for="deptId in form.allowed_department_ids" :key="deptId" class="badge bg-light text-dark me-1 mb-1">
                  {{ getDepartmentName(deptId) }}
                </div>
              </div>
            </div>

            <div v-if="canAssignSims" class="summary-item mb-3">
              <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted">Selected SIMs</span>
                <span class="badge bg-success">{{ form.sim_ids.length }}</span>
              </div>
              <div v-if="form.sim_ids.length > 0" class="small mt-2">
                <div v-for="simId in form.sim_ids.slice(0, 5)" :key="simId" class="d-flex align-items-center mb-2">
                  <i class="fas fa-mobile-alt me-2 text-primary" style="font-size: 0.75rem;"></i>
                  <span>{{ getSimMobile(simId) }}  -  {{ getSimName(simId) }}</span>
                </div>
                <div v-if="form.sim_ids.length > 5" class="text-muted">
                  + {{ form.sim_ids.length - 5 }} more
                </div>
              </div>
            </div>

            <hr />

            <div v-if="canAssignSims" class="alert alert-info small mb-0">
              <strong>Note:</strong> Assigning new SIMs will replace any existing SIM assignments for this user.
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else class="alert alert-danger">
      <i class="fas fa-exclamation-triangle me-2"></i>
      Failed to load user data. Please try again.
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import Button from '@/components/Button.vue'
import { showSuccess, showError } from '@/services/toast'

const route = useRoute()
const router = useRouter()

const userId = ref(route.params.userId)
const loading = ref(true)
const loadingSims = ref(false)
const submitting = ref(false)
const userData = ref(null)
const departments = ref([])
const availableSims = ref([])
const errors = ref({})
const errorMessage = ref('')
const successMessage = ref('')

const form = ref({
  allowed_department_ids: [],
  sim_ids: [],
})

const canAssignSims = computed(() => {
  return userData.value?.role !== 'manager'
})

const canSubmit = computed(() => {
  if (submitting.value) return false
  if (form.value.allowed_department_ids.length === 0) return false
  if (!canAssignSims.value) return true
  return form.value.sim_ids.length > 0
})

const loadUserData = async () => {
  try {
    loading.value = true
    const response = await api.get(`/users/${userId.value}/assign-sims`)
    userData.value = response.data.user
    
    // Parse allowed_department_ids from JSON string if needed
    let allowedDeptIds = []
    if (userData.value.allowed_department_ids) {
      if (typeof userData.value.allowed_department_ids === 'string') {
        try {
          allowedDeptIds = JSON.parse(userData.value.allowed_department_ids)
        } catch (e) {
          console.error('Error parsing allowed_department_ids:', e)
        }
      } else if (Array.isArray(userData.value.allowed_department_ids)) {
        allowedDeptIds = userData.value.allowed_department_ids
      }
    }
    
    // Ensure user's own department is always included
    if (userData.value.department_id && !allowedDeptIds.includes(userData.value.department_id)) {
      allowedDeptIds.push(userData.value.department_id)
    }
    
    form.value.allowed_department_ids = allowedDeptIds
    form.value.sim_ids = canAssignSims.value ? (response.data.assigned_sim_ids || []) : []

    // Sort departments: selected ones first
    departments.value = response.data.departments.sort((a, b) => {
      const aSelected = allowedDeptIds.includes(a.id)
      const bSelected = allowedDeptIds.includes(b.id)
      if (aSelected && !bSelected) return -1
      if (!aSelected && bSelected) return 1
      return a.name.localeCompare(b.name)
    })

    if (canAssignSims.value && form.value.allowed_department_ids.length > 0) {
      await loadAvailableSims()
    } else {
      availableSims.value = []
    }
  } catch (error) {
    console.error('Failed to load user data:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to load user data'
    showError('Failed to load user data')
  } finally {
    loading.value = false
  }
}

const loadAvailableSims = async () => {
  if (form.value.allowed_department_ids.length === 0) {
    availableSims.value = []
    return
  }

  try {
    loadingSims.value = true
    const response = await api.post('/users/sims/by-departments', {
      user_id: userId.value,
      department_ids: form.value.allowed_department_ids,
    })
    
    // Sort SIMs: selected ones first
    availableSims.value = response.data.sort((a, b) => {
      const aSelected = form.value.sim_ids.includes(a.id)
      const bSelected = form.value.sim_ids.includes(b.id)
      if (aSelected && !bSelected) return -1
      if (!aSelected && bSelected) return 1
      return a.mobile.localeCompare(b.mobile)
    })
  } catch (error) {
    console.error('Failed to load SIMs:', error)
    showError('Failed to load SIMs')
  } finally {
    loadingSims.value = false
  }
}

const handleDepartmentChange = async () => {
  if (canAssignSims.value) {
    form.value.sim_ids = form.value.sim_ids.filter(simId => {
      const sim = availableSims.value.find(s => s.id === simId)
      return sim && form.value.allowed_department_ids.includes(sim.department_id)
    })

    await loadAvailableSims()
  } else {
    form.value.sim_ids = []
    availableSims.value = []
  }
  
  if (errors.value.allowed_department_ids) {
    delete errors.value.allowed_department_ids
  }
}

const handleSubmit = async () => {
  errors.value = {}
  errorMessage.value = ''
  successMessage.value = ''

  if (form.value.allowed_department_ids.length === 0) {
    errors.value.allowed_department_ids = ['At least one department must be selected']
    return
  }

  if (canAssignSims.value && form.value.sim_ids.length === 0) {
    errors.value.sim_ids = ['At least one SIM must be selected']
    return
  }

  try {
    submitting.value = true
    const payload = {
      allowed_department_ids: form.value.allowed_department_ids,
    }
    if (canAssignSims.value) {
      payload.sim_ids = form.value.sim_ids
    }

    const response = await api.post(`/users/${userId.value}/assign-sims`, payload)

    successMessage.value = response.data.message || 'SIMs assigned successfully'
    showSuccess('SIMs assigned successfully')
    
    setTimeout(() => {
      goBack()
    }, 1500)
  } catch (error) {
    console.error('Failed to assign SIMs:', error)
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }
    
    errorMessage.value = error.response?.data?.message || 'Failed to assign SIMs'
    showError(errorMessage.value)
  } finally {
    submitting.value = false
  }
}

const getDepartmentName = (deptId) => {
  const dept = departments.value.find(d => d.id === deptId)
  return dept?.name || 'Unknown'
}

const getSimMobile = (simId) => {
  const sim = availableSims.value.find(s => s.id === simId)
  return sim?.mobile || 'Unknown'
}

const getSimName = (simId) => {
  const sim = availableSims.value.find(s => s.id === simId)
  return sim?.name || 'N/A'
}

const selectAllDepartments = () => {
  form.value.allowed_department_ids = departments.value.map(dept => dept.id)
  handleDepartmentChange()
}

const clearAllDepartments = () => {
  // Keep user's own department always selected
  if (userData.value?.department_id) {
    form.value.allowed_department_ids = [userData.value.department_id]
  } else {
    form.value.allowed_department_ids = []
  }
  form.value.sim_ids = []
  handleDepartmentChange()
}

const selectAllSims = () => {
  form.value.sim_ids = availableSims.value.map(sim => sim.id)
}

const clearAllSims = () => {
  form.value.sim_ids = []
}

const getErrorMessage = (error) => {
  if (Array.isArray(error)) {
    return error[0]
  }
  return error
}

const goBack = () => {
  router.push({ name: 'Users' })
}

onMounted(() => {
  loadUserData()
})
</script>

<style scoped>
.page-container {
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 30px;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 5px;
}

.page-subtitle {
  color: #6c757d;
  font-size: 0.95rem;
  margin-bottom: 0;
}

.card-round {
  border-radius: 12px;
  border: 1px solid #e9ecef;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
}

.card-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1a1a1a;
}

.multiselect-header {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 15px;
  background: #f8f9fc;
  border: 1px solid #e3e6f0;
  border-radius: 8px 8px 0 0;
  border-bottom: none;
}

.multiselect-header .btn-sm {
  padding: 4px 12px;
  font-size: 12px;
  font-weight: 500;
}

.department-checkboxes {
  max-height: 280px;
  overflow-y: auto;
  padding: 15px;
  background: #fff;
  border: 1px solid #e3e6f0;
  border-radius: 0 0 8px 8px;
}

.sims-selection-box {
  max-height: 380px;
  overflow-y: auto;
  padding: 15px;
  background: #fff;
  border: 1px solid #e3e6f0;
  border-radius: 0 0 8px 8px;
  border-top: none;
}

.form-check {
  padding: 12px;
  border-radius: 6px;
  transition: all 0.2s;
  background: #f8f9fc;
  border: 1px solid transparent;
}

.form-check:hover {
  background-color: #e7f1ff;
  border-color: #b6d4fe;
}

.form-check-input {
  width: 18px;
  height: 18px;
  cursor: pointer;
  border: 2px solid #c1c1c1;
}

.form-check-input:checked {
  background-color: #4e73df;
  border-color: #4e73df;
}

.form-check-input:checked ~ .form-check-label {
  color: #2c3e50;
  font-weight: 600;
}

.form-check-label {
  cursor: pointer;
  font-size: 14px;
  color: #5a5c69;
}

/* SIM Item Styles - Simple and Clean */
.sim-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 10px 12px;
  margin-bottom: 8px;
  background: #fff;
  border: 1px solid #e3e6f0;
  border-radius: 6px;
  transition: all 0.2s;
}

.sim-item:hover {
  background: #f8f9fc;
  border-color: #4e73df;
}

.sim-item:has(input:checked) {
  background: #f8f9fc;
  border-color: #4e73df;
}

.sim-item input[type="checkbox"] {
  width: 18px;
  height: 18px;
  margin-top: 2px;
  cursor: pointer;
  flex-shrink: 0;
}

.sim-label {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  margin: 0;
  gap: 10px;
}

.sim-details {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  flex: 1;
  min-width: 0;
}

.sim-details i {
  font-size: 16px;
  margin-top: 2px;
  flex-shrink: 0;
}

.sim-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
  min-width: 0;
}

.sim-mobile {
  font-size: 14px;
  font-weight: 600;
  color: #2c3e50;
  display: block;
}

.sim-name {
  font-size: 13px;
  color: #6c757d;
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.sim-label .badge {
  padding: 4px 10px;
  font-size: 11px;
  font-weight: 500;
  border-radius: 4px;
  flex-shrink: 0;
  white-space: nowrap;
}

.summary-item {
  padding-bottom: 15px;
  border-bottom: 1px solid #e9ecef;
}

.summary-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.sticky-top {
  position: sticky;
}

.badge {
  font-weight: 500;
  padding: 0.35em 0.65em;
}

.alert {
  border-radius: 8px;
}

.department-checkboxes::-webkit-scrollbar,
.sims-selection-box::-webkit-scrollbar {
  width: 8px;
}

.department-checkboxes::-webkit-scrollbar-track,
.sims-selection-box::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.department-checkboxes::-webkit-scrollbar-thumb,
.sims-selection-box::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 10px;
}

.department-checkboxes::-webkit-scrollbar-thumb:hover,
.sims-selection-box::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>