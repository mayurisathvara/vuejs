<template>
  <div class="table-responsive">
    <table class="table" :class="tableClasses">
      <thead v-if="headers.length" class="thead-light">
        <tr>
          <th
            v-for="(header, index) in headers"
            :key="index"
            :class="header.class"
            :style="header.style"
          >
            {{ header.label }}
          </th>
          <th v-if="actions" class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td :colspan="headers.length + (actions ? 1 : 0)" class="text-center py-4">
            <div class="spinner-border" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </td>
        </tr>
        <tr v-else-if="!data.length">
          <td :colspan="headers.length + (actions ? 1 : 0)" class="text-center py-4 text-muted">
            No data available
          </td>
        </tr>
        <tr v-else v-for="(row, rowIndex) in data" :key="rowIndex">
          <td
            v-for="(header, colIndex) in headers"
            :key="colIndex"
            :class="header.class"
          >
            <slot
              :name="`cell-${header.key}`"
              :row="row"
              :value="getNestedValue(row, header.key)"
              :index="rowIndex"
            >
              {{ getNestedValue(row, header.key) }}
            </slot>
          </td>
          <td v-if="actions" class="text-end">
            <slot name="actions" :row="row" :index="rowIndex">
              <div class="action-buttons">
                <button
                  v-if="actions.edit"
                  class="action-btn edit-btn"
                  @click="$emit('edit', row, rowIndex)"
                >
                  <i class="fas fa-edit"></i>
                </button>
                <button
                  v-if="actions.delete"
                  class="action-btn delete-btn"
                  @click="$emit('delete', row, rowIndex)"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </slot>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
/* Table cell padding optimization */
.table {
  margin-bottom: 0;
  font-size: 14px;
}

.table td {
  padding: 10px 12px !important;
  vertical-align: middle;
  border-top: 1px solid #f8f9fc;
  font-size: 14px;
}

.table th {
  padding: 12px 12px !important;
  vertical-align: middle;
  font-weight: 600;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #5a5c69;
  border-bottom: 2px solid #e3e6f0;
  background-color: #f8f9fc;
}

/* Reduce padding for action column specifically */
.table td:last-child {
  padding: 10px 16px 10px 8px !important;
}

/* Hover effect for table rows */
.table tbody tr:hover {
  background-color: #f8f9fc;
}

/* Compact row height */
.table tbody tr {
  height: 60px;
}

.action-buttons {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  align-items: center;
}

.action-btn {
  width: 24px;
  height: 24px;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 16px;
  background: transparent;
  position: relative;
  padding: 0;
  margin: 0;
}

.action-btn:hover {
  transform: scale(1.1);
}

.action-btn:active {
  transform: scale(0.95);
}

.edit-btn {
  color: #6c757d;
}

.edit-btn:hover {
  color: #4e73df;
}

.delete-btn {
  color: #6c757d;
}

.delete-btn:hover {
  color: #e74a3b;
}


/* Responsive adjustments */
@media (max-width: 768px) {
  .action-buttons {
    gap: 6px;
  }
  
  .action-btn {
    width: 22px;
    height: 22px;
    font-size: 14px;
  }
}
</style>

<script setup>
import { computed } from 'vue'
import Button from './Button.vue'

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  },
  headers: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  striped: {
    type: Boolean,
    default: false
  },
  hover: {
    type: Boolean,
    default: false
  },
  bordered: {
    type: Boolean,
    default: false
  },
  actions: {
    type: [Boolean, Object],
    default: false
  }
})

defineEmits(['edit', 'delete'])

const tableClasses = computed(() => {
  const classes = []
  
  if (props.striped) classes.push('table-striped')
  if (props.hover) classes.push('table-hover')
  if (props.bordered) classes.push('table-bordered')
  
  return classes.join(' ')
})

const getNestedValue = (obj, path) => {
  return path.split('.').reduce((current, key) => current?.[key], obj) || ''
}
</script>
