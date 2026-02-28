<template>
  <div class="form-group">
    <label v-if="label" :for="id" class="form-label">
      {{ label }}
      <span v-if="required" class="text-danger">*</span>
    </label>
    <div class="input-wrapper" :class="{ 'has-toggle': isPasswordWithToggle }">
      <input
        :id="id"
        :type="inputType"
        :value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :autocomplete="autocomplete"
        :class="inputClasses"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      />
      <button
        v-if="isPasswordWithToggle"
        type="button"
        class="password-toggle"
        :aria-label="showPassword ? 'Hide password' : 'Show password'"
        @click="showPassword = !showPassword"
      >
        <i :class="showPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
      </button>
    </div>
    <div v-if="error" class="invalid-feedback d-block">
      {{ Array.isArray(error) ? error[0] : error }}
    </div>
    <div v-if="help" class="form-text">
      {{ help }}
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  help: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  autocomplete: {
    type: String,
    default: 'on'
  },
  showToggle: {
    type: Boolean,
    default: false
  }
})

defineEmits(['update:modelValue', 'blur', 'focus'])

const id = computed(() => `input-${Math.random().toString(36).substr(2, 9)}`)
const showPassword = ref(false)

const isPasswordWithToggle = computed(() => props.type === 'password' && props.showToggle)
const inputType = computed(() => {
  if (isPasswordWithToggle.value) {
    return showPassword.value ? 'text' : 'password'
  }

  return props.type
})

const inputClasses = computed(() => {
  const classes = ['form-control']
  
  if (props.size === 'sm') classes.push('form-control-sm')
  if (props.size === 'lg') classes.push('form-control-lg')
  if (props.error) classes.push('is-invalid')
  
  return classes.join(' ')
})
</script>

<style scoped>
.input-wrapper {
  position: relative;
}

.input-wrapper.has-toggle .form-control {
  padding-right: 2.5rem;
}

.password-toggle {
  position: absolute;
  right: 0.8rem;
  top: 50%;
  transform: translateY(-50%);
  border: 0;
  background: transparent;
  color: #6c757d;
  padding: 0;
  line-height: 1;
}
</style>
