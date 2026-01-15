<template>
  <div class="main-header">
    <div class="main-header-logo">
      <!-- Logo Header -->
      <div class="logo-header" data-background-color="dark">
        <router-link to="/dashboard" class="logo">
          <div class="navbar-brand fw-bold text-white">Vue Admin</div>
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
            >
              <i class="fa fa-bell"></i>
              <span class="notification">4</span>
            </a>
            <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
              <li>
                <div class="dropdown-title">You have 4 new notification</div>
              </li>
              <li>
                <div class="notif-scroll scrollbar-outer">
                  <div class="notif-center">
                    <a href="#">
                      <div class="notif-icon notif-primary">
                        <i class="fa fa-user-plus"></i>
                      </div>
                      <div class="notif-content">
                        <span class="block">New user registered</span>
                        <span class="time">5 minutes ago</span>
                      </div>
                    </a>
                    <a href="#">
                      <div class="notif-icon notif-success">
                        <i class="fa fa-comment"></i>
                      </div>
                      <div class="notif-content">
                        <span class="block">New comment received</span>
                        <span class="time">12 minutes ago</span>
                      </div>
                    </a>
                  </div>
                </div>
              </li>
              <li>
                <a class="see-all" href="javascript:void(0);">
                  See all notifications<i class="fa fa-angle-right"></i>
                </a>
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
import { ref, onMounted, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import Modal from '@/components/Modal.vue'

const authStore = useAuthStore()
const router = useRouter()

const { user, userRole } = storeToRefs(authStore)
const isMobile = ref(false)

const isOrganization = computed(() => ['organization', 'manager'].includes(userRole.value))
const isOrganizationOnly = computed(() => userRole.value === 'organization')
const organizationAppLoginCode = computed(() => user.value?.organization?.app_login_code || '—')

const showLogoutModal = ref(false)

const checkMobile = () => {
  isMobile.value = window.innerWidth <= 991.5
}

const toggleSidebar = () => {
  if (isMobile.value) {
    // For mobile, use nav_open class on html element
    document.documentElement.classList.toggle('nav_open')
  } else {
    // For desktop, use sidebar-mini class on body
    document.body.classList.toggle('sidebar-mini')
  }
}

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
</style>
