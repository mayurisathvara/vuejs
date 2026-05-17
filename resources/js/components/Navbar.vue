<template>
  <div class="main-header">
    <div class="main-header-logo">
      <!-- Logo Header -->
      <div class="logo-header" data-background-color="dark">
        <router-link to="/dashboard" class="logo">
          <img :src="'/logo/logo.svg'" alt="Callytics" class="app-logo" />
        </router-link>
        <div class="nav-toggle">
          <button class="btn btn-toggle toggle-sidebar" @click="toggleSidebar">
            <i class="fa fa-bars"></i>
          </button>
          <button class="btn btn-toggle sidenav-toggler" @click="toggleSidebar">
            <i class="fa fa-bars"></i>
          </button>
        </div>
        <button class="topbar-toggler more">
          <i class="fa fa-ellipsis-v"></i>
        </button>
      </div>
      <!-- End Logo Header -->
    </div>
    
    <!-- Navbar Header -->
    <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
      <div class="container-fluid">
        <div class="navbar navbar-header-left navbar-expand-lg p-0 d-none d-lg-flex">
          <div v-if="isOrganization" class="org-app-code">
            <span class="org-app-code__label">APP LOGIN CODE</span>
            <span class="org-app-code__value">{{ organizationAppLoginCode }}</span>
          </div>
        </div>

        <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
          <!-- Notifications -->
          <li class="nav-item topbar-icon dropdown hidden-caret">
            <a
              class="nav-link dropdown-toggle"
              href="#"
              id="notifDropdown"
              role="button"
              data-bs-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
              @click="onNotifDropdownOpen"
            >
              <i class="fa fa-bell"></i>
              <span v-if="unreadCount > 0" class="notification">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
            </a>
            <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
              <li>
                <div class="dropdown-title d-flex align-items-center justify-content-between">
                  <span>
                    {{ unreadCount > 0 ? `${unreadCount} new notification${unreadCount > 1 ? 's' : ''}` : 'Notifications' }}
                  </span>
                  <button v-if="unreadCount > 0" type="button" class="notif-mark-all" @click.stop="markAllRead">
                    Mark all read
                  </button>
                </div>
              </li>
              <li>
                <div class="notif-scroll scrollbar-outer">
                  <div class="notif-center">
                    <!-- Loading -->
                    <div v-if="notifLoading" class="notif-empty">
                      <i class="fas fa-spinner fa-spin me-2"></i> Loading…
                    </div>

                    <!-- Empty -->
                    <div v-else-if="notifications.length === 0" class="notif-empty">
                      <i class="fas fa-bell-slash me-2 text-muted"></i> No notifications yet
                    </div>

                    <!-- List -->
                    <template v-else>
                      <a
                        v-for="notif in notifications"
                        :key="notif.id"
                        class="notif-item"
                        :class="{ 'notif-item--unread': !notif.read_at }"
                        href="#"
                        @click.prevent="handleNotifClick(notif)"
                      >
                        <div class="notif-icon-wrap" :class="notif.data.status !== 'ready' ? 'notif-icon-wrap--danger' : notif.data.report_type === 'summary_report' ? 'notif-icon-wrap--summary' : 'notif-icon-wrap--call'">
                          <i class="fas" :class="notif.data.status !== 'ready' ? 'fa-times' : notif.data.report_type === 'summary_report' ? 'fa-chart-bar' : 'fa-phone'"></i>
                        </div>
                        <div class="notif-body">
                          <span class="notif-msg">{{ notif.data.message }}</span>
                          <span class="notif-time">{{ notifTimeAgo(notif.created_at) }}</span>
                        </div>
                        <span v-if="!notif.read_at" class="notif-unread-dot"></span>
                      </a>
                    </template>
                  </div>
                </div>
              </li>
              <li>
                <router-link class="see-all" to="/generated-reports" @click="markAllRead">
                  View all reports <i class="fa fa-angle-right"></i>
                </router-link>
              </li>
            </ul>
          </li>

          <!-- Divider -->
          <li class="nav-item d-none d-md-flex align-items-center" aria-hidden="true">
            <span class="topbar-divider"></span>
          </li>

          <!-- User Profile -->
          <li class="nav-item topbar-user dropdown hidden-caret">
            <a
              class="dropdown-toggle profile-pic"
              data-bs-toggle="dropdown"
              href="#"
              aria-expanded="false"
            >
              <div class="avatar-sm">
                <div class="avatar-img rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="width: 32px; height: 32px;">
                  {{ user?.name?.charAt(0) || 'U' }}
                </div>
              </div>
              <span class="profile-username">
                <span class="fw-bold">{{ user?.name || 'User' }}</span>
              </span>
            </a>
            <ul class="dropdown-menu dropdown-user animated fadeIn">
              <div class="dropdown-user-scroll scrollbar-outer">
                <!-- User Info Section -->
                <li class="user-info-section">
                  <div class="d-flex align-items-center p-3">
                    <div class="avatar-sm me-3">
                      <div class="avatar-img rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="width: 40px; height: 40px; font-size: 1.1rem;">
                        {{ user?.name?.charAt(0) || 'U' }}
                      </div>
                    </div>
                    <div class="user-details">
                      <h6 class="mb-1 fw-bold text-dark">{{ user?.name || 'User' }}</h6>
                      <small class="text-muted">{{ user?.email || 'user@example.com' }}</small>
                    </div>
                  </div>
                </li>
                
                <!-- Menu Items -->
                <li>
                  <div class="dropdown-divider"></div>
                  <router-link class="dropdown-item d-flex align-items-center py-2" to="/profile">
                    <i class="fas fa-user me-3 text-muted"></i>
                    <span>My Profile</span>
                  </router-link>
                  <router-link class="dropdown-item d-flex align-items-center py-2" to="/change-password">
                    <i class="fas fa-key me-3 text-muted"></i>
                    <span>Change Password</span>
                  </router-link>
                  <router-link v-if="isOrganizationOnly" class="dropdown-item d-flex align-items-center py-2" to="/settings">
                    <i class="fas fa-cog me-3 text-muted"></i>
                    <span>Account Setting</span>
                  </router-link>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item d-flex align-items-center py-2 text-danger" href="#" @click.prevent="openLogoutModal">
                    <i class="fas fa-sign-out-alt me-3"></i>
                    <span>Logout</span>
                  </a>
                </li>
              </div>
            </ul>
          </li>
        </ul>
      </div>
    </nav>
    <!-- End Navbar -->

    <!-- Logout Confirmation Modal (SweetAlert-style) -->
    <Modal
      :show="showLogoutModal"
      title="Logout"
      centered
      @close="closeLogoutModal"
      @confirm="confirmLogout"
    >
      <div class="logout-modal-content">
        <p class="logout-question">Are you sure you want to logout?</p>
        <div class="info-card">
          <div class="info-name">{{ user?.name || 'User' }}</div>
          <div class="info-email">{{ user?.email || '' }}</div>
        </div>
        <p class="warning-text">
          <i class="fas fa-exclamation-triangle me-2"></i>
          This will end your current session.
        </p>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import Modal from '@/components/Modal.vue'
