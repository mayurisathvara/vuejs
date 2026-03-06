<template>
  <div id="app" class="min-h-screen flex flex-col">
    <MainLayout>
      <router-view />
    </MainLayout>
    
    <!-- Scroll to Top Button -->
    <transition name="fade">
      <button
        v-if="showScrollTop"
        @click="scrollToTop"
        class="fixed bottom-8 right-8 text-white w-12 h-12 rounded-full shadow-lg transition-all duration-300 flex items-center justify-center z-50"
        aria-label="Scroll to top"
        style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);"
        @mouseover="$event.target.style.transform='scale(1.1)'"
        @mouseout="$event.target.style.transform='scale(1)'"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
      </button>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import MainLayout from './layouts/MainLayout.vue';

const showScrollTop = ref(false);

const handleScroll = () => {
  showScrollTop.value = window.scrollY > 300;
};

const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
