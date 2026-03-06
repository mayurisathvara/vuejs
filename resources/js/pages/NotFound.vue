<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full text-center">
      <!-- 404 Number -->
      <div class="mb-8">
        <h1 class="text-9xl font-black bg-gradient-to-r from-orange-500 to-orange-600 bg-clip-text text-transparent">
          404
        </h1>
      </div>

      <!-- Error Message -->
      <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">
          Oops! Page Not Found
        </h2>
        <p class="text-lg text-gray-600 mb-2">
          The page you're looking for doesn't exist or has been moved.
        </p>
        <p class="text-md text-gray-500">
          Let's get you back on track!
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
        <router-link 
          to="/"
          class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          Go Home
        </router-link>
        
        <button 
          @click="goBack"
          class="inline-flex items-center justify-center px-8 py-3 border-2 border-orange-500 text-base font-medium rounded-lg text-orange-600 bg-white hover:bg-orange-50 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Go Back
        </button>
      </div>

      <!-- Quick Links -->
      <div class="border-t border-gray-200 pt-8">
        <p class="text-sm text-gray-500 mb-4">Or explore these popular pages:</p>
        <div class="flex flex-wrap justify-center gap-4">
          <router-link to="/about" class="text-orange-600 hover:text-orange-700 hover:underline transition-colors">
            About Us
          </router-link>
          <router-link to="/services" class="text-orange-600 hover:text-orange-700 hover:underline transition-colors">
            Services
          </router-link>
          <router-link to="/pricing" class="text-orange-600 hover:text-orange-700 hover:underline transition-colors">
            Pricing
          </router-link>
          <router-link to="/contact" class="text-orange-600 hover:text-orange-700 hover:underline transition-colors">
            Contact
          </router-link>
          <router-link to="/help" class="text-orange-600 hover:text-orange-700 hover:underline transition-colors">
            Help Center
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

const goBack = () => {
  // Check if there's history to go back to
  if (window.history.length > 1) {
    router.go(-1);
  } else {
    // If no history, go to home
    router.push('/');
  }
};
</script>

<style scoped>
/* Additional styles if needed */
</style>