import api from '@/services/api'

const authStore = useAuthStore()
const router = useRouter()

const { user, userRole } = storeToRefs(authStore)
const isMobile = ref(false)

const isOrganization = computed(() => ['organization', 'manager'].includes(userRole.value))
const isOrganizationOnly = computed(() => userRole.value === 'organization')
const organizationAppLoginCode = computed(() => user.value?.organization?.app_login_code || '—')

const showLogoutModal = ref(false)

// ── Notifications ─────────────────────────────────────────
const notifications    = ref([])
const unreadCount      = ref(0)
const notifLoading     = ref(false)
const notifDropdownOpen = ref(false)
let   notifPollTimer   = null

const fetchUnreadCount = async () => {
  try {
    const resp = await api.get('/notifications/unread-count')
    unreadCount.value = resp.data.count ?? 0
  } catch {
    // silently fail for background poll
  }
}

const fetchNotifications = async () => {
  notifLoading.value = true
  try {
    const resp = await api.get('/notifications')
    notifications.value = resp.data
  } catch {
    // ignore
  } finally {
    notifLoading.value = false
  }
}

const onNotifDropdownOpen = async () => {
  notifDropdownOpen.value = true
  await fetchNotifications()
}

const markRead = async (id) => {
  try {
    await api.post(`/notifications/${id}/read`)
    const n = notifications.value.find(n => n.id === id)
    if (n) n.read_at = new Date().toISOString()
    if (unreadCount.value > 0) unreadCount.value--
  } catch {
    // ignore
  }
}

const markAllRead = async () => {
  try {
    await api.post('/notifications/read-all')
    notifications.value.forEach(n => { if (!n.read_at) n.read_at = new Date().toISOString() })
    unreadCount.value = 0
  } catch {
    // ignore
  }
}

const handleNotifClick = async (notif) => {
  if (!notif.read_at) await markRead(notif.id)
  if (notif.data?.url) router.push(notif.data.url)
}

