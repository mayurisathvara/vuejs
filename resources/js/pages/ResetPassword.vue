<template>
  <div class="rp-page">
    <div class="rp-card">

      <!-- Logo -->
      <div class="rp-logo">
        <img :src="'/logo/login_icon.png'" alt="Callytics" class="rp-logo-img" />
        <span class="rp-logo-name">Callytics</span>
      </div>

      <!-- Success state -->
      <template v-if="success">
        <div class="rp-icon-wrap rp-icon-wrap--success">
          <i class="fas fa-check-circle rp-icon"></i>
        </div>
        <h1 class="rp-title">Password Reset!</h1>
        <p class="rp-sub">Your password has been updated successfully. You can now sign in with your new password.</p>
        <router-link to="/login" class="rp-btn-primary">
          <i class="fas fa-sign-in-alt"></i>
          Sign In Now
        </router-link>
      </template>

      <!-- Invalid / missing token state -->
      <template v-else-if="!token || !email">
        <div class="rp-icon-wrap rp-icon-wrap--warn">
          <i class="fas fa-exclamation-triangle rp-icon"></i>
        </div>
        <h1 class="rp-title">Invalid Reset Link</h1>
        <p class="rp-sub">This password reset link is missing required information. Please request a new one.</p>
        <router-link to="/forgot-password" class="rp-btn-primary">
          <i class="fas fa-redo"></i>
          Request New Link
        </router-link>
      </template>

      <!-- Form state -->
      <template v-else>
        <div class="rp-icon-wrap">
          <i class="fas fa-lock rp-icon"></i>
        </div>
        <h1 class="rp-title">Reset Password</h1>
        <p class="rp-sub">Enter your new password below. Make it strong!</p>

        <!-- Error alert -->
        <div v-if="errorMessage" class="rp-alert rp-alert--error" role="alert">
          <i class="fas fa-exclamation-circle"></i>
          <span>{{ errorMessage }}</span>
        </div>

        <form @submit.prevent="handleSubmit" novalidate>

          <!-- New password -->
          <div class="rp-field">
            <label class="rp-label" for="rp-password">New Password</label>
            <div class="rp-input-wrap">
              <input
                id="rp-password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                class="rp-input"
                :class="{ 'rp-input--error': errors.password }"
                placeholder="Min. 8 characters"
                autocomplete="new-password"
                required
              />
              <button type="button" class="rp-eye" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Hide password' : 'Show password'">
                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
            <span v-if="errors.password" class="rp-field-error">{{ errors.password }}</span>
          </div>

          <!-- Confirm password -->
          <div class="rp-field">
            <label class="rp-label" for="rp-confirm">Confirm New Password</label>
            <div class="rp-input-wrap">
              <input
                id="rp-confirm"
                v-model="passwordConfirmation"
                :type="showConfirm ? 'text' : 'password'"
                class="rp-input"
                :class="{ 'rp-input--error': errors.password_confirmation }"
                placeholder="Re-enter new password"
                autocomplete="new-password"
                required
              />
              <button type="button" class="rp-eye" @click="showConfirm = !showConfirm" :aria-label="showConfirm ? 'Hide password' : 'Show password'">
                <i :class="showConfirm ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
            <span v-if="errors.password_confirmation" class="rp-field-error">{{ errors.password_confirmation }}</span>
          </div>

          <!-- Password strength hint -->
          <p class="rp-hint">
            <i class="fas fa-shield-alt"></i>
            Must be 8+ characters with uppercase, lowercase, number, and symbol.
          </p>

          <button type="submit" class="rp-btn-primary" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <i v-else class="fas fa-lock"></i>
            <span>{{ loading ? 'Resetting…' : 'Reset Password' }}</span>
          </button>
        </form>

        <div class="rp-footer">
          <router-link to="/login" class="rp-back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Sign In
          </router-link>
        </div>
      </template>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'

const route = useRoute()

const token               = ref(route.query.token || '')
const email               = ref(route.query.email || '')
const password            = ref('')
const passwordConfirmation = ref('')
const showPassword        = ref(false)
const showConfirm         = ref(false)
const loading             = ref(false)
const success             = ref(false)
const errorMessage        = ref('')
const errors              = ref({})

