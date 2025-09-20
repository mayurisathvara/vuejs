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
        <nav class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex">
          <div class="input-group">
            <div class="input-group-prepend">
              <button type="submit" class="btn btn-search pe-1">
                <i class="fa fa-search search-icon"></i>
              </button>
            </div>
            <input
              type="text"
              placeholder="Search ..."
              class="form-control"
              v-model="searchQuery"
            />
          </div>
        </nav>

        <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
          <!-- Search for mobile -->
          <li class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none">
            <a
              class="nav-link dropdown-toggle"
              data-bs-toggle="dropdown"
              href="#"
              role="button"
              aria-expanded="false"
              aria-haspopup="true"
            >
              <i class="fa fa-search"></i>
            </a>
            <ul class="dropdown-menu dropdown-search animated fadeIn">
              <form class="navbar-left navbar-form nav-search">
                <div class="input-group">
                  <input
                    type="text"
                    placeholder="Search ..."
                    class="form-control"
                    v-model="searchQuery"
                  />
                </div>
              </form>
            </ul>
          </li>

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
                <span class="op-7">Hi,</span>
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
                  <a class="dropdown-item d-flex align-items-center py-2" href="#">
                    <i class="fas fa-cog me-3 text-muted"></i>
                    <span>Account Setting</span>
                  </a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item d-flex align-items-center py-2 text-danger" href="#" @click="logout">
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const searchQuery = ref('')
const user = authStore.user
const isMobile = ref(false)

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

const logout = async () => {
  await authStore.logout()
  router.push('/login')
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})
</script>
