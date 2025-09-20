<template>
  <div class="wrapper">
    <!-- Sidebar -->
    <Sidebar />
    <!-- End Sidebar -->

    <div class="main-panel">
      <!-- Navbar -->
      <Navbar />
      <!-- End Navbar -->

      <div class="container">
        <div class="page-inner">
          <router-view />
        </div>
      </div>

      <!-- Footer -->
      <Footer />
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import Sidebar from '@/components/Sidebar.vue'
import Navbar from '@/components/Navbar.vue'
import Footer from '@/components/Footer.vue'

onMounted(() => {
  // Initialize theme scripts if needed
  if (typeof window !== 'undefined' && window.$) {
    // jQuery is available, initialize theme components
    console.log('Theme initialized')
  }
})
</script>
