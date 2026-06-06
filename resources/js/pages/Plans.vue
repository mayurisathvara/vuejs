<template>
  <div class="plans-page">
    <div class="page-header">
      <div>
        <span class="eyebrow">Billing</span>
        <h3>Plan Management</h3>
      </div>
      <div class="header-actions">
        <button type="button" class="hdr-btn hdr-btn--ghost" :disabled="loading" @click="fetchPlans">
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i><span>Refresh</span>
        </button>
        <button type="button" class="hdr-btn hdr-btn--add" @click="openCreateModal">
          <i class="fas fa-plus"></i><span>Add Plan</span>
        </button>
      </div>
    </div>

    <div class="filters-card">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input v-model="filters.search" type="text" class="form-control" placeholder="Search plans..." @input="debouncedFetch" />
      </div>
      <select v-model="filters.billing_type" class="form-select" @change="fetchPlans">
        <option value="">All cycles</option>
        <option value="trial">Trial</option>
        <option value="monthly">Monthly</option>
        <option value="yearly">Yearly</option>
      </select>
      <select v-model="filters.is_active" class="form-select" @change="fetchPlans">
        <option value="">All statuses</option>
        <option value="1">Active</option>
        <option value="0">Inactive</option>
      </select>
    </div>

    <div v-if="error" class="alert alert-danger">
      <i class="fas fa-exclamation-circle me-2"></i>
      {{ error }}
    </div>

    <div class="plans-table-card">
      <div v-if="loading" class="table-state">
        <div class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></div>
        <span>Loading plans...</span>
      </div>

      <div v-else-if="!plans.length" class="table-state">
        No plans found.
      </div>

      <div v-else class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th>Plan</th>
              <th>Cycle</th>
              <th class="text-end">Price / SIM</th>
              <th class="text-end">Trial Days</th>
              <th>Features</th>
              <th>Status</th>
              <th class="text-end">Subscriptions</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="plan in plans" :key="plan.id">
              <td>
                <strong>{{ plan.display_name }}</strong>
                <span>{{ plan.name }}</span>
              </td>
              <td>{{ toTitleCase(plan.billing_type) }}</td>
              <td class="text-end">{{ formatCurrency(plan.price_per_sim) }}</td>
              <td class="text-end">{{ plan.trial_days || '-' }}</td>
              <td>
                <div class="feature-pills">
                  <span v-for="feature in plan.features || []" :key="`${plan.id}-${feature}`">{{ featureLabel(feature) }}</span>
                  <em v-if="!(plan.features || []).length">No features</em>
                </div>
              </td>
              <td>
                <span class="status-pill" :class="{ inactive: !plan.is_active }">
                  {{ plan.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="text-end">{{ Number(plan.subscriptions_count || 0).toLocaleString() }}</td>
              <td class="text-end">
                <div class="action-buttons">
                  <button type="button" title="Edit plan" @click="openEditModal(plan)">
                    <i class="fas fa-pen"></i>
                  </button>
                  <button type="button" title="Delete plan" :disabled="Number(plan.subscriptions_count || 0) > 0" @click="confirmDelete(plan)">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Modal :show="showModal" :title="isEditing ? 'Edit Plan' : 'Create Plan'" size="lg" @close="closeModal">
      <form @submit.prevent="savePlan">
        <div class="row">
          <div class="col-md-6 mb-3">
            <InputField v-model="form.display_name" label="Display Name" placeholder="Basic" :error="errors.display_name" required />
          </div>
          <div class="col-md-6 mb-3">
            <InputField v-model="form.name" label="Plan Slug" placeholder="basic" :error="errors.name" required />
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Billing Cycle <span class="text-danger">*</span></label>
            <select v-model="form.billing_type" class="form-select" :class="{ 'is-invalid': errors.billing_type }">
              <option value="trial">Trial</option>
              <option value="monthly">Monthly</option>
              <option value="yearly">Yearly</option>
            </select>
            <div v-if="errors.billing_type" class="invalid-feedback d-block">{{ getError(errors.billing_type) }}</div>
          </div>
          <div class="col-md-6 mb-3">
            <InputField v-model="form.price_per_sim" type="number" label="Price per SIM" placeholder="150" :error="errors.price_per_sim" required />
          </div>
          <div class="col-md-6 mb-3">
            <InputField v-model="form.trial_days" type="number" label="Trial Days" placeholder="14" :error="errors.trial_days" :disabled="form.billing_type !== 'trial'" />
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Status</label>
            <select v-model="form.is_active" class="form-select">
              <option :value="true">Active</option>
              <option :value="false">Inactive</option>
            </select>
          </div>
          <div class="col-12 mb-3">
            <label class="form-label">Features</label>
            <div class="feature-multiselect" :class="{ open: featureDropdownOpen, 'is-invalid': errors.features }">
              <button type="button" ref="featureTriggerRef" class="feature-multiselect__trigger" @click="toggleFeatureDropdown">
                <span v-if="form.features.length" class="feature-multiselect__pills">
                  <span v-for="f in form.features" :key="f" class="fms-pill">
                    {{ featureLabel(f) }}
                    <i class="fas fa-times" @click.stop="removeFeature(f)"></i>
                  </span>
                </span>
                <span v-else class="feature-multiselect__placeholder">Select features…</span>
                <i class="fas fa-chevron-down feature-multiselect__caret"></i>
              </button>
              <div v-if="featureDropdownOpen" class="feature-multiselect__dropdown"
                   :style="{ position: 'fixed', top: dropdownPos.top + 'px', left: dropdownPos.left + 'px', width: dropdownPos.width + 'px', zIndex: 9999 }">
                <label
                  v-for="opt in FEATURE_OPTIONS"
                  :key="opt.value"
                  class="fms-option"
                  :class="{ selected: form.features.includes(opt.value) }"
                  @click="toggleFeature(opt.value)"
                >
                  <span class="fms-checkbox">
                    <i v-if="form.features.includes(opt.value)" class="fas fa-check"></i>
                  </span>
                  <span class="fms-option-label">{{ opt.label }}</span>
                </label>
              </div>
            </div>
            <div v-if="errors.features" class="invalid-feedback d-block">{{ getError(errors.features) }}</div>
          </div>
        </div>
      </form>

      <template #footer>
        <Button variant="secondary" @click="closeModal">Cancel</Button>
        <Button variant="primary" :loading="saving" @click="savePlan">
          {{ isEditing ? 'Update Plan' : 'Create Plan' }}
        </Button>
      </template>
    </Modal>

    <Modal :show="showDeleteModal" title="Delete Plan" @close="closeDeleteModal">
      <p class="mb-3">Are you sure you want to delete this plan?</p>
      <div v-if="planToDelete" class="delete-card">
        <strong>{{ planToDelete.display_name }}</strong>
        <span>{{ toTitleCase(planToDelete.billing_type) }}</span>
      </div>
      <template #footer>
        <Button variant="secondary" @click="closeDeleteModal">Cancel</Button>
        <Button variant="danger" :loading="saving" @click="deletePlan">Delete Plan</Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, reactive, ref } from 'vue'
import api from '@/services/api'
import Button from '@/components/Button.vue'
import InputField from '@/components/InputField.vue'
import Modal from '@/components/Modal.vue'
import { showError, showSuccess } from '@/services/toast'

const FEATURE_OPTIONS = [
  { value: 'call_tracking', label: 'Call Tracking' },
  { value: 'reports',       label: 'Reports' },
  { value: 'ai',            label: 'AI Insights' },
  { value: 'api',           label: 'Developer API' },
]

const loading = ref(false)
const saving = ref(false)
const error = ref('')
const plans = ref([])
const searchTimer = ref(null)
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEditing = ref(false)
const selectedPlan = ref(null)
const planToDelete = ref(null)
const featureDropdownOpen = ref(false)
const featureTriggerRef = ref(null)
const dropdownPos = reactive({ top: 0, left: 0, width: 0 })
const errors = ref({})

const toggleFeatureDropdown = () => {
  if (!featureDropdownOpen.value && featureTriggerRef.value) {
    const rect = featureTriggerRef.value.getBoundingClientRect()
    dropdownPos.top = rect.bottom + 4
    dropdownPos.left = rect.left
    dropdownPos.width = rect.width
  }
  featureDropdownOpen.value = !featureDropdownOpen.value
}

const toggleFeature = (value) => {
  const idx = form.features.indexOf(value)
  if (idx === -1) form.features.push(value)
  else form.features.splice(idx, 1)
}

const removeFeature = (value) => {
  const idx = form.features.indexOf(value)
  if (idx !== -1) form.features.splice(idx, 1)
}

const onClickOutside = (e) => {
  if (!e.target.closest('.feature-multiselect')) featureDropdownOpen.value = false
}
onMounted(() => document.addEventListener('click', onClickOutside))
onUnmounted(() => document.removeEventListener('click', onClickOutside))

const filters = reactive({
  search: '',
  billing_type: '',
  is_active: ''
})

const form = reactive({
  name: '',
  display_name: '',
  billing_type: 'monthly',
  price_per_sim: 0,
  trial_days: null,
  features: [],
  is_active: true
})

const fetchPlans = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await api.get('/admin/plans', { params: filters })
    plans.value = response.data?.data || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Unable to load plans.'
  } finally {
    loading.value = false
  }
}

