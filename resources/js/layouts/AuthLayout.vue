<template>
  <div class="auth-wrapper">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5 col-xl-4">
          <div class="card card-round">
            <div class="card-body">
              <div class="text-center mb-4 auth-heading">
                <img :src="'/logo/login_icon.png'" alt="Callytics" class="auth-logo mb-3" />
                <p class="text-muted">{{ subtitle }}</p>
              </div>
              
              <router-view />
              
              <div class="text-center mt-4">
                <p class="auth-switch-text mb-0" v-if="$route.name === 'Login'">
                  Don't have an account?
                  <router-link to="/register" class="auth-switch-link text-decoration-none">
                    Sign up
                  </router-link>
                </p>
                <p class="auth-switch-text mb-0" v-else>
                  Already have an account?
                  <router-link to="/login" class="auth-switch-link text-decoration-none">
                    Sign in
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
    ? 'Sign in to continue to your dashboard' 
    : 'Sign up for a new account to get started'
})
</script>

<style scoped>
.auth-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  background:
    radial-gradient(90% 60% at 25% 70%, rgba(239, 226, 208, 0.42) 0%, rgba(239, 226, 208, 0) 70%),
    radial-gradient(70% 45% at 75% 25%, rgba(233, 225, 214, 0.28) 0%, rgba(233, 225, 214, 0) 70%),
    linear-gradient(135deg, #f9f7f6 0%, #f3efec 52%, #f8f5f3 100%);
  padding: 2rem 0;
}

.card {
  border: none;
  box-shadow: 0 25px 60px rgba(27, 22, 30, 0.15);
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.92);
}

.card-body {
  padding: 2.4rem 2.15rem 2rem;
}

.auth-logo {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  object-fit: cover;
  margin-left: auto;
  margin-right: auto;
  box-shadow: 0 10px 24px rgba(255, 138, 0, 0.28);
}

.auth-heading h4 {
  color: #232b37;
  font-size: 2.9rem;
  letter-spacing: -0.02em;
}

.auth-heading p {
  color: #6f7783 !important;
  font-size: 1.07rem;
  margin-top: 0.25rem;
}

:deep(.form-label) {
  color: #5e6673;
  font-weight: 600;
  margin-bottom: 0.45rem;
  font-size: 0.98rem;
}

:deep(.form-control) {
  background: #f3f5f9;
  border: 1px solid #e5e9f1;
  border-radius: 10px;
  height: 54px;
  color: #2c3340;
  font-size: 1rem;
}

:deep(.form-control:focus) {
  border-color: #ff9b2f;
  box-shadow: 0 0 0 0.2rem rgba(255, 153, 0, 0.2);
}

:deep(.btn.btn-primary) {
  border: none !important;
  border-radius: 12px !important;
  background: linear-gradient(90deg, #ff860a 0%, #ffac21 52%, #ffc144 100%) !important;
  box-shadow: 0 7px 18px rgba(255, 155, 35, 0.22) !important;
  font-weight: 700 !important;
  font-size: 1.08rem !important;
  line-height: 1 !important;
  letter-spacing: 0.01em !important;
  color: #ffffff !important;
}

:deep(.btn.btn-primary:hover),
:deep(.btn.btn-primary:focus) {
  background: linear-gradient(90deg, #ff7e03 0%, #ffa61a 52%, #ffba36 100%) !important;
  color: #ffffff !important;
  box-shadow: 0 8px 18px rgba(255, 145, 20, 0.24) !important;
}

:deep(.btn.btn-primary:active) {
  background: linear-gradient(90deg, #f57700 0%, #f9a516 52%, #f7b32b 100%) !important;
  color: #ffffff !important;
}

:deep(.btn.btn-primary:focus-visible) {
  outline: none !important;
  box-shadow: 0 0 0 0.2rem rgba(255, 170, 66, 0.28), 0 6px 14px rgba(255, 146, 24, 0.22) !important;
}

:deep(.btn-lg) {
  min-height: 48px;
  padding: 0.75rem 1rem;
}

:deep(.form-check-input:checked) {
  background-color: #ff9f2d;
  border-color: #ff9f2d;
}

.text-center.mt-4 {
  margin-top: 2rem !important;
}

.auth-switch-text {
  color: #7b8391;
  font-size: 1.08rem;
}

.auth-switch-link {
  color: #ff8f1f;
  font-weight: 700;
  margin-left: 0.2rem;
}

.auth-switch-link:hover {
  color: #ff7f00;
}

@media (max-width: 576px) {
  .card-body {
    padding: 1.8rem 1.4rem;
  }

  .auth-heading h4 {
    font-size: 2.2rem;
  }
}
</style>
