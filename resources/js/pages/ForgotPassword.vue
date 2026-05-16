<template>
  <div class="fp-page">
    <div class="fp-card">

      <!-- Logo -->
      <div class="fp-logo">
        <img :src="'/logo/login_icon.png'" alt="Callytics" class="fp-logo-img" />
        <span class="fp-logo-name">Callytics</span>
      </div>

      <!-- Success state -->
      <template v-if="submitted">
        <div class="fp-icon-wrap fp-icon-wrap--success">
          <i class="fas fa-paper-plane fp-icon"></i>
        </div>
        <h1 class="fp-title">Check Your Inbox</h1>
        <p class="fp-sub">
          If <strong>{{ sentTo }}</strong> is registered, we've sent a password reset link. Check your inbox (and spam folder).
        </p>
        <div class="fp-info-box">
          <i class="fas fa-clock"></i>
          <span>The reset link expires in <strong>60 minutes</strong>.</span>
        </div>
        <div class="fp-actions">
          <router-link to="/login" class="fp-btn-primary">
            <i class="fas fa-arrow-left"></i>
            Back to Sign In
          </router-link>
        </div>
      </template>

      <!-- Form state -->
      <template v-else>
        <div class="fp-icon-wrap">
          <i class="fas fa-lock-open fp-icon"></i>
        </div>
        <h1 class="fp-title">Forgot Password?</h1>
        <p class="fp-sub">
          Enter the email address for your account and we'll send you a reset link.
        </p>

        <!-- Error alert -->
        <div v-if="errorMessage" class="fp-alert fp-alert--error" role="alert">
          <i class="fas fa-exclamation-circle"></i>
          <span>{{ errorMessage }}</span>
        </div>

        <form @submit.prevent="handleSubmit" novalidate>
          <div class="fp-field">
            <label class="fp-label" for="fp-email">Email Address</label>
            <input
              id="fp-email"
              v-model="email"
              type="email"
              class="fp-input"
              :class="{ 'fp-input--error': emailError }"
              placeholder="you@company.com"
              autocomplete="email"
              required
            />
            <span v-if="emailError" class="fp-field-error">{{ emailError }}</span>
          </div>

          <button type="submit" class="fp-btn-primary" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <i v-else class="fas fa-paper-plane"></i>
            <span>{{ loading ? 'Sending…' : 'Send Reset Link' }}</span>
          </button>
        </form>

        <div class="fp-footer">
          <router-link to="/login" class="fp-back-link">
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
import api from '@/services/api'

const email        = ref('')
const emailError   = ref('')
const errorMessage = ref('')
const loading      = ref(false)
const submitted    = ref(false)
const sentTo       = ref('')

const handleSubmit = async () => {
  emailError.value   = ''
  errorMessage.value = ''

  if (!email.value.trim()) {
    emailError.value = 'Email address is required.'
    return
  }

  loading.value = true

  try {
    await api.post('/forgot-password', { email: email.value.trim() })
    sentTo.value    = email.value.trim()
    submitted.value = true
  } catch (err) {
    if (err.response?.status === 422) {
      const errs = err.response.data.errors || {}
      emailError.value = errs.email?.[0] || errs.email || ''
    }
    errorMessage.value = err.response?.data?.message || 'Something went wrong. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.fp-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(145deg, #0f172a 0%, #1e1b4b 40%, #312e81 75%, #1e1b4b 100%);
  padding: 24px 16px;
}

.fp-card {
  background: #fff;
  border-radius: 24px;
  padding: 40px 40px 32px;
  width: 100%;
  max-width: 440px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

/* Logo */
.fp-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 28px;
  justify-content: center;
}

.fp-logo-img {
  width: 36px;
  height: 36px;
  border-radius: 9px;
  object-fit: cover;
}

.fp-logo-name {
  font-size: 1.2rem;
  font-weight: 900;
  color: #10233f;
  letter-spacing: -0.02em;
}

/* Icon */
.fp-icon-wrap {
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

.fp-icon-wrap--success {
  background: linear-gradient(135deg, #f0fdf4, #dcfce7);
  border-color: #bbf7d0;
}

.fp-icon {
  font-size: 1.6rem;
  color: #f97316;
}

.fp-icon-wrap--success .fp-icon {
  color: #16a34a;
}

/* Title & sub */
.fp-title {
  font-size: 1.55rem;
  font-weight: 800;
  color: #10233f;
  text-align: center;
  margin: 0 0 10px;
  letter-spacing: -0.02em;
}

.fp-sub {
  font-size: 0.95rem;
  color: #6b7280;
  text-align: center;
  line-height: 1.65;
  margin: 0 0 24px;
}

.fp-sub strong { color: #10233f; }

/* Info box (success) */
.fp-info-box {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f8fafc;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 12px 16px;
  font-size: 0.88rem;
  color: #374151;
  margin-bottom: 24px;
}

.fp-info-box i { color: #f97316; flex-shrink: 0; }
.fp-info-box strong { color: #10233f; }

/* Alert */
.fp-alert {
  display: flex;
  align-items: center;
  gap: 10px;
  border-radius: 10px;
  padding: 12px 16px;
  font-size: 0.88rem;
  font-weight: 600;
  margin-bottom: 18px;
}

.fp-alert--error {
  background: #fff1f2;
  border: 1.5px solid #fecdd3;
  color: #be123c;
}

/* Form */
.fp-field {
  margin-bottom: 20px;
}

.fp-label {
  display: block;
  font-size: 0.83rem;
  font-weight: 700;
  color: #374151;
  margin-bottom: 6px;
  letter-spacing: 0.01em;
}

.fp-input {
  width: 100%;
  height: 48px;
  padding: 0 14px;
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.97rem;
  color: #111827;
  background: #fff;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.18s, box-shadow 0.18s;
}

.fp-input:focus {
  border-color: #f97316;
  box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
}

.fp-input--error { border-color: #ef4444; }

.fp-field-error {
  display: block;
  font-size: 0.8rem;
  color: #dc2626;
  margin-top: 5px;
}

/* Buttons */
.fp-btn-primary {
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

.fp-btn-primary:hover:not(:disabled) {
  box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
  color: #fff;
}

.fp-btn-primary:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  box-shadow: none;
}

/* Footer */
.fp-footer {
  border-top: 1px solid #e5e7eb;
  padding-top: 18px;
  margin-top: 20px;
  text-align: center;
}

.fp-back-link {
  font-size: 0.88rem;
  font-weight: 700;
  color: #f97316;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: color 0.15s;
}

.fp-back-link:hover {
  color: #ea580c;
  text-decoration: underline;
}

/* Actions (success state) */
.fp-actions { margin-bottom: 0; }

@media (max-width: 480px) {
  .fp-card {
    padding: 28px 22px 24px;
  }

  .fp-title {
    font-size: 1.3rem;
  }
}
</style>