const handleSubmit = async () => {
  errorMessage.value = ''
  errors.value       = {}

  if (!password.value) {
    errors.value.password = 'New password is required.'
    return
  }
  if (password.value !== passwordConfirmation.value) {
    errors.value.password_confirmation = 'Passwords do not match.'
    return
  }

  loading.value = true

  try {
    await api.post('/reset-password', {
      token:                 token.value,
      email:                 email.value,
      password:              password.value,
      password_confirmation: passwordConfirmation.value,
    })
    success.value = true
  } catch (err) {
    if (err.response?.status === 422) {
      const serverErrors = err.response.data.errors || {}
      errors.value = Object.fromEntries(
        Object.entries(serverErrors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      )
    }
    errorMessage.value = err.response?.data?.message || 'Unable to reset password. Please request a new link.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.rp-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(145deg, #0f172a 0%, #1e1b4b 40%, #312e81 75%, #1e1b4b 100%);
  padding: 24px 16px;
}

.rp-card {
  background: #fff;
  border-radius: 24px;
  padding: 40px 40px 32px;
  width: 100%;
  max-width: 440px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

/* Logo */
.rp-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 28px;
  justify-content: center;
}

.rp-logo-img {
  width: 36px;
  height: 36px;
  border-radius: 9px;
  object-fit: cover;
}

.rp-logo-name {
  font-size: 1.2rem;
  font-weight: 900;
  color: #10233f;
  letter-spacing: -0.02em;
}

/* Icon */
.rp-icon-wrap {
  width: 68px;
  height: 68px;
  background: linear-gradient(135deg, #fff7ed, #ffedd5);
  border: 2px solid #fed7aa;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
}

.rp-icon-wrap--success {
  background: linear-gradient(135deg, #f0fdf4, #dcfce7);
  border-color: #bbf7d0;
}

.rp-icon-wrap--warn {
  background: linear-gradient(135deg, #fff7ed, #fef3c7);
  border-color: #fcd34d;
}

.rp-icon {
  font-size: 1.6rem;
  color: #f97316;
}

.rp-icon-wrap--success .rp-icon { color: #16a34a; }
.rp-icon-wrap--warn .rp-icon    { color: #d97706; }

/* Title & sub */
.rp-title {
  font-size: 1.55rem;
  font-weight: 800;
  color: #10233f;
  text-align: center;
  margin: 0 0 10px;
  letter-spacing: -0.02em;
}

.rp-sub {
  font-size: 0.95rem;
  color: #6b7280;
  text-align: center;
  line-height: 1.65;
  margin: 0 0 24px;
}

/* Alert */
.rp-alert {
  display: flex;
  align-items: center;
  gap: 10px;
  border-radius: 10px;
  padding: 12px 16px;
  font-size: 0.88rem;
  font-weight: 600;
  margin-bottom: 18px;
}

.rp-alert--error {
  background: #fff1f2;
  border: 1.5px solid #fecdd3;
  color: #be123c;
}

/* Form */
.rp-field {
  margin-bottom: 18px;
}

.rp-label {
  display: block;
  font-size: 0.83rem;
  font-weight: 700;
  color: #374151;
  margin-bottom: 6px;
  letter-spacing: 0.01em;
}

.rp-input-wrap {
  position: relative;
}

.rp-input {
  width: 100%;
  height: 48px;
  padding: 0 44px 0 14px;
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.97rem;
  color: #111827;
  background: #fff;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.18s, box-shadow 0.18s;
}

.rp-input:focus {
  border-color: #f97316;
  box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
}

.rp-input--error { border-color: #ef4444; }

.rp-eye {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: transparent;
  border: 0;
  color: #9ca3af;
  cursor: pointer;
  padding: 4px;
  font-size: 0.9rem;
  transition: color 0.15s;
}

.rp-eye:hover { color: #374151; }

.rp-field-error {
  display: block;
  font-size: 0.8rem;
  color: #dc2626;
  margin-top: 5px;
}

/* Hint */
.rp-hint {
  display: flex;
  align-items: flex-start;
  gap: 7px;
  font-size: 0.8rem;
  color: #9ca3af;
  margin: 0 0 20px;
  line-height: 1.5;
}

.rp-hint i { color: #d1d5db; margin-top: 2px; flex-shrink: 0; }

/* Button */
.rp-btn-primary {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  height: 50px;
  border: 0;
  border-radius: 12px;
  background: linear-gradient(90deg, #ea580c 0%, #f97316 55%, #fb923c 100%);
  color: #fff;
  font-size: 0.95rem;
  font-weight: 800;
  cursor: pointer;
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(249, 115, 22, 0.3);
  transition: opacity 0.15s, box-shadow 0.15s;
}

.rp-btn-primary:hover:not(:disabled) {
  box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
  color: #fff;
}

.rp-btn-primary:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  box-shadow: none;
}

/* Footer */
.rp-footer {
  border-top: 1px solid #e5e7eb;
  padding-top: 18px;
  margin-top: 20px;
  text-align: center;
}

.rp-back-link {
  font-size: 0.88rem;
  font-weight: 700;
  color: #f97316;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: color 0.15s;
}

.rp-back-link:hover {
  color: #ea580c;
  text-decoration: underline;
}

@media (max-width: 480px) {
  .rp-card {
    padding: 28px 22px 24px;
  }

  .rp-title {
    font-size: 1.3rem;
  }
}
</style>