const debouncedFetch = () => {
  clearTimeout(searchTimer.value)
  searchTimer.value = setTimeout(fetchPlans, 350)
}

const resetForm = () => {
  form.name = ''
  form.display_name = ''
  form.billing_type = 'monthly'
  form.price_per_sim = 0
  form.trial_days = null
  form.features = []
  form.is_active = true
  featureDropdownOpen.value = false
  errors.value = {}
  selectedPlan.value = null
}

const openCreateModal = () => {
  isEditing.value = false
  resetForm()
  showModal.value = true
}

const openEditModal = (plan) => {
  isEditing.value = true
  selectedPlan.value = plan
  form.name = plan.name || ''
  form.display_name = plan.display_name || ''
  form.billing_type = plan.billing_type || 'monthly'
  form.price_per_sim = Number(plan.price_per_sim || 0)
  form.trial_days = plan.trial_days || null
  form.features = Array.isArray(plan.features) ? [...plan.features] : []
  form.is_active = Boolean(plan.is_active)
  featureDropdownOpen.value = false
  errors.value = {}
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const planPayload = () => ({
  ...form,
  price_per_sim: Number(form.price_per_sim || 0),
  trial_days: form.billing_type === 'trial' ? Number(form.trial_days || 0) : null,
  features: [...form.features],
})

const savePlan = async () => {
  saving.value = true
  errors.value = {}

  try {
    if (isEditing.value) {
      await api.put(`/admin/plans/${selectedPlan.value.id}`, planPayload())
      showSuccess('Plan updated successfully.')
    } else {
      await api.post('/admin/plans', planPayload())
      showSuccess('Plan created successfully.')
    }

    closeModal()
    await fetchPlans()
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      showError(err.response?.data?.message || 'Unable to save plan.')
    }
  } finally {
    saving.value = false
  }
}

