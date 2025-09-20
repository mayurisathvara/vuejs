<template>
  <nav v-if="totalPages > 1" aria-label="Page navigation">
    <ul class="pagination justify-content-center">
      <li class="page-item" :class="{ disabled: currentPage === 1 }">
        <button
          class="page-link"
          :disabled="currentPage === 1"
          @click="$emit('page-change', 1)"
        >
          <i class="fas fa-angle-double-left"></i>
        </button>
      </li>
      
      <li class="page-item" :class="{ disabled: currentPage === 1 }">
        <button
          class="page-link"
          :disabled="currentPage === 1"
          @click="$emit('page-change', currentPage - 1)"
        >
          <i class="fas fa-angle-left"></i>
        </button>
      </li>

      <li
        v-for="page in visiblePages"
        :key="page"
        class="page-item"
        :class="{ active: page === currentPage }"
      >
        <button
          class="page-link"
          @click="$emit('page-change', page)"
        >
          {{ page }}
        </button>
      </li>

      <li class="page-item" :class="{ disabled: currentPage === totalPages }">
        <button
          class="page-link"
          :disabled="currentPage === totalPages"
          @click="$emit('page-change', currentPage + 1)"
        >
          <i class="fas fa-angle-right"></i>
        </button>
      </li>
      
      <li class="page-item" :class="{ disabled: currentPage === totalPages }">
        <button
          class="page-link"
          :disabled="currentPage === totalPages"
          @click="$emit('page-change', totalPages)"
        >
          <i class="fas fa-angle-double-right"></i>
        </button>
      </li>
    </ul>
  </nav>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentPage: {
    type: Number,
    required: true
  },
  totalPages: {
    type: Number,
    required: true
  },
  maxVisible: {
    type: Number,
    default: 5
  }
})

defineEmits(['page-change'])

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, props.currentPage - Math.floor(props.maxVisible / 2))
  const end = Math.min(props.totalPages, start + props.maxVisible - 1)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})
</script>
