<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

onMounted(() => {
  // Initialize authentication
  authStore.initializeAuth()
  
  // Initialize theme after DOM is loaded
  if (typeof window !== 'undefined') {
    // Wait for fonts to load
    if (sessionStorage.fonts) {
      document.documentElement.classList.add('fonts-loaded')
    }
    
    // Initialize theme components
    setTimeout(() => {
      if (window.$ && window.$.fn.scrollbar) {
        $('.scrollbar-outer').scrollbar()
      }
    }, 100)
  }
})
</script>

<style>
/* Global styles */
body {
  font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* Custom scrollbar */
.scrollbar-outer {
  overflow-y: auto;
}

.scrollbar-outer::-webkit-scrollbar {
  width: 6px;
}

.scrollbar-outer::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.scrollbar-outer::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.scrollbar-outer::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Loading spinner */
.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

/* Badge styles */
.badge {
  font-size: 0.75em;
  font-weight: 500;
}

.badge-danger {
  background-color: #dc3545;
}

.badge-warning {
  background-color: #ffc107;
  color: #000;
}

.badge-info {
  background-color: #17a2b8;
}

.badge-secondary {
  background-color: #6c757d;
}

/* Card styles */
.card-stats .icon-big {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.icon-primary {
  background-color: rgba(23, 125, 255, 0.1);
  color: #177dff;
}

.icon-info {
  background-color: rgba(23, 125, 255, 0.1);
  color: #17a2b8;
}

.icon-success {
  background-color: rgba(40, 167, 69, 0.1);
  color: #28a745;
}

.icon-secondary {
  background-color: rgba(108, 117, 125, 0.1);
  color: #6c757d;
}

.bubble-shadow-small {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Sidebar styles */
.sidebar-mini .sidebar {
  width: 80px;
}

.sidebar-mini .main-panel {
  margin-left: 80px;
}

/* Mobile sidebar overlay */
@media (max-width: 991.5px) {
  .nav_open::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
  }
  
  .nav_open .sidebar {
    z-index: 1000;
  }
}
</style>
