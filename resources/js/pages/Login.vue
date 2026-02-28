<template>
  <form class="login-form" @submit.prevent="handleLogin">
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
        :show-toggle="true"
        label="Password"
        placeholder="Enter your password"
        :error="errors.password"
        required
      />
    </div>

    <div class="mb-4 d-flex justify-content-between align-items-center login-meta">
      <div class="form-check mb-0">
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
      <a href="#" class="forgot-link" @click.prevent>Forgot password?</a>
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

    <div class="login-divider"></div>

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

<style scoped>
.login-meta .form-check-label,
.forgot-link {
  font-size: 1.05rem;
  color: #7b8391;
}

.forgot-link {
  text-decoration: none;
  font-weight: 500;
}

.forgot-link:hover {
  color: #ff8f1f;
}

.login-divider {
  margin-top: 1.55rem;
  border-top: 1px solid #e8e9ee;
}
</style>
