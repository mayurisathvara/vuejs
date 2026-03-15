<template>
  <footer class="footer">
    <div class="container-fluid d-flex justify-content-center">
      <div class="copyright">
        © {{ currentYear }} All rights reserved.
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>
