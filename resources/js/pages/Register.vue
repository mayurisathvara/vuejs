<template>
  <form class="register-form" @submit.prevent="handleRegister" novalidate>

    <!-- Server / general error -->
    <div v-if="errorMessage" class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="flex-shrink-0">
        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
        <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
      </svg>
      <span>{{ errorMessage }}</span>
    </div>

    <!-- 2-column fields grid -->
    <div class="fields-grid">

      <!-- Row 1: Organization Name | Email Address -->
      <div class="field-cell">
        <InputField
          v-model="form.name"
          type="text"
          label="Organization Name"
          placeholder="e.g. Acme Corp"
          :error="errors.name"
          required
        />
      </div>
      <div class="field-cell">
        <InputField
          v-model="form.email"
          type="email"
          label="Email Address"
          placeholder="admin@yourcompany.com"
          :error="errors.email"
          required
        />
      </div>

      <!-- Row 2: Mobile Number | Select Industry -->
      <div class="field-cell">
        <label class="form-label" for="mobile">Mobile Number <span class="text-danger">*</span></label>
        <div class="mobile-input-wrap" :class="{ 'has-error': errors.mobile }">
          <div class="mobile-prefix" aria-label="Country code">+91</div>
          <input
            id="mobile"
            v-model="form.mobile"
            type="tel"
            class="form-control mobile-input"
            placeholder="Enter 10-digit mobile number"
            maxlength="10"
            inputmode="numeric"
            autocomplete="tel"
            required
          />
        </div>
        <div v-if="errors.mobile" class="field-error">{{ errors.mobile }}</div>
      </div>
      <div class="field-cell">
        <label class="form-label" for="industry">Select Industry <span class="text-danger">*</span></label>
        <div class="select-wrap" :class="{ 'has-error': errors.industry }">
          <select
            v-model="form.industry"
            id="industry"
            class="form-select industry-select"
          >
            <option value="" disabled>Choose your industry</option>
            <option v-for="opt in industries" :key="opt" :value="opt">{{ opt }}</option>
          </select>
          <i class="fas fa-chevron-down select-arrow"></i>
        </div>
        <div v-if="errors.industry" class="field-error">{{ errors.industry }}</div>
      </div>

      <!-- Row 3: Password | Confirm Password -->
      <div class="field-cell">
        <InputField
          v-model="form.password"
          type="password"
          :show-toggle="true"
          label="Password"
          placeholder="Min. 6 characters"
          :error="errors.password"
          required
        />
      </div>
      <div class="field-cell">
        <InputField
          v-model="form.password_confirmation"
          type="password"
          :show-toggle="true"
          label="Confirm Password"
          placeholder="Re-enter password"
          :error="errors.password_confirmation"
          required
        />
      </div>

    </div>

    <!-- Terms -->
    <div class="terms-row">
      <div class="form-check">
        <input
          v-model="form.terms"
          type="checkbox"
          class="form-check-input"
          :class="{ 'is-invalid': errors.terms }"
          id="terms"
        />
        <label class="form-check-label" for="terms">
          I agree to the <a href="#" class="terms-link" @click.prevent>Terms &amp; Conditions</a>
        </label>
        <div v-if="errors.terms" class="invalid-feedback d-block">{{ errors.terms }}</div>
      </div>
    </div>

    <!-- Submit -->
    <div class="submit-row">
      <Button
        type="submit"
        variant="primary"
        size="lg"
        :loading="authStore.loading"
        label="Create Account"
        block
      />
    </div>

    <!-- Trial info (below button) -->
    <div class="trial-info">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16">
        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
      </svg>
      <span><strong>14-day free trial</strong> &middot; No credit card required &middot; SIM limit: 10</span>
    </div>

  </form>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import InputField from '@/components/InputField.vue'
import Button from '@/components/Button.vue'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  name:                  '',
  email:                 '',
  mobile:                '',
  industry:              '',
  password:              '',
  password_confirmation: '',
  terms:                 false,
})

const errors       = ref({})
const errorMessage = ref('')

