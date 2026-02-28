<template>
  <div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
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
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
      <div class="sidebar-content">
        <ul class="nav nav-primary">
          <li class="nav-item" :class="{ active: $route.name === 'Dashboard' }">
            <router-link to="/dashboard" class="nav-link" @click="handleMenuClick">
              <i class="fas fa-home"></i>
              <p>Dashboard</p>
            </router-link>
          </li>
          
          <li v-if="!isUser" class="nav-section">
            <span class="sidebar-mini-icon">
              <i class="fa fa-ellipsis-h"></i>
            </span>
            <h4 class="text-section">Management</h4>
          </li>
          
          <li v-if="showUsers" class="nav-item" :class="{ active: $route.name === 'Users' || $route.name === 'assign-sims' }">
            <router-link to="/users" class="nav-link" @click="handleMenuClick">
              <i class="fas fa-users"></i>
              <p>Users</p>
            </router-link>
          </li>
          
          <li v-if="showOrganizations" class="nav-item" :class="{ active: $route.name === 'Organizations' }">
            <router-link to="/organizations" class="nav-link" @click="handleMenuClick">
              <i class="fas fa-building"></i>
              <p>Organizations</p>
            </router-link>
          </li>
          
          <li v-if="showDepartments" class="nav-item" :class="{ active: $route.name === 'Departments' }">
            <router-link to="/departments" class="nav-link" @click="handleMenuClick">
              <i class="fas fa-sitemap"></i>
              <p>Departments</p>
            </router-link>
          </li>
          
          <li v-if="showSims" class="nav-item" :class="{ active: $route.name === 'Sims' }">
            <router-link to="/sims" class="nav-link" @click="handleMenuClick">
              <i class="fas fa-mobile-alt"></i>
              <p>SIM Cards</p>
            </router-link>
          </li>

          <li v-if="showCallReports" class="nav-item" :class="{ active: $route.name === 'CallReports' }">
            <router-link to="/call-reports" class="nav-link" @click="handleMenuClick">
              <i class="fas fa-chart-bar"></i>
              <p>Call Reports</p>
            </router-link>
          </li>

          <li v-if="showSummaryReport" class="nav-item" :class="{ active: $route.name === 'SummaryReport' }">
            <router-link to="/summary-report" class="nav-link" @click="handleMenuClick">
              <i class="fas fa-chart-line"></i>
              <p>Summary Report</p>
            </router-link>
          </li>

          <li v-if="showOrganizationSettings" class="nav-item" :class="{ active: $route.name === 'OrganizationSettingsApp' }">
            <router-link to="/settings" class="nav-link" @click="handleMenuClick">
              <i class="fas fa-cog"></i>
              <p>Settings</p>
            </router-link>
          </li>
          
          <li v-if="showSettings" class="nav-item">
            <a href="#" class="nav-link" @click="handleMenuClick">
              <i class="fas fa-cog"></i>
              <p>Settings</p>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const sidebarCollapsed = ref(false)
const isMobile = ref(false)

// Computed properties for role-based menu visibility
const userRole = computed(() => authStore.user?.role || 'user')
const isAdmin = computed(() => userRole.value === 'admin')
const isOrganization = computed(() => userRole.value === 'organization')
const isManager = computed(() => userRole.value === 'manager')
const isUser = computed(() => userRole.value === 'user')

// Show menu items based on role
const showUsers = computed(() => isAdmin.value || isOrganization.value || isManager.value)
const showOrganizations = computed(() => isAdmin.value)
const showDepartments = computed(() => isAdmin.value || isOrganization.value)
const showSims = computed(() => isAdmin.value || isOrganization.value)
const showSettings = computed(() => isAdmin.value)
const showOrganizationSettings = computed(() => isOrganization.value)
const showCallReports = computed(() => isAdmin.value || isOrganization.value || isManager.value || isUser.value)
const showSummaryReport = computed(() => isAdmin.value || isOrganization.value || isManager.value || isUser.value)

const checkMobile = () => {
  isMobile.value = window.innerWidth <= 991.5
}

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
  
  if (isMobile.value) {
    // For mobile, use nav_open class on html element
    document.documentElement.classList.toggle('nav_open')
  } else {
    // For desktop, use sidebar-mini class on body
    document.body.classList.toggle('sidebar-mini')
  }
}

const closeSidebarOnOutsideClick = (event) => {
  if (isMobile.value && sidebarCollapsed.value) {
    const sidebar = document.querySelector('.sidebar')
    const toggleButton = document.querySelector('.sidenav-toggler')
    
    if (sidebar && !sidebar.contains(event.target) && !toggleButton?.contains(event.target)) {
      closeSidebar()
    }
  }
}

const closeSidebar = () => {
  if (isMobile.value) {
    document.documentElement.classList.remove('nav_open')
    sidebarCollapsed.value = false
  }
}

const handleMenuClick = () => {
  // Auto-close sidebar on mobile when menu item is clicked
  if (isMobile.value) {
    closeSidebar()
  }
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
  document.addEventListener('click', closeSidebarOnOutsideClick)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
  document.removeEventListener('click', closeSidebarOnOutsideClick)
})
</script>

<style scoped>
.app-logo {
  height: 42px;
  width: auto;
  max-width: 200px;
  display: block;
  object-fit: contain;
}
</style>
