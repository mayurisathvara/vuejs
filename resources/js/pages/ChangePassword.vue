<template>
  <div class="page-inner">
    <div class="page-header">
      <div class="d-flex align-items-left">
        <h4 class="page-title">Change Password</h4>
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
          <li class="separator">
            <i class="fas fa-chevron-right"></i>
          </li>
          <li class="nav-item">
            <a href="#">Change Password</a>
          </li>
        </ul>
      </div>
    </div>

    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <i class="fas fa-lock"></i>
              Change Password
            </div>
            <div class="card-category">Update your account password</div>
          </div>
          <div class="card-body">
            <form @submit.prevent="changePassword">
              <div class="form-group">
                <label for="current_password">Current Password</label>
                <div class="password-input-wrapper">
                  <input
                    :type="showCurrentPassword ? 'text' : 'password'"
                    class="form-control password-input"
                    id="current_password"
                    v-model="form.current_password"
                    required
                    placeholder="Enter your current password"
                  />
                  <button
                    type="button"
                    class="password-toggle-btn"
                    @click="showCurrentPassword = !showCurrentPassword"
                  >
                    <i :class="showCurrentPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                  </button>
                </div>
              </div>

              <div class="form-group">
                <label for="new_password">New Password</label>
                <div class="password-input-wrapper">
                  <input
                    :type="showNewPassword ? 'text' : 'password'"
                    class="form-control password-input"
                    id="new_password"
                    v-model="form.new_password"
                    required
                    placeholder="Enter your new password"
                    @input="validatePassword"
                  />
                  <button
                    type="button"
                    class="password-toggle-btn"
                    @click="showNewPassword = !showNewPassword"
                  >
                    <i :class="showNewPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                  </button>
                </div>
                <div class="password-strength mt-2" v-if="form.new_password">
                  <div class="progress" style="height: 5px;">
                    <div
                      class="progress-bar"
                      :class="passwordStrengthClass"
                      :style="{ width: passwordStrength + '%' }"
                    ></div>
                  </div>
                  <small class="text-muted">{{ passwordStrengthText }}</small>
                </div>
              </div>

              <div class="form-group">
                <label for="new_password_confirmation">Confirm New Password</label>
                <div class="password-input-wrapper">
                  <input
                    :type="showConfirmPassword ? 'text' : 'password'"
                    class="form-control password-input"
                    id="new_password_confirmation"
                    v-model="form.new_password_confirmation"
                    required
                    placeholder="Confirm your new password"
                    @input="validatePasswordMatch"
                  />
                  <button
                    type="button"
                    class="password-toggle-btn"
                    @click="showConfirmPassword = !showConfirmPassword"
                  >
                    <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                  </button>
                </div>
                <div class="password-match mt-1" v-if="form.new_password_confirmation">
                  <small :class="passwordMatch ? 'text-success' : 'text-danger'">
                    <i :class="passwordMatch ? 'fas fa-check' : 'fas fa-times'"></i>
                    {{ passwordMatch ? 'Passwords match' : 'Passwords do not match' }}
                  </small>
                </div>
              </div>

              <div class="password-requirements">
                <h6>Password Requirements:</h6>
                <ul class="list-unstyled">
                  <li :class="{ 'text-success': hasMinLength }">
                    <i :class="hasMinLength ? 'fas fa-check' : 'fas fa-times'"></i>
                    At least 8 characters
                  </li>
                  <li :class="{ 'text-success': hasUppercase }">
                    <i :class="hasUppercase ? 'fas fa-check' : 'fas fa-times'"></i>
                    At least one uppercase letter
                  </li>
                  <li :class="{ 'text-success': hasLowercase }">
                    <i :class="hasLowercase ? 'fas fa-check' : 'fas fa-times'"></i>
                    At least one lowercase letter
                  </li>
                  <li :class="{ 'text-success': hasNumber }">
                    <i :class="hasNumber ? 'fas fa-check' : 'fas fa-times'"></i>
                    At least one number
                  </li>
                  <li :class="{ 'text-success': hasSpecialChar }">
                    <i :class="hasSpecialChar ? 'fas fa-check' : 'fas fa-times'"></i>
                    At least one special character
                  </li>
                </ul>
              </div>

              <div class="form-group">
                <button
                  type="submit"
                  class="btn btn-primary btn-block"
                  :disabled="loading || !isFormValid"
                >
                  <i class="fas fa-key" v-if="!loading"></i>
                  <i class="fas fa-spinner fa-spin" v-if="loading"></i>
                  {{ loading ? 'Changing Password...' : 'Change Password' }}
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
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

