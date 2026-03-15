<template>
  <nav v-if="totalPages > 1" aria-label="Page navigation" class="pagination-wrapper">
    <ul class="pagination-modern">
      <li class="pagination-item" :class="{ disabled: currentPage === 1 }">
        <button
          class="pagination-btn pagination-nav"
          :disabled="currentPage === 1"
          @click="$emit('page-change', 1)"
          title="First Page"
        >
          <i class="fas fa-angle-double-left"></i>
        </button>
      </li>
      
      <li class="pagination-item" :class="{ disabled: currentPage === 1 }">
        <button
          class="pagination-btn pagination-nav"
          :disabled="currentPage === 1"
          @click="$emit('page-change', currentPage - 1)"
          title="Previous Page"
        >
          <i class="fas fa-chevron-left"></i>
        </button>
      </li>

      <li
        v-for="page in visiblePages"
        :key="page"
        class="pagination-item"
        :class="{ active: page === currentPage }"
      >
        <button
          class="pagination-btn pagination-number"
          :class="{ active: page === currentPage }"
          @click="$emit('page-change', page)"
        >
          {{ page }}
        </button>
      </li>

      <li class="pagination-item" :class="{ disabled: currentPage === totalPages }">
        <button
          class="pagination-btn pagination-nav"
          :disabled="currentPage === totalPages"
          @click="$emit('page-change', currentPage + 1)"
          title="Next Page"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
      </li>
      
      <li class="pagination-item" :class="{ disabled: currentPage === totalPages }">
        <button
          class="pagination-btn pagination-nav"
          :disabled="currentPage === totalPages"
          @click="$emit('page-change', totalPages)"
          title="Last Page"
        >
          <i class="fas fa-angle-double-right"></i>
        </button>
      </li>
    </ul>
  </nav>
</template>

<style scoped>
.pagination-wrapper {
  display: flex;
  justify-content: center;
  margin: 0;
}

.pagination-modern {
  display: flex;
  list-style: none;
  padding: 0;
  margin: 0;
  gap: 4px;
  align-items: center;
}

.pagination-item {
  display: flex;
}

.pagination-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 36px;
  height: 36px;
  padding: 0 8px;
  border: 1px solid #e3e6f0;
  background-color: #fff;
  color: #5a5c69;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  border-radius: 6px;
  outline: none;
}

.pagination-btn:hover:not(:disabled):not(.active) {
  background-color: #f8f9fc;
  border-color: #d1d3e2;
  color: #4e73df;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

.pagination-btn:active:not(:disabled) {
  transform: translateY(0);
}

.pagination-btn:disabled {
  cursor: not-allowed;
  opacity: 0.4;
  background-color: #f8f9fc;
  color: #858796;
}

.pagination-btn.active {
  background-color: #4e73df;
  border-color: #4e73df;
  color: #fff;
  font-weight: 600;
  cursor: default;
  box-shadow: 0 2px 6px rgba(78, 115, 223, 0.3);
}

.pagination-nav {
  min-width: 36px;
  font-size: 12px;
}

.pagination-number {
  min-width: 36px;
}

/* Disabled state for list items */
.pagination-item.disabled .pagination-btn {
  pointer-events: none;
}

/* Focus state for accessibility */
.pagination-btn:focus-visible {
  box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.2);
  border-color: #4e73df;
}
</style>

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