const handleRegister = async () => {
  errors.value       = {}
  errorMessage.value = ''

  // Client-side validation
  if (!form.name.trim()) {
    errors.value.name = 'Organization name is required'
    return
  }
  if (!form.email.trim()) {
    errors.value.email = 'Email address is required'
    return
  }
  if (!form.mobile.trim()) {
    errors.value.mobile = 'Mobile number is required'
    return
  }
  if (!/^\d{10}$/.test(form.mobile.trim())) {
    errors.value.mobile = 'Mobile number must be exactly 10 digits'
    return
  }
  if (!form.industry) {
    errors.value.industry = 'Please select your industry'
    return
  }
  if (!form.password) {
    errors.value.password = 'Password is required'
    return
  }
  if (form.password.length < 6) {
    errors.value.password = 'Password must be at least 6 characters'
    return
  }
  if (form.password !== form.password_confirmation) {
    errors.value.password_confirmation = 'Passwords do not match'
    return
  }
  if (!form.terms) {
    errors.value.terms = 'You must agree to the terms and conditions'
    return
  }

  try {
    await authStore.register({
      name:                  form.name,
      email:                 form.email,
      mobile:                form.mobile,
      industry:              form.industry,
      password:              form.password,
      password_confirmation: form.password_confirmation,
    })
    router.push('/dashboard')
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
      // Flatten array error messages
      Object.keys(errors.value).forEach(key => {
        if (Array.isArray(errors.value[key])) {
          errors.value[key] = errors.value[key][0]
        }
      })
    } else {
      errorMessage.value = error.response?.data?.message || 'Registration failed. Please try again.'
    }
  }
}
const industries = [
  'Real Estate',
  'E-commerce',
  'Healthcare & Clinics',
  'Education / EdTech',
  'Marketing Agency',
  'Finance & Banking',
  'Insurance',
  'Automotive & Car Dealers',
  'Hospitality & Hotels',
  'Travel & Tourism',
  'Logistics & Transport',
  'Retail & Showrooms',
  'Legal Services',
  'Home Services & Repairs',
  'Recruitment & Staffing',
  'IT & Software',
  'Manufacturing',
  'Media & Advertising',
  'Pharmacy & Diagnostics',
  'Construction & Infrastructure',
  'Other',
]
</script>

<style scoped>
/* ── 2-column grid layout ──────────────────────────────────────── */
.fields-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px 16px;
  margin-bottom: 20px;
}

.field-cell {
  min-width: 0;
}

.mobile-input-wrap {
  display: flex;
  align-items: stretch;
  height: 48px;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  background: #fff;
  overflow: hidden;
  transition: border-color 0.18s, box-shadow 0.18s;
}

.mobile-input-wrap:focus-within {
  border-color: #f97316;
  box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
}

.mobile-input-wrap.has-error {
  border-color: #ef4444;
}

.mobile-prefix {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 72px;
  padding: 0 0.9rem;
  color: #111827;
  background: #f8fafc;
  border-right: 1.5px solid #e5e7eb;
  font-size: 0.95rem;
  font-weight: 700;
}
.mobile-input {
  height: 100%;
  border: 0 !important;
  border-radius: 0 !important;
  box-shadow: none !important;
  padding: 0 0.85rem;
}

.mobile-input:focus {
  border: 0 !important;
  box-shadow: none !important;
}

/* Terms row */
.terms-row {
  margin-bottom: 20px;
}

/* Submit row */
.submit-row {
  margin-bottom: 12px;
}

/* Trial info (below button) */
.trial-info {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-size: 0.82rem;
  color: #9ca3af;
  text-align: center;
}

.trial-info svg {
  color: #d1d5db;
  flex-shrink: 0;
}

.trial-info strong {
  color: #6b7280;
  font-weight: 600;
}

/* ── Industry select ───────────────────────────────────────────── */
.select-wrap {
  position: relative;
}

.industry-select {
  width: 100%;
  height: 48px;
  padding: 0 2.5rem 0 0.85rem;
  appearance: none;
  -webkit-appearance: none;
  background: #fff;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  color: #111827;
  font-size: 0.97rem;
  cursor: pointer;
  transition: border-color 0.18s, box-shadow 0.18s;
}

.industry-select:focus {
  outline: none;
  border-color: #f97316;
  box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
}

.industry-select option[value=""] {
  color: #9ca3af;
}

.select-wrap.has-error .industry-select {
  border-color: #ef4444;
}

.select-arrow {
  position: absolute;
  right: 0.9rem;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  font-size: 0.72rem;
  pointer-events: none;
}

.field-error {
  color: #dc3545;
  font-size: 0.82rem;
  margin-top: 0.28rem;
}

.terms-link {
  color: #ff8f1f;
  font-weight: 600;
  text-decoration: none;
}

.terms-link:hover {
  color: #ff7f00;
  text-decoration: underline;
}

/* ── Responsive: single column on mobile ───────────────────────── */
@media (max-width: 640px) {
  .fields-grid {
    grid-template-columns: 1fr;
    gap: 16px 0;
  }
}
</style>