const loading = ref(false)
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const form = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

// Password validation
const hasMinLength = computed(() => form.value.new_password.length >= 8)
const hasUppercase = computed(() => /[A-Z]/.test(form.value.new_password))
const hasLowercase = computed(() => /[a-z]/.test(form.value.new_password))
const hasNumber = computed(() => /\d/.test(form.value.new_password))
const hasSpecialChar = computed(() => /[!@#$%^&*(),.?":{}|<>]/.test(form.value.new_password))

const passwordMatch = ref(false)
const passwordStrength = ref(0)
const passwordStrengthClass = ref('')
const passwordStrengthText = ref('')

const isFormValid = computed(() => {
  return (
    form.value.current_password &&
    form.value.new_password &&
    form.value.new_password_confirmation &&
    hasMinLength.value &&
    hasUppercase.value &&
    hasLowercase.value &&
    hasNumber.value &&
    hasSpecialChar.value &&
    passwordMatch.value
  )
})

const validatePassword = () => {
  const password = form.value.new_password
  let strength = 0
  
  if (hasMinLength.value) strength += 20
  if (hasUppercase.value) strength += 20
  if (hasLowercase.value) strength += 20
  if (hasNumber.value) strength += 20
  if (hasSpecialChar.value) strength += 20
  
  passwordStrength.value = strength
  
  if (strength < 40) {
    passwordStrengthClass.value = 'bg-danger'
    passwordStrengthText.value = 'Weak password'
  } else if (strength < 80) {
    passwordStrengthClass.value = 'bg-warning'
    passwordStrengthText.value = 'Medium strength'
  } else {
    passwordStrengthClass.value = 'bg-success'
    passwordStrengthText.value = 'Strong password'
  }
  
  // Also validate password match when new password changes
  validatePasswordMatch()
}

const validatePasswordMatch = () => {
  passwordMatch.value = form.value.new_password === form.value.new_password_confirmation
}

// Watch for changes in password fields to update validation
watch([() => form.value.new_password, () => form.value.new_password_confirmation], () => {
  validatePasswordMatch()
})

const changePassword = async () => {
  if (!isFormValid.value) {
    alert('Please fill in all fields correctly')
    return
  }

  console.log('Attempting to change password with data:', form.value)
  loading.value = true
  
  try {
    const response = await api.post('/api/user/change-password', form.value)
    console.log('Password change response:', response.data)
    alert('Password changed successfully!')
    // Reset form
    form.value = {
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    }
    passwordMatch.value = false
    passwordStrength.value = 0
    passwordStrengthText.value = ''
  } catch (error) {
    console.error('Error changing password:', error)
    console.error('Error response:', error.response)
    if (error.response?.data?.message) {
      alert(error.response.data.message)
    } else if (error.response?.data?.errors) {
      const errorMessages = Object.values(error.response.data.errors).flat().join('\n')
      alert('Validation errors:\n' + errorMessages)
    } else {
      alert('Error changing password. Please try again.')
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.password-requirements {
  background-color: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.password-requirements h6 {
  color: #2c3e50;
  margin-bottom: 10px;
}

.password-requirements li {
  margin-bottom: 5px;
  font-size: 0.9rem;
}

.password-requirements .text-success {
  color: #28a745 !important;
}

.password-requirements .text-danger {
  color: #dc3545 !important;
}

.password-strength .progress {
  background-color: #e9ecef;
}

.password-match {
  font-size: 0.875rem;
}


.btn-block {
  width: 100%;
}

/* Password input wrapper styling */
.password-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.password-input {
  padding-right: 45px !important;
  border: 1px solid #e3e6f0;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s ease;
  background-color: #fff;
}

.password-input:focus {
  border-color: #4e73df;
  box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
  outline: none;
}

.password-toggle-btn {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #6c757d;
  cursor: pointer;
  padding: 0;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s ease;
  z-index: 10;
}

.password-toggle-btn:hover {
  color: #4e73df;
}

.password-toggle-btn:focus {
  outline: none;
  color: #4e73df;
}

/* Legacy input group styling (for backward compatibility) */
.input-group-append .btn {
  border-left: 0;
}

.form-control:focus + .input-group-append .btn {
  border-color: #007bff;
}
</style>
