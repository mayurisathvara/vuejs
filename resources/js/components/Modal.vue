<template>
  <div
    v-if="show"
    class="modal fade show d-block modal-backdrop-custom"
    tabindex="-1"
    role="dialog"
    @click.self="close"
  >
    <div class="modal-dialog" :class="modalClasses" role="document">
      <div class="modal-content modal-content-modern">
        <div v-if="title" class="modal-header modal-header-modern">
          <h5 class="modal-title modal-title-modern">{{ title }}</h5>
          <button
            type="button"
            class="btn-close modal-close-modern"
            aria-label="Close"
            @click="close"
          ></button>
        </div>
        <div class="modal-body modal-body-modern">
          <slot></slot>
        </div>
        <div v-if="showFooter" class="modal-footer modal-footer-modern">
          <slot name="footer">
            <Button variant="secondary" class="btn-min" @click="close">Cancel</Button>
            <Button variant="primary" class="btn-min" @click="confirm">Confirm</Button>
          </slot>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Backdrop */
.modal-backdrop-custom {
  background-color: rgba(17, 24, 39, 0.55);
}

/* Container */
.modal-content-modern {
  border: 0;
  border-radius: 14px;
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.24);
  overflow: hidden;
}

/* Header */
.modal-header-modern {
  border: 0;
  padding: 18px 20px 10px;
  position: relative;
  justify-content: center;
}

.modal-title-modern {
  font-weight: 700;
  font-size: 18px;
  color: #111827;
  margin: 0;
  text-align: center;
  width: 100%;
  padding: 0 34px;
}

.modal-close-modern {
  position: absolute;
  right: 16px;
  top: 16px;
}

/* Body */
.modal-body-modern {
  padding: 12px 20px 16px;
}

/* Footer */
.modal-footer-modern {
  border: 0;
  padding: 14px 20px 18px;
  gap: 10px;
}

.btn-min {
  min-width: 110px;
  border-radius: 10px;
  font-weight: 600;
}

/* Button styling for consistent design */
.modal-footer .btn-secondary {
  background-color: #6f42c1 !important;
  border-color: #6f42c1 !important;
  color: white !important;
}

.modal-footer .btn-secondary:hover {
  background-color: #5a32a3 !important;
  border-color: #5a32a3 !important;
  color: white !important;
}

.modal-footer .btn-primary {
  background-color: #4e73df !important;
  border-color: #4e73df !important;
  color: white !important;
}

.modal-footer .btn-primary:hover {
  background-color: #3d5fc7 !important;
  border-color: #3d5fc7 !important;
  color: white !important;
}
</style>

<script setup>
import { computed } from 'vue'
import Button from './Button.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
  },
  showFooter: {
    type: Boolean,
    default: true
  },
  centered: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'confirm'])

const modalClasses = computed(() => {
  const classes = []
  
  if (props.size === 'sm') classes.push('modal-sm')
  if (props.size === 'lg') classes.push('modal-lg')
  if (props.size === 'xl') classes.push('modal-xl')
  if (props.centered) classes.push('modal-dialog-centered')
  
  return classes.join(' ')
})

const close = () => {
  emit('close')
}

const confirm = () => {
  emit('confirm')
}
</script>
