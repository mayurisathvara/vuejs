<template>
  <div class="ve-page">
    <div class="ve-card">

      <!-- Logo -->
      <div class="ve-logo">
        <img :src="'/logo/login_icon.png'" alt="Callytics" class="ve-logo-img" />
        <span class="ve-logo-name">Callytics</span>
      </div>

      <!-- Expired / Invalid state -->
      <template v-if="linkStatus === 'expired' || linkStatus === 'invalid'">
        <div class="ve-icon-wrap ve-icon-wrap--warn">
          <i class="fas fa-exclamation-triangle ve-icon"></i>
        </div>
        <h1 class="ve-title">{{ linkStatus === 'expired' ? 'Link Expired' : 'Invalid Link' }}</h1>
        <p class="ve-sub">
          {{ linkStatus === 'expired'
            ? 'Your verification link has expired. Request a new one below.'
            : 'This verification link is invalid. Request a new one below.' }}
        </p>
      </template>

      <!-- Default: check inbox -->
      <template v-else>
        <div class="ve-icon-wrap">
          <i class="fas fa-envelope-open ve-icon"></i>
        </div>
        <h1 class="ve-title">Check Your Email</h1>
        <p class="ve-sub">
          We've sent a verification link to
          <strong v-if="email">{{ email }}</strong><span v-else>your email address</span>.
          Click the link in the email to activate your account.
        </p>
      </template>

      <!-- Steps -->
      <ol class="ve-steps">
        <li>Open your email inbox</li>
        <li>Find the email from Callytics</li>
        <li>Click <strong>"Verify Email Address"</strong></li>
      </ol>

      <!-- Success feedback -->
      <div v-if="resendSuccess" class="ve-alert ve-alert--success" role="alert">
        <i class="fas fa-check-circle"></i>
        <span>Verification email sent! Please check your inbox.</span>
      </div>

      <!-- Error feedback -->
      <div v-if="resendError" class="ve-alert ve-alert--error" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ resendError }}</span>
      </div>

      <!-- Resend button -->
      <div class="ve-actions">
        <button
          type="button"
          class="ve-btn-primary"
          :disabled="resendLoading || resendCooldown > 0"
          @click="resendEmail"
        >
          <span v-if="resendLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          <i v-else class="fas fa-paper-plane"></i>
          <span v-if="resendCooldown > 0">Resend in {{ resendCooldown }}s</span>
          <span v-else-if="resendLoading">Sending…</span>
          <span v-else>Resend Verification Email</span>
        </button>
      </div>

      <!-- Spam note -->
      <p class="ve-spam-note">
        <i class="fas fa-info-circle"></i>
        Can't find the email? Check your spam or junk folder.
      </p>

      <!-- Back to login -->
      <div class="ve-footer">
        <router-link to="/login" class="ve-back-link">
          <i class="fas fa-arrow-left"></i>
          Back to Sign In
        </router-link>
      </div>

    </div>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'

const route = useRoute()

const email         = ref(route.query.email || '')
const linkStatus    = ref(route.query.status || '')  // 'expired' | 'invalid' | ''
const resendLoading = ref(false)
const resendSuccess = ref(false)
const resendError   = ref('')
const resendCooldown = ref(0)

let cooldownTimer = null

const startCooldown = (seconds = 60) => {
  resendCooldown.value = seconds
  cooldownTimer = setInterval(() => {
    resendCooldown.value--
    if (resendCooldown.value <= 0) {
      clearInterval(cooldownTimer)
    }
  }, 1000)
}

const resendEmail = async () => {
  if (!email.value) {
    resendError.value = 'Please enter your email address.'
    return
  }

  resendLoading.value = true
  resendSuccess.value = false
  resendError.value   = ''

  try {
    await api.post('/email/resend', { email: email.value })
    resendSuccess.value = true
    startCooldown(60)
  } catch (err) {
    resendError.value = err.response?.data?.message || 'Failed to send. Please try again.'
  } finally {
    resendLoading.value = false
  }
}