const notifTimeAgo = (iso) => {
  if (!iso) return ''
  const diff = Math.floor((Date.now() - new Date(iso)) / 1000)
  if (diff < 60)   return 'just now'
  if (diff < 3600) return Math.floor(diff / 60) + 'm ago'
  if (diff < 86400) return Math.floor(diff / 3600) + 'h ago'
  return Math.floor(diff / 86400) + 'd ago'
}

// ── Sidebar toggle ────────────────────────────────────────
const checkMobile = () => {
  isMobile.value = window.innerWidth <= 991.5
}

const toggleSidebar = () => {
  if (isMobile.value) {
    document.documentElement.classList.toggle('nav_open')
  } else {
    document.body.classList.toggle('sidebar-mini')
  }
}

// ── Logout ────────────────────────────────────────────────
const openLogoutModal = () => {
  showLogoutModal.value = true
}

const closeLogoutModal = () => {
  showLogoutModal.value = false
}

const confirmLogout = async () => {
  try {
    await authStore.logout()
  } finally {
    showLogoutModal.value = false
    router.push('/login')
  }
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
  fetchUnreadCount()
  // Poll every 30 seconds
  notifPollTimer = setInterval(fetchUnreadCount, 30000)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
  if (notifPollTimer) clearInterval(notifPollTimer)
})
</script>

<style scoped>
.topbar-divider {
  width: 1px;
  height: 34px;
  background: rgba(0, 0, 0, 0.14);
  margin: 0 0 0 5px;
}

.org-app-code {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 6px 10px;
  border: 1px solid rgba(var(--bs-body-color-rgb), 0.12);
  border-radius: 12px;
  background: var(--bs-body-bg);
  line-height: 1;
}

.org-app-code__label {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: rgba(var(--bs-body-color-rgb), 0.55);
  white-space: nowrap;
}

.org-app-code__value {
  font-size: 13px;
  font-weight: 800;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  padding: 6px 10px;
  border-radius: 10px;
  border: 1px solid rgba(var(--bs-body-color-rgb), 0.12);
  background: rgba(var(--bs-body-color-rgb), 0.06);
  color: var(--bs-body-color);
  white-space: nowrap;
}

/* Logout confirmation modal styling (matches existing delete confirmation look) */
.logout-modal-content {
  padding: 8px 0;
}

.logout-question {
  font-size: 15px;
  color: #3a3b45;
  margin-bottom: 20px;
  font-weight: 500;
  line-height: 1.5;
}

.info-card {
  background-color: #f8f9fc;
  border: 1px solid #e3e6f0;
  border-radius: 8px;
  padding: 16px 20px;
  margin-bottom: 20px;
}

.info-name {
  font-size: 16px;
  font-weight: 600;
  color: #2c2d3a;
  margin-bottom: 6px;
}

.info-email {
  font-size: 14px;
  color: #6c757d;
}

.warning-text {
  color: #dc3545;
  font-size: 14px;
  font-weight: 500;
  margin: 0;
  display: flex;
  align-items: center;
}

.warning-text i {
  color: #dc3545;
}

.app-logo {
  height: 34px;
  width: auto;
  max-width: 200px;
  display: block;
  object-fit: contain;
}

/* ── Notification dropdown enhancements ── */
.notif-mark-all {
  font-size: 11px;
  font-weight: 600;
  color: #2563eb;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  line-height: 1;
}
.notif-mark-all:hover { color: #1d4ed8; text-decoration: underline; }

.notif-empty {
  padding: 2rem 1rem;
  text-align: center;
  color: #94a3b8;
  font-size: 13px;
}

.notif-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 16px;
  text-decoration: none !important;
  position: relative;
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.15s;
}
.notif-item:hover { background: #f8fafc; }
.notif-item--unread { background: #f5f9ff; }
.notif-item--unread:hover { background: #edf4ff; }

.notif-icon-wrap {
  flex-shrink: 0;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
}
.notif-icon-wrap--call {
  background: #dbeafe;
  color: #2563eb;
}
.notif-icon-wrap--summary {
  background: #f3e8ff;
  color: #9333ea;
}
.notif-icon-wrap--danger {
  background: #fee2e2;
  color: #dc2626;
}

.notif-body {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.notif-msg {
  font-size: 13px;
  font-weight: 500;
  color: #1e293b;
  line-height: 1.4;
  display: block;
  white-space: normal;
}
.notif-time {
  font-size: 11px;
  color: #94a3b8;
  display: block;
}

.notif-unread-dot {
  flex-shrink: 0;
  width: 7px;
  height: 7px;
  background: #2563eb;
  border-radius: 50%;
  align-self: center;
}
</style>
