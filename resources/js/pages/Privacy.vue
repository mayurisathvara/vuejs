<template>
  <div>
    <!-- Hero Section -->
    <section class="relative overflow-hidden" role="banner" aria-label="Privacy Policy">
      <div class="absolute inset-0" style="background: linear-gradient(135deg, #F9FAFB 0%, #EEF2F7 50%, #F3F4F6 100%);" aria-hidden="true"></div>
      <div class="absolute top-0 right-0 w-[500px] h-[500px] opacity-60" style="background: radial-gradient(circle, rgba(255, 115, 50, 0.18) 0%, transparent 70%);" aria-hidden="true"></div>
      <div class="absolute bottom-0 left-0 w-[400px] h-[400px] opacity-40" style="background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 70%);" aria-hidden="true"></div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-16">
        <div class="text-center max-w-3xl mx-auto">
          <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-orange-100 mb-4">
            <svg class="w-4 h-4 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Legal</span>
          </div>
          <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3 leading-tight">
            Privacy
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-orange-400"> Policy</span>
          </h1>
          <p v-if="page" class="text-base text-gray-500">Last updated: {{ page.last_updated }}</p>
        </div>
      </div>
    </section>

    <!-- Loading State -->
    <section v-if="loading" class="py-20 bg-white">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="animate-pulse space-y-4">
          <div class="h-4 bg-gray-200 rounded w-3/4 mx-auto"></div>
          <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto"></div>
          <div class="h-4 bg-gray-200 rounded w-2/3 mx-auto"></div>
        </div>
      </div>
    </section>

    <!-- Error State -->
    <section v-else-if="error" class="py-20 bg-white">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-red-500">{{ error }}</p>
      </div>
    </section>

    <!-- Content Section -->
    <section v-else-if="page" class="py-20 bg-white">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose prose-lg max-w-none">

          <!-- Intro box -->
          <div class="p-6 mb-8" style="background-color: #fff5e6; border-left: 4px solid #ff6b00;">
            <p class="text-gray-700">{{ page.intro_text }}</p>
          </div>

          <!-- Sections -->
          <div class="space-y-8">
            <section v-for="section in page.sections" :key="section.heading">
              <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ section.heading }}</h2>
              <div class="text-gray-700 space-y-3">
                <template v-for="block in section.blocks" :key="block.type + block.text">

                  <h3 v-if="block.type === 'subheading'" class="text-xl font-semibold text-gray-900 mt-4">
                    {{ block.text }}
                  </h3>

                  <p v-else-if="block.type === 'paragraph'">{{ block.text }}</p>

                  <ul v-else-if="block.type === 'list'" class="list-disc pl-6 space-y-2">
                    <li v-for="item in block.items" :key="item">{{ item }}</li>
                  </ul>

                  <div v-else-if="block.type === 'contact'" class="bg-orange-50 border border-orange-100 p-6 rounded-xl mt-2">
                    <p class="font-bold text-gray-900 mb-2">Callytics Privacy Team</p>
                    <p>Email: <a :href="'mailto:' + block.email" class="text-orange-600 font-medium hover:underline">{{ block.email }}</a></p>
                    <p v-if="block.note" class="mt-2 text-sm text-gray-500">{{ block.note }}</p>
                  </div>

                </template>
              </div>
            </section>
          </div>

        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
          Have Questions About Our Privacy Policy?
        </h2>
        <p class="text-lg text-gray-600 mb-6">
          Our team is here to help you understand how we protect your data
        </p>
        <router-link
          to="/contact"
          class="inline-block px-8 py-3 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105"
          style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);"
        >
          Contact Us
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const page = ref(null)
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/legal/privacy')
    page.value = data
  } catch {
    error.value = 'Failed to load Privacy Policy. Please try again later.'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.prose h2 { margin-top: 2rem; margin-bottom: 1rem; }
.prose h3 { margin-top: 1.5rem; margin-bottom: 0.5rem; }
.prose p { margin-bottom: 1rem; }
.prose ul { margin-bottom: 1rem; }

@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