const confirmDelete = (plan) => {
  planToDelete.value = plan
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  planToDelete.value = null
  showDeleteModal.value = false
}

const deletePlan = async () => {
  if (!planToDelete.value) return

  saving.value = true

  try {
    await api.delete(`/admin/plans/${planToDelete.value.id}`)
    showSuccess('Plan deleted successfully.')
    closeDeleteModal()
    await fetchPlans()
  } catch (err) {
    showError(err.response?.data?.message || 'Unable to delete plan.')
  } finally {
    saving.value = false
  }
}

const getError = (value) => Array.isArray(value) ? value[0] : value

const toTitleCase = (value) => String(value || '')
  .replace(/[_-]/g, ' ')
  .replace(/\w\S*/g, (word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())

const featureLabel = (value) => toTitleCase(value)

const formatCurrency = (value) => new Intl.NumberFormat('en-IN', {
  style: 'currency',
  currency: 'INR',
  maximumFractionDigits: Number(value || 0) % 1 === 0 ? 0 : 2
}).format(Number(value || 0))

onMounted(fetchPlans)
</script>

<style scoped>
.plans-page {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.page-header,
.filters-card,
.plans-table-card {
  border: 1px solid #eef2f7;
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 18px;
}

.eyebrow {
  color: #7c8aa0;
  font-size: 0.72rem;
  font-weight: 900;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.page-header h3 {
  margin: 4px 0 0;
  color: #101828;
  font-weight: 900;
}

.header-actions,
.filters-card {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
}

.filters-card {
  padding: 14px;
}

.search-box {
  position: relative;
  flex: 1 1 320px;
}

.search-box i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}

.search-box input {
  padding-left: 38px;
}

.filters-card select {
  flex: 0 1 180px;
}

.plans-table-card {
  overflow: hidden;
}

.table-state {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 22px;
  color: #667085;
  font-weight: 800;
}

.table {
  min-width: 1040px;
}

.hdr-btn { display: inline-flex; align-items: center; gap: 7px; padding: 0 14px; height: 36px; border-radius: 10px; font-size: 0.82rem; font-weight: 700; border: none; cursor: pointer; transition: background 0.15s, box-shadow 0.15s, border-color 0.15s; white-space: nowrap; line-height: 1; }
.hdr-btn:disabled { opacity: 0.55; cursor: not-allowed; }
.hdr-btn i { font-size: 0.8rem; }
.hdr-btn--ghost { background: #fff; color: #374151; border: 1.5px solid #e5e7eb; }
.hdr-btn--ghost:hover:not(:disabled) { background: #f9fafb; border-color: #d1d5db; }
.hdr-btn--add { background: linear-gradient(135deg, #f97316, #ffb454); color: #fff; box-shadow: 0 3px 12px rgba(249,115,22,0.28); }
.hdr-btn--add:hover { box-shadow: 0 5px 16px rgba(249,115,22,0.38); }

.table th {
  background: #f9fafb;
  color: #374151;
  border-bottom: 1px solid #edf1f7;
  font-size: 12px;
  font-weight: 900;
  text-transform: uppercase;
  padding: 10px 14px;
}

.table td {
  border-top: 1px solid #f1f5f9;
  padding: 9px 14px;
}

.table td strong,
.table td span {
  display: block;
}

.table td strong {
  color: #111827;
}

.table td span {
  color: #6b7280;
  font-size: 12px;
}

.feature-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  max-width: 340px;
}

.feature-pills span {
  display: inline-flex !important;
  padding: 4px 8px;
  border-radius: 999px;
  color: #175cd3 !important;
  background: #eff8ff;
  font-weight: 800;
}

.feature-pills em {
  color: #98a2b3;
  font-size: 12px;
}

.status-pill {
  display: inline-flex;
  padding: 5px 10px;
  border-radius: 999px;
  color: #067647;
  background: #dcfae6;
  font-weight: 900;
  font-size: 12px;
}

.status-pill.inactive {
  color: #475467;
  background: #f2f4f7;
}

.action-buttons {
  display: inline-flex;
  gap: 8px;
}

.action-buttons button {
  width: 34px;
  height: 34px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  color: #3559b8;
  background: #fff;
}

.action-buttons button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.delete-card {
  padding: 14px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  background: #f8fafc;
}

.delete-card strong,
.delete-card span {
  display: block;
}

/* ── Feature multi-select ── */
.feature-multiselect {
  position: relative;
}

.feature-multiselect__trigger {
  display: flex;
  align-items: center;
  width: 100%;
  min-height: 38px;
  padding: 5px 10px;
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  cursor: pointer;
  gap: 6px;
  text-align: left;
  transition: border-color 0.15s, box-shadow 0.15s;
}

.feature-multiselect.open .feature-multiselect__trigger,
.feature-multiselect__trigger:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
  outline: none;
}

.feature-multiselect.is-invalid .feature-multiselect__trigger {
  border-color: #dc3545;
}

.feature-multiselect__pills {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  flex: 1;
}

.fms-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 2px 8px;
  background: #eff8ff;
  color: #175cd3;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
}

.fms-pill i {
  font-size: 10px;
  cursor: pointer;
  opacity: 0.6;
  transition: opacity 0.1s;
}

.fms-pill i:hover {
  opacity: 1;
}

.feature-multiselect__placeholder {
  flex: 1;
  color: #6c757d;
  font-size: 0.9rem;
}

.feature-multiselect__caret {
  margin-left: auto;
  color: #9ca3af;
  font-size: 11px;
  transition: transform 0.2s;
}

.feature-multiselect.open .feature-multiselect__caret {
  transform: rotate(180deg);
}

.feature-multiselect__dropdown {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.1);
  overflow: hidden;
}

.fms-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  cursor: pointer;
  transition: background 0.12s;
  user-select: none;
}

.fms-option:hover {
  background: #f8fafc;
}

.fms-option.selected {
  background: #eff8ff;
}

.fms-checkbox {
  width: 18px;
  height: 18px;
  border: 1.5px solid #d1d5db;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
  transition: background 0.12s, border-color 0.12s;
  flex-shrink: 0;
}

.fms-option.selected .fms-checkbox {
  background: #175cd3;
  border-color: #175cd3;
}

.fms-checkbox i {
  color: #fff;
  font-size: 10px;
}

.fms-option-label {
  font-size: 0.875rem;
  color: #374151;
  font-weight: 500;
}

.fms-option.selected .fms-option-label {
  color: #175cd3;
  font-weight: 700;
}

@media (max-width: 767.98px) {
  .page-header {
    align-items: stretch;
    flex-direction: column;
  }
}
</style>
