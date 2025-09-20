<template>
  <div class="auth-wrapper">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
          <div class="card card-round">
            <div class="card-body">
              <div class="text-center mb-4">
                <div class="logo-placeholder mb-3" style="height: 40px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; border-radius: 8px;">
                  <span class="fw-bold text-primary">Vue Admin</span>
                </div>
                <h4 class="fw-bold">{{ title }}</h4>
                <p class="text-muted">{{ subtitle }}</p>
              </div>
              
              <router-view />
              
              <div class="text-center mt-4">
                <p class="text-muted">
                  <router-link
                    v-if="$route.name === 'Login'"
                    to="/register"
                    class="text-decoration-none"
                  >
                    Don't have an account? Sign up
                  </router-link>
                  <router-link
                    v-else
                    to="/login"
                    class="text-decoration-none"
                  >
                    Already have an account? Sign in
                  </router-link>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const title = computed(() => {
  return route.name === 'Login' ? 'Welcome Back' : 'Create Account'
})

const subtitle = computed(() => {
  return route.name === 'Login' 
    ? 'Sign in to your account to continue' 
    : 'Sign up for a new account to get started'
})
</script>

<style scoped>
.auth-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem 0;
}

.card {
  border: none;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  border-radius: 15px;
}

.card-body {
  padding: 2.5rem;
}
</style>
