<template>
  <form @submit.prevent="handleRegister">
    <div class="row">
      <div class="col-md-6 mb-3">
        <InputField
          v-model="form.name"
          type="text"
          label="Full Name"
          placeholder="Enter your full name"
          :error="errors.name"
          required
        />
      </div>
      <div class="col-md-6 mb-3">
        <InputField
          v-model="form.email"
          type="email"
          label="Email Address"
          placeholder="Enter your email"
          :error="errors.email"
          required
        />
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <InputField
          v-model="form.password"
          type="password"
          label="Password"
          placeholder="Enter your password"
          :error="errors.password"
          required
        />
      </div>
      <div class="col-md-6 mb-3">
        <InputField
          v-model="form.password_confirmation"
          type="password"
          label="Confirm Password"
          placeholder="Confirm your password"
          :error="errors.password_confirmation"
          required
        />
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Role</label>
      <select
        v-model="form.role"
        class="form-select"
        :class="{ 'is-invalid': errors.role }"
      >
        <option value="">Select a role</option>
        <option value="admin">Admin</option>
        <option value="user">User</option>
        <option value="manager">Manager</option>
      </select>
      <div v-if="errors.role" class="invalid-feedback d-block">
        {{ errors.role }}
      </div>
    </div>

    <div class="mb-3">
      <InputField
        v-model="form.organization_id"
        type="text"
        label="Organization ID"
        placeholder="Enter organization ID"
        :error="errors.organization_id"
        help="Optional: Enter your organization ID"
      />
    </div>

    <div class="mb-3 form-check">
      <input
        v-model="form.terms"
        type="checkbox"
        class="form-check-input"
        id="terms"
        required
      />
      <label class="form-check-label" for="terms">
        I agree to the <a href="#" class="text-decoration-none">Terms and Conditions</a>
      </label>
    </div>

    <div class="d-grid">
      <Button
        type="submit"
        variant="primary"
        size="lg"
        :loading="authStore.loading"
        label="Create Account"
        block
      />
    </div>

    <div v-if="errorMessage" class="alert alert-danger mt-3" role="alert">
      {{ errorMessage }}
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
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: '',
  organization_id: '',
  terms: false
})

const errors = ref({})
const errorMessage = ref('')

const handleRegister = async () => {
  errors.value = {}
  errorMessage.value = ''

  // Basic validation
  if (!form.name) {
    errors.value.name = 'Name is required'
    return
  }
  if (!form.email) {
    errors.value.email = 'Email is required'
    return
  }
  if (!form.password) {
    errors.value.password = 'Password is required'
    return
  }
  if (form.password !== form.password_confirmation) {
    errors.value.password_confirmation = 'Passwords do not match'
    return
  }
  if (!form.role) {
    errors.value.role = 'Role is required'
    return
  }
  if (!form.terms) {
    errorMessage.value = 'You must agree to the terms and conditions'
    return
  }

  try {
    await authStore.register(form)
    router.push('/dashboard')
  } catch (error) {
    if (error.response?.status === 422) {
      // Validation errors
      errors.value = error.response.data.errors || {}
    } else {
      // General error
      errorMessage.value = error.response?.data?.message || 'Registration failed. Please try again.'
    }
  }
}
</script>
