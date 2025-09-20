<template>
  <div class="page-inner">
    <div class="page-header">
      <div class="d-flex align-items-left">
        <h4 class="page-title">Profile</h4>
        <ul class="breadcrumbs">
          <li class="nav-home">
            <a href="#">
              <i class="fas fa-home"></i>
            </a>
          </li>
          <li class="separator">
            <i class="fas fa-chevron-right"></i>
          </li>
          <li class="nav-item">
            <a href="#">Profile</a>
          </li>
        </ul>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <div class="card-title">Edit Profile</div>
          </div>
          <div class="card-body">
            <form @submit.prevent="updateProfile">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="name">Full Name</label>
                    <input
                      type="text"
                      class="form-control"
                      id="name"
                      v-model="form.name"
                      required
                    />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="email">Email</label>
                    <input
                      type="email"
                      class="form-control"
                      id="email"
                      v-model="form.email"
                      required
                    />
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="role">Role</label>
                    <select class="form-control" id="role" v-model="form.role">
                      <option value="admin">Admin</option>
                      <option value="manager">Manager</option>
                      <option value="user">User</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="organization_id">Organization ID</label>
                    <input
                      type="number"
                      class="form-control"
                      id="organization_id"
                      v-model="form.organization_id"
                    />
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="profile">Profile Description</label>
                <textarea
                  class="form-control"
                  id="profile"
                  rows="4"
                  v-model="form.profile"
                  placeholder="Tell us about yourself..."
                ></textarea>
              </div>

              <div class="form-group">
                <button type="submit" class="btn btn-primary" :disabled="loading">
                  <i class="fas fa-save" v-if="!loading"></i>
                  <i class="fas fa-spinner fa-spin" v-if="loading"></i>
                  {{ loading ? 'Updating...' : 'Update Profile' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const authStore = useAuthStore()
const router = useRouter()

const loading = ref(false)
const form = ref({
  name: '',
  email: '',
  role: '',
  organization_id: '',
  profile: ''
})

const user = computed(() => authStore.user)

const loadUserData = () => {
  if (user.value) {
    form.value = {
      name: user.value.name || '',
      email: user.value.email || '',
      role: user.value.role || 'user',
      organization_id: user.value.organization_id || '',
      profile: user.value.profile || ''
    }
  }
}

const updateProfile = async () => {
  loading.value = true
  try {
    const response = await api.put('/api/user/profile', form.value)
    authStore.setUser(response.data.user)
    // Show success message
    alert('Profile updated successfully!')
  } catch (error) {
    console.error('Error updating profile:', error)
    alert('Error updating profile. Please try again.')
  } finally {
    loading.value = false
  }
}



onMounted(() => {
  loadUserData()
})
</script>

<style scoped>


</style>
