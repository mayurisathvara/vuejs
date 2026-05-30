<template>
  <nav class="bg-white shadow-md sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Logo -->
        <router-link to="/" class="flex items-center">
          <img src="../assets/images/logo.png" alt="Callytics" class="h-10" />
        </router-link>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-8">
          <router-link
            to="/"
            class="nav-link"
            :class="{ 'active': isActive('/') }"
          >
            Features
          </router-link>

          <router-link
            to="/pricing"
            class="nav-link"
            :class="{ 'active': isActive('/pricing') }"
          >
            Pricing
          </router-link>

          <router-link
            to="/#features"
            class="nav-link"
            :class="{ 'active': isActive('/#features') }"
          >
            How it Works
          </router-link>

          <!-- Company Dropdown -->
          <div class="relative" @mouseenter="companyDropdownOpen = true" @mouseleave="companyDropdownOpen = false">
            <button class="nav-link flex items-center" :class="{ 'active': isCompanyActive }">
              Company
              <svg class="w-4 h-4 ml-1 transition-transform duration-200" :class="{ 'rotate-180': companyDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            
            <!-- Dropdown Menu -->
            <transition name="dropdown">
              <div v-if="companyDropdownOpen" class="absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-100 py-2 z-50">
                <!-- About Us menu item commented out -->
                <!-- <router-link
                  to="/about"
                  class="dropdown-item"
                  @click="companyDropdownOpen = false"
                >
                  <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  About Us
                </router-link> -->
                <router-link
                  to="/help"
                  class="dropdown-item"
                  @click="companyDropdownOpen = false"
                >
                  <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                  Help / Support
                </router-link>
                <router-link
                  to="/contact"
                  class="dropdown-item"
                  @click="companyDropdownOpen = false"
                >
                  <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  Contact
                </router-link>
              </div>
            </transition>
          </div>
        </div>

        <!-- CTA Buttons Desktop -->
        <div class="hidden md:flex items-center space-x-3">
          <a
            href="#"
            class="px-6 py-2.5 text-gray-700 font-semibold rounded-lg border-2 border-gray-300 hover:border-orange-500 hover:text-orange-600 transition-all duration-300"
          >
            Login
          </a>
          <router-link
            to="/contact"
            class="px-6 py-2.5 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:scale-105"
            style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);"
          >
            Get Started
          </router-link>
        </div>

        <!-- Mobile Menu Button -->
        <button
          @click="mobileMenuOpen = !mobileMenuOpen"
          class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors"
        >
          <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Menu -->
    <transition name="mobile-menu">
      <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-200">
        <div class="px-4 py-4 space-y-3">
          <router-link
            to="/"
            @click="mobileMenuOpen = false"
            class="block px-4 py-2.5 rounded-lg text-gray-700 transition-colors font-medium"
            :class="{ 'text-white': isActive('/') }"
            :style="isActive('/') ? 'background-color: #ffe6cc; color: #ff6b00;' : ''"
          >
            Features
          </router-link>

          <router-link
            to="/pricing"
            @click="mobileMenuOpen = false"
            class="block px-4 py-2.5 rounded-lg text-gray-700 transition-colors font-medium"
            :class="{ 'text-white': isActive('/pricing') }"
            :style="isActive('/pricing') ? 'background-color: #ffe6cc; color: #ff6b00;' : ''"
          >
            Pricing
          </router-link>

          <router-link
            to="/#features"
            @click="mobileMenuOpen = false"
            class="block px-4 py-2.5 rounded-lg text-gray-700 transition-colors font-medium"
            :class="{ 'text-white': isActive('/#features') }"
            :style="isActive('/#features') ? 'background-color: #ffe6cc; color: #ff6b00;' : ''"
          >
            How it Works
          </router-link>

          <!-- Company Submenu in Mobile -->
          <div>
            <button
              @click="mobileCompanyOpen = !mobileCompanyOpen"
              class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-gray-700 transition-colors font-medium"
            >
              <span>Company</span>
              <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': mobileCompanyOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <transition name="mobile-submenu">
              <div v-if="mobileCompanyOpen" class="ml-4 mt-2 space-y-2">
                <!-- About Us menu item commented out -->
                <!-- <router-link
                  to="/about"
                  @click="mobileMenuOpen = false; mobileCompanyOpen = false"
                  class="block px-4 py-2 rounded-lg text-gray-600 text-sm hover:bg-orange-50 transition-colors"
                  :style="isActive('/about') ? 'background-color: #ffe6cc; color: #ff6b00;' : ''"
                >
                  About Us
                </router-link> -->
                <router-link
                  to="/help"
                  @click="mobileMenuOpen = false; mobileCompanyOpen = false"
                  class="block px-4 py-2 rounded-lg text-gray-600 text-sm hover:bg-orange-50 transition-colors"
                  :style="isActive('/help') ? 'background-color: #ffe6cc; color: #ff6b00;' : ''"
                >
                  Help / Support
                </router-link>
                <router-link
                  to="/contact"
                  @click="mobileMenuOpen = false; mobileCompanyOpen = false"
                  class="block px-4 py-2 rounded-lg text-gray-600 text-sm hover:bg-orange-50 transition-colors"
                  :style="isActive('/contact') ? 'background-color: #ffe6cc; color: #ff6b00;' : ''"
                >
                  Contact
                </router-link>
              </div>
            </transition>
          </div>

          <a
            href="#"
            @click="mobileMenuOpen = false"
            class="block px-4 py-2.5 text-gray-700 font-semibold rounded-lg text-center border-2 border-gray-300"
          >
            Login
          </a>

          <router-link
            to="/contact"
            @click="mobileMenuOpen = false"
            class="block px-4 py-2.5 text-white font-semibold rounded-lg text-center shadow-md"
            style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);"
          >
            Get Started
          </router-link>
        </div>
      </div>
    </transition>
  </nav>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const mobileMenuOpen = ref(false);
const companyDropdownOpen = ref(false);
const mobileCompanyOpen = ref(false);

const isActive = (path) => {
  if (path.startsWith('/#')) {
    return route.path === '/' && route.hash === path.substring(1);
  }
  return route.path === path;
};

const isCompanyActive = computed(() => {
  return ['/about', '/help', '/contact'].includes(route.path);
});
</script>

<style scoped>
.nav-link {
  @apply text-gray-700 font-medium transition-colors duration-200 relative;
}

.nav-link:hover {
  color: #ff6b00;
}

.nav-link.active {
  color: #ff6b00;
}

.nav-link.active::after {
  content: '';
  position: absolute;
  bottom: -4px;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(to right, #ff6b00, #ff8c33);
  border-radius: 2px;
}

.mobile-menu-enter-active,
.mobile-menu-leave-active {
  transition: all 0.3s ease;
}

.mobile-menu-enter-from,
.mobile-menu-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.dropdown-item {
  @apply flex items-center px-4 py-3 text-gray-700 hover:bg-orange-50 transition-colors;
}

.dropdown-item:hover {
  color: #ff6b00;
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.mobile-submenu-enter-active,
.mobile-submenu-leave-active {
  transition: all 0.3s ease;
}

.mobile-submenu-enter-from,
.mobile-submenu-leave-to {
  opacity: 0;
  max-height: 0;
}
</style>
