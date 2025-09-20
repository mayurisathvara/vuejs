<template>
  <footer class="footer">
    <div class="container-fluid d-flex justify-content-between">
      <nav class="pull-left">
        <ul class="nav">
          <li class="nav-item">
            <a class="nav-link" href="#" target="_blank">
              Vue 3 Admin
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Help</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Licenses</a>
          </li>
        </ul>
      </nav>
      <div class="copyright">
        {{ currentYear }}, made with <i class="fa fa-heart heart text-danger"></i> by
        <a href="#" target="_blank">Vue 3 Team</a>
      </div>
      <div>
        Distributed by
        <a target="_blank" href="https://laravel.com/">Laravel</a>.
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>
