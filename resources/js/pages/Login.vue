<template>
  <form @submit.prevent="handleLogin">
    <div class="mb-3">
      <InputField
        v-model="form.email"
        type="email"
        label="Email Address"
        placeholder="Enter your email"
        :error="errors.email"
        required
      />
    </div>

    <div class="mb-3">
      <InputField
        v-model="form.password"
        type="password"
        label="Password"
        placeholder="Enter your password"
        :error="errors.password"
        required
      />
    </div>

    <div class="mb-3 form-check">
      <input
        v-model="form.remember"
        type="checkbox"
        class="form-check-input"
        id="remember"
      />
      <label class="form-check-label" for="remember">
        Remember me
      </label>
    </div>

    <div class="d-grid">
      <Button
        type="submit"
        variant="primary"
        size="lg"
        :loading="authStore.loading"
        label="Sign In"
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
  email: '',
  password: '',
  remember: false
})

const errors = ref({})
const errorMessage = ref('')

const handleLogin = async () => {
  errors.value = {}
  errorMessage.value = ''

  // Basic validation
  if (!form.email) {
    errors.value.email = 'Email is required'
    return
  }
  if (!form.password) {
    errors.value.password = 'Password is required'
    return
  }

  try {
    await authStore.login(form)
    router.push('/dashboard')
  } catch (error) {
    if (error.response?.status === 422) {
      // Validation errors
      errors.value = error.response.data.errors || {}
    } else {
      // General error
      errorMessage.value = error.response?.data?.message || 'Login failed. Please try again.'
    }
  }
}
</script>
