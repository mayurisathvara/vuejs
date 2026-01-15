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
          <div class="row g-4 align-items-start">
            <div class="col-lg-5">
              <div class="setting-field">
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
            </div>

            <div class="col-lg-7">
              <div class="help-card">
                <div class="help-card__text">
                  <p class="mb-2">
                    This setting controls how dates are displayed throughout the application, including dashboards, call logs, and reports.
                  </p>
                  <p class="mb-2">
                    Select a format that aligns with your regional or organizational preference. The chosen format will be applied consistently across the system.
                  </p>
                  <p class="mb-0">
                    Changing this option affects display only and does not alter stored date data.
                  </p>

                  <div class="help-card__subtitle">Example Formats:</div>
                  <ul class="mb-0">
                    <li><span class="font-monospace">Y-m-d</span> → 2026-01-02</li>
                    <li><span class="font-monospace">d-m-Y</span> → 02-01-2026</li>
                    <li><span class="font-monospace">m/d/Y</span> → 01/02/2026</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="submit" class="btn btn-primary" :disabled="loading || !organizationId">
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
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import { showSuccess } from '@/services/toast'

const authStore = useAuthStore()

const organizationId = computed(() => authStore.user?.organization?.id || authStore.user?.organization_id || null)
const organizationName = computed(() => authStore.user?.organization?.name || '')

const loading = ref(false)
const errorMessage = ref('')
const errors = ref({})

const dateFormatOptions = ref(['Y-m-d', 'd-m-Y', 'm-d-Y', 'd/m/Y', 'm/d/Y', 'Y/m/d'])

const form = reactive({
  date_formate: 'Y-m-d'
})

const settingsSnapshot = ref(null)

const getErrorMessage = (error) => {
  if (Array.isArray(error)) return error[0]
  return error
}

const fetchSettings = async () => {
  if (!organizationId.value) {
    errorMessage.value = 'Organization not found for this user.'
    return
  }

  const res = await api.get(`/organizations/${organizationId.value}/settings`)

  const settings = res.data?.settings || {}
  const options = res.data?.options || {}

  settingsSnapshot.value = settings

  if (Array.isArray(options.date_formate)) dateFormatOptions.value = options.date_formate

  form.date_formate = settings.date_formate ?? 'Y-m-d'
}

const handleSave = async () => {
  if (!organizationId.value) return

  loading.value = true
  errorMessage.value = ''
  errors.value = {}

  try {
    const s = settingsSnapshot.value || {}

    await api.put(`/organizations/${organizationId.value}/settings`, {
      callback_window_hours: Number(s.callback_window_hours ?? 48),
      date_formate: form.date_formate,
      enable_manager_role: Boolean(s.enable_manager_role ?? false),
      enable_working_hours: Boolean(s.enable_working_hours ?? false),
      working_hours: s.working_hours === '' ? null : s.working_hours
    })

    showSuccess('Settings saved successfully')
    await fetchSettings()
  } catch (e) {
    console.error('Error saving date format setting:', e)
    if (e.response?.data?.errors) {
      errors.value = e.response.data.errors
    } else {
      errorMessage.value = 'Failed to save settings.'
    }
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  loading.value = true
  try {
    await fetchSettings()
  } catch (e) {
    console.error('Error loading date format setting:', e)
    errorMessage.value = 'Failed to load settings.'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.setting-field {
  max-width: 420px;
}

.help-card {
  border: 1px solid rgba(var(--bs-body-color-rgb), 0.12);
  background: var(--bs-body-bg);
  border-radius: 12px;
  padding: 14px 14px;
}

.help-card__subtitle {
  font-weight: 700;
  margin-top: 12px;
  margin-bottom: 6px;
}

.help-card__text {
  color: rgba(var(--bs-body-color-rgb), 0.75);
  font-size: 0.95rem;
}
</style>
