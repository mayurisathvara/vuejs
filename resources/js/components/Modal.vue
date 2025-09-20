<template>
  <div
    v-if="show"
    class="modal fade show d-block"
    tabindex="-1"
    role="dialog"
    :style="{ backgroundColor: 'rgba(0,0,0,0.5)' }"
    @click.self="close"
  >
    <div class="modal-dialog" :class="modalClasses" role="document">
      <div class="modal-content">
        <div v-if="title" class="modal-header">
          <h5 class="modal-title">{{ title }}</h5>
          <button
            type="button"
            class="btn-close"
            aria-label="Close"
            @click="close"
          ></button>
        </div>
        <div class="modal-body">
          <slot></slot>
        </div>
        <div v-if="showFooter" class="modal-footer">
          <slot name="footer">
            <Button variant="secondary" @click="close">Cancel</Button>
            <Button variant="primary" @click="confirm">Confirm</Button>
          </slot>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Global modal button styling for consistent theme */
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