onBeforeUnmount(() => {
  if (cooldownTimer) clearInterval(cooldownTimer)
})
</script>

<style scoped>
.ve-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(145deg, #0f172a 0%, #1e1b4b 40%, #312e81 75%, #1e1b4b 100%);
  padding: 24px 16px;
}

.ve-card {
  background: #fff;
  border-radius: 24px;
  padding: 40px 40px 32px;
  width: 100%;
  max-width: 480px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

/* Logo */
.ve-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 28px;
  justify-content: center;
}

.ve-logo-img {
  width: 36px;
  height: 36px;
  border-radius: 9px;
  object-fit: cover;
}

.ve-logo-name {
  font-size: 1.2rem;
  font-weight: 900;
  color: #10233f;
  letter-spacing: -0.02em;
}

/* Icon */
.ve-icon-wrap {
  width: 72px;
  height: 72px;
  background: linear-gradient(135deg, #fff7ed, #ffedd5);
  border: 2px solid #fed7aa;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
}

.ve-icon-wrap--warn {
  background: linear-gradient(135deg, #fff7ed, #fef3c7);
  border-color: #fcd34d;
}

.ve-icon {
  font-size: 1.8rem;
  color: #f97316;
}

.ve-icon-wrap--warn .ve-icon {
  color: #d97706;
}

/* Title & subtitle */
.ve-title {
  font-size: 1.55rem;
  font-weight: 800;
  color: #10233f;
  text-align: center;
  margin: 0 0 10px;
  letter-spacing: -0.02em;
}

.ve-sub {
  font-size: 0.95rem;
  color: #6b7280;
  text-align: center;
  line-height: 1.65;
  margin: 0 0 22px;
}

.ve-sub strong {
  color: #10233f;
  font-weight: 700;
}

/* Steps */
.ve-steps {
  margin: 0 0 24px;
  padding: 16px 20px 16px 36px;
  background: #f8fafc;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  color: #374151;
  font-size: 0.88rem;
  line-height: 1.8;
}

.ve-steps li { margin: 0; }
.ve-steps strong { color: #10233f; }

/* Alerts */
.ve-alert {
  display: flex;
  align-items: center;
  gap: 10px;
  border-radius: 10px;
  padding: 12px 16px;
  font-size: 0.88rem;
  font-weight: 600;
  margin-bottom: 16px;
}

.ve-alert--success {
  background: #f0fdf4;
  border: 1.5px solid #bbf7d0;
  color: #15803d;
}

.ve-alert--error {
  background: #fff1f2;
  border: 1.5px solid #fecdd3;
  color: #be123c;
}

/* Actions */
.ve-actions {
  margin-bottom: 12px;
}

.ve-btn-primary {
  width: 100%;
  height: 50px;
  border: 0;
  border-radius: 12px;
  background: linear-gradient(90deg, #ea580c 0%, #f97316 55%, #fb923c 100%);
  color: #fff;
  font-size: 0.95rem;
  font-weight: 800;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  box-shadow: 0 4px 14px rgba(249, 115, 22, 0.3);
  transition: opacity 0.15s, box-shadow 0.15s;
}

.ve-btn-primary:hover:not(:disabled) {
  box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
}

.ve-btn-primary:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  box-shadow: none;
}

/* Spam note */
.ve-spam-note {
  font-size: 0.8rem;
  color: #9ca3af;
  text-align: center;
  margin: 10px 0 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

/* Footer */
.ve-footer {
  border-top: 1px solid #e5e7eb;
  padding-top: 18px;
  text-align: center;
}

.ve-back-link {
  font-size: 0.88rem;
  font-weight: 700;
  color: #f97316;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: color 0.15s;
}

.ve-back-link:hover {
  color: #ea580c;
  text-decoration: underline;
}

@media (max-width: 520px) {
  .ve-card {
    padding: 28px 22px 24px;
  }

  .ve-title {
    font-size: 1.3rem;
  }
}
</style>
