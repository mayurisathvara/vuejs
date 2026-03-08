<template>
  <div class="page-container">
    <div class="page-header">
      <div class="header-content">
        <div class="header-title">
          <h3 class="page-title">Organization Settings</h3>
          <div class="text-muted" v-if="organizationName">{{ organizationName }}</div>
        </div>
      </div>
    </div>

    <div class="card card-round">
      <div class="card-body">
        <div v-if="errorMessage" class="alert alert-danger" role="alert">
          {{ errorMessage }}
        </div>

        <form @submit.prevent="handleSave">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Callback Window (Hours) <span class="text-danger">*</span></label>
              <select
                v-model.number="form.callback_window_hours"
                class="form-select"
                :class="{ 'is-invalid': errors.callback_window_hours }"
                required
              >
                <option v-for="h in callbackOptions" :key="h" :value="h">{{ h }}</option>
              </select>
              <div v-if="errors.callback_window_hours" class="invalid-feedback d-block">
                {{ getErrorMessage(errors.callback_window_hours) }}
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Date Format <span class="text-danger">*</span></label>
              <select
                v-model="form.date_formate"
                class="form-select"
                :class="{ 'is-invalid': errors.date_formate }"
                required
              >
                <option v-for="f in dateFormatOptions" :key="f" :value="f">{{ f }}</option>
              </select>
              <div v-if="errors.date_formate" class="invalid-feedback d-block">
                {{ getErrorMessage(errors.date_formate) }}
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label d-block">Enable Manager Role <span class="text-danger">*</span></label>
              <div class="form-check form-switch">
                <input
                  class="form-check-input switcher-lg"
                  type="checkbox"
                  role="switch"
                  id="enable_manager_role"
                  v-model="form.enable_manager_role"
                />
                <label class="form-check-label" for="enable_manager_role">{{ form.enable_manager_role ? 'On' : 'Off' }}</label>
              </div>
              <div v-if="errors.enable_manager_role" class="text-danger mt-1" style="font-size: 0.875rem;">
                {{ getErrorMessage(errors.enable_manager_role) }}
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label d-block">Enable Working Hours <span class="text-danger">*</span></label>
              <div class="form-check form-switch">
                <input
                  class="form-check-input switcher-lg"
                  type="checkbox"
                  role="switch"
                  id="enable_working_hours"
                  v-model="form.enable_working_hours"
                />
                <label class="form-check-label" for="enable_working_hours">{{ form.enable_working_hours ? 'On' : 'Off' }}</label>
              </div>
              <div v-if="errors.enable_working_hours" class="text-danger mt-1" style="font-size: 0.875rem;">
                {{ getErrorMessage(errors.enable_working_hours) }}
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Working Hours</label>
              <select
                v-model.number="form.working_hours"
                class="form-select"
                :class="{ 'is-invalid': errors.working_hours }"
              >
                <option value="">Select</option>
                <option v-for="h in workingHoursOptions" :key="h" :value="h">{{ h }}</option>
              </select>
              <div v-if="errors.working_hours" class="invalid-feedback d-block">
                {{ getErrorMessage(errors.working_hours) }}
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label d-block">Excluded Numbers <span class="text-danger">*</span></label>
              <div class="form-check form-switch">
                <input
                  class="form-check-input switcher-lg"
                  type="checkbox"
                  role="switch"
                  id="exclude_numbers_enabled"
                  v-model="excludeNumbersEnabled"
                />
                <label class="form-check-label" for="exclude_numbers_enabled">{{ excludeNumbersEnabled ? 'Enabled' : 'Disabled' }}</label>
              </div>
              <div v-if="errors.exclude_numbers_enabled" class="text-danger mt-1" style="font-size: 0.875rem;">
                {{ getErrorMessage(errors.exclude_numbers_enabled) }}
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              Save
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'
import { showSuccess, showError } from '@/services/toast'
import { decryptId } from '@/utils/encryption'

const route = useRoute()
const organizationId = computed(() => decryptId(route.params.organizationId))

const loading = ref(false)
const errorMessage = ref('')
const errors = ref({})

const organizationName = ref('')

const callbackOptions = ref([24, 48, 72, 96])
const dateFormatOptions = ref(['Y-m-d', 'd-m-Y', 'm-d-Y', 'd/m/Y', 'm/d/Y', 'Y/m/d'])
const workingHoursOptions = ref(Array.from({ length: 20 }, (_, i) => i + 1))

const form = reactive({
  callback_window_hours: 48,
  date_formate: 'Y-m-d',
  enable_manager_role: false,
  enable_working_hours: false,
  working_hours: '',
  exclude_numbers_enabled: 1,
})

// Two-way bridge: checkbox ↔ 1/0 integer
const excludeNumbersEnabled = computed({
  get: () => form.exclude_numbers_enabled === 1,
  set: (val) => { form.exclude_numbers_enabled = val ? 1 : 0 },
})

const getErrorMessage = (error) => {
  if (Array.isArray(error)) return error[0]
  return error
}

const coerceWorkingHours = (value) => {
  if (value === null || value === undefined || value === '') return ''
  const num = Number(value)
  return Number.isFinite(num) ? num : ''
}

const fetchOrganization = async () => {
  const res = await api.get(`/organizations/${organizationId.value}`)
  organizationName.value = res.data?.name || ''
}

const fetchSettings = async () => {
  const res = await api.get(`/organizations/${organizationId.value}/settings`)

  const settings = res.data?.settings || {}
  const options = res.data?.options || {}

  if (Array.isArray(options.callback_window_hours)) callbackOptions.value = options.callback_window_hours
  if (Array.isArray(options.date_formate)) dateFormatOptions.value = options.date_formate

  form.callback_window_hours = Number(settings.callback_window_hours ?? 48)
  form.date_formate = settings.date_formate ?? 'Y-m-d'
  form.enable_manager_role = Boolean(settings.enable_manager_role ?? false)
  form.enable_working_hours = Boolean(settings.enable_working_hours ?? false)
  form.working_hours = coerceWorkingHours(settings.working_hours)
  form.exclude_numbers_enabled = settings.exclude_numbers_enabled !== undefined ? Number(settings.exclude_numbers_enabled) : 1
}

const handleRefresh = async () => {
  errors.value = {}
  errorMessage.value = ''
  loading.value = true
  try {
    await Promise.all([fetchOrganization(), fetchSettings()])
  } catch (e) {
    console.error('Error loading organization settings:', e)
    errorMessage.value = 'Failed to load organization settings.'
  } finally {
    loading.value = false
  }
}

const handleSave = async () => {
  loading.value = true
  errorMessage.value = ''
  errors.value = {}

  try {
    await api.put(`/organizations/${organizationId.value}/settings`, {
      callback_window_hours:   form.callback_window_hours,
      date_formate:            form.date_formate,
      enable_manager_role:     form.enable_manager_role,
      enable_working_hours:    form.enable_working_hours,
      working_hours:           form.working_hours,
      exclude_numbers_enabled: form.exclude_numbers_enabled,
    })

    showSuccess('Settings saved successfully')
    await fetchSettings()
  } catch (e) {
    console.error('Error saving settings:', e)
    if (e.response?.data?.errors) {
      errors.value = e.response.data.errors
    } else {
      errorMessage.value = 'Failed to save settings.'
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  handleRefresh()
})
</script>

<style scoped>
.form-check-input.switcher-lg {
  transform: none;
  width: 3.75em;
  height: 1.75em;
  border-radius: 2em;
  background-size: 1.45em 1.45em;
}
</style>
