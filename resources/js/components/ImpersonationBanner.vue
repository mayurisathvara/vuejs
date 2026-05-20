<template>
  <div v-if="authStore.isImpersonating" class="impersonation-banner" role="alert">
    <div class="banner-inner">
      <div class="banner-left">
        <span class="banner-icon-wrap">
          <i class="fas fa-user-secret"></i>
        </span>
        <span class="banner-text">
          You are logged in as
          <strong class="banner-org-name">{{ authStore.impersonatedOrgName }}</strong>
        </span>
      </div>
      <button
        type="button"
        class="back-btn"
        :disabled="authStore.loading"
        @click="handleBack"
      >
        <i class="fas fa-arrow-left"></i>
        <span>Back to Admin</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const handleBack = async () => {
  try {
    await authStore.stopImpersonating()
  } catch (error) {
    console.error('Failed to stop impersonation:', error)
  }
  router.push('/organizations')
}
</script>

<style scoped>
.impersonation-banner {
  position: sticky;
  top: 0;
  z-index: 1100;
  background: linear-gradient(90deg, #b45309, #d97706);
  color: #fff;
  box-shadow: 0 2px 8px rgba(180, 83, 9, 0.35);
}

.banner-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 24px;
  gap: 16px;
  flex-wrap: wrap;
}

.banner-left {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
  min-width: 0;
}

.banner-icon-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  background: rgba(255, 255, 255, 0.18);
  border-radius: 50%;
  flex-shrink: 0;
  font-size: 13px;
}

.banner-text {
  font-size: 13.5px;
  font-weight: 500;
  line-height: 1.4;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.banner-org-name {
  font-weight: 700;
  margin-left: 4px;
}

.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 7px 16px;
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
  border: 1.5px solid rgba(255, 255, 255, 0.5);
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.15s, border-color 0.15s;
  flex-shrink: 0;
}

.back-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.32);
  border-color: rgba(255, 255, 255, 0.75);
}

.back-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.back-btn i {
  font-size: 11px;
}
</style>
