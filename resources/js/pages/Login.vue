<template>
  <form class="login-form" @submit.prevent="handleLogin" novalidate>

    <!-- Server error alert -->
    <div v-if="errorMessage" class="lf-error-alert" role="alert">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
        <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
      </svg>
      <span>{{ errorMessage }}</span>
    </div>

    <!-- Email field -->
    <div class="lf-field">
      <InputField
        v-model="form.email"
        type="email"
        label="Email Address"
        placeholder="you@company.com"
        :error="errors.email"
        required
      />
    </div>

    <!-- Password field -->
    <div class="lf-field">
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

    <!-- Options row: remember me + forgot password -->
    <div class="lf-options">
      <label class="lf-remember">
        <input
          v-model="form.remember"
          type="checkbox"
          class="lf-checkbox"
          id="remember"
        />
        <span>Remember me</span>
      </label>
      <a href="#" class="lf-forgot" @click.prevent>Forgot password?</a>
    </div>

    <!-- Sign In button -->
    <div class="lf-submit">
      <Button
        type="submit"
        variant="primary"
        size="lg"
        :loading="authStore.loading"
        label="Sign In"
        block
      />
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
/* ── Field spacing ─────────────────────────────────────────────── */
.lf-field {
  margin-bottom: 20px;
}

/* ── Error alert ───────────────────────────────────────────────── */
.lf-error-alert {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fff1f0;
  border: 1px solid #fecaca;
  border-radius: 8px;
  padding: 0.65rem 0.9rem;
  color: #dc2626;
  font-size: 0.88rem;
  margin-bottom: 20px;
}

/* ── Options row ───────────────────────────────────────────────── */
.lf-options {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}

.lf-remember {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 0.875rem;
  color: #6b7280;
  user-select: none;
  line-height: 1;
}

.lf-checkbox {
  width: 15px;
  height: 15px;
  min-width: 15px;
  accent-color: #f97316;
  cursor: pointer;
  margin: 0;
  vertical-align: middle;
  position: relative;
  top: -1px;
}

.lf-forgot {
  font-size: 0.875rem;
  font-weight: 600;
  color: #f97316;
  text-decoration: none;
  transition: color 0.15s;
}

.lf-forgot:hover {
  color: #ea580c;
  text-decoration: underline;
}

/* ── Submit row ────────────────────────────────────────────────── */
.lf-submit {
  margin-bottom: 0;
}
</style>
