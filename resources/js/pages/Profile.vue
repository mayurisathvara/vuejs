<template>
  <div class="page-container">
    <div class="page-header">
      <div class="header-content">
        <div class="header-title">
          <h3 class="page-title">Profile</h3>
        </div>
      </div>
    </div>

    <div class="card card-round">
      <div class="card-body">
        <div class="row">
          <div class="col-lg-8">
            <div class="profile-info">
              <!-- Name -->
              <div class="form-group mb-3">
                <label class="form-label">Full Name</label>
                <input
                  type="text"
                  class="form-control"
                  :value="user?.name || ''"
                  readonly
                />
              </div>

              <!-- Email -->
              <div class="form-group mb-3">
                <label class="form-label">Email</label>
                <input
                  type="email"
                  class="form-control"
                  :value="user?.email || ''"
                  readonly
                />
              </div>

              <!-- Mobile -->
              <div class="form-group mb-3">
                <label class="form-label">Mobile</label>
                <input
                  type="text"
                  class="form-control"
                  :value="user?.mobile || 'N/A'"
                  readonly
                />
              </div>

              <!-- Department (for Manager and User roles) -->
              <div v-if="isManager || isUser" class="form-group mb-3">
                <label class="form-label">Department</label>
                <input
                  type="text"
                  class="form-control"
                  :value="departmentName"
                  readonly
                />
              </div>

              <!-- Organization (for reference) -->
              <div v-if="isOrganization || isManager || isUser" class="form-group mb-3">
                <label class="form-label">Organization</label>
                <input
                  type="text"
                  class="form-control"
                  :value="organizationName"
                  readonly
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const authStore = useAuthStore()

const user = computed(() => authStore.user)
const userRole = computed(() => user.value?.role || 'user')

const isOrganization = computed(() => userRole.value === 'organization')
const isManager = computed(() => userRole.value === 'manager')
const isUser = computed(() => userRole.value === 'user')

const departmentName = computed(() => {
  return user.value?.department?.name || user.value?.department_name || 'N/A'
})

const organizationName = computed(() => {
  return user.value?.organization?.name || 'N/A'
})

onMounted(() => {
  // User data is already loaded from auth store
})
</script>

<style scoped>
.profile-info {
  max-width: 600px;
}

.form-control[readonly] {
  background-color: #f8f9fa;
  cursor: not-allowed;
}
</style>
