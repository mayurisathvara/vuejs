<template>
  <div class="table-responsive">
    <table class="table" :class="tableClasses">
      <thead v-if="headers.length" class="thead-light">
        <tr>
          <th v-if="checkboxes" style="width: 50px;">
            <input
              type="checkbox"
              class="form-check-input"
              :checked="isAllSelected"
              @change="toggleSelectAll"
            />
          </th>
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
          <td :colspan="(checkboxes ? 1 : 0) + headers.length + (actions ? 1 : 0)" class="text-center py-4">
            <div class="spinner-border" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </td>
        </tr>
        <tr v-else-if="!data.length">
          <td :colspan="(checkboxes ? 1 : 0) + headers.length + (actions ? 1 : 0)" class="text-center py-4 text-muted">
            No data available
          </td>
        </tr>
        <tr v-else v-for="(row, rowIndex) in data" :key="rowIndex">
          <td v-if="checkboxes">
            <input
              type="checkbox"
              class="form-check-input"
              :checked="isRowSelected(row)"
              @change="toggleRowSelection(row)"
            />
          </td>
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
                  v-if="typeof actions === 'object' && actions.edit"
                  class="action-btn edit-btn"
                  @click="$emit('edit', row, rowIndex)"
                >
                  <i class="fas fa-edit"></i>
                </button>
                <button
                  v-if="typeof actions === 'object' && actions.delete"
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
  padding: 6px 16px !important;
  vertical-align: middle;
  border-top: 1px solid #f8f9fc;
  font-size: 14px;
}

.table th {
  padding: 8px 16px !important;
  vertical-align: middle;
  font-weight: 600;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #5a5c69;
  border-bottom: 2px solid #e3e6f0;
  background-color: #f8f9fc;
}

/* Checkbox styling for better visibility */
.form-check-input {
  width: 18px;
  height: 18px;
  border: 2px solid #d1d3e2;
  border-radius: 4px;
  background-color: #f8f9fc;
  cursor: pointer;
  transition: all 0.2s ease;
  margin: 0;
}

.form-check-input:hover {
  border-color: #4e73df;
  background-color: #e7f0ff;
}

.form-check-input:checked {
  background-color: #4e73df;
  border-color: #4e73df;
}

.form-check-input:focus {
  box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
  border-color: #4e73df;
}

/* Add extra padding to first and last columns */
.table td:first-child,
.table th:first-child {
  padding-left: 18px !important;
}

.table td:last-child,
.table th:last-child {
  padding-right: 18px !important;
}

/* Hover effect for table rows */
.table tbody tr:hover {
  background-color: #f8f9fc;
}

/* Reduced row height */
.table tbody tr {
  height: 44px;
}

.action-buttons {
  display: flex;
  gap: 6px;
  justify-content: flex-end;
  align-items: center;
}

.action-btn {
  width: 30px;
  height: 30px;
  border: 1px solid #e3e6f0;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
  background: #fff;
  position: relative;
  padding: 0;
  margin: 0;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.action-btn:active {
  transform: translateY(0);
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.assign-sim-btn {
  color: #6f42c1 !important;
  width: auto;
  padding: 0 10px;
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
}

.assign-sim-btn:hover {
  background: #6f42c1;
  border-color: #6f42c1;
  color: #fff !important;
}

.edit-btn {
  color: #4e73df !important;
}

.edit-btn:hover {
  background: #4e73df;
  border-color: #4e73df;
  color: #fff !important;
}

.delete-btn {
  color: #e74a3b !important;
}

.delete-btn:hover {
  background: #e74a3b;
  border-color: #e74a3b;
  color: #fff !important;
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
  },
  checkboxes: {
    type: Boolean,
    default: false
  },
  selectedRows: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['edit', 'delete', 'selection-change'])

const tableClasses = computed(() => {
  const classes = []
  
  if (props.striped) classes.push('table-striped')
  if (props.hover) classes.push('table-hover')
  if (props.bordered) classes.push('table-bordered')
  
  return classes.join(' ')
})

const isAllSelected = computed(() => {
  return props.data.length > 0 && props.selectedRows.length === props.data.length
})

const isRowSelected = (row) => {
  return props.selectedRows.some(selected => selected.id === row.id)
}

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    emit('selection-change', [])
  } else {
    emit('selection-change', [...props.data])
  }
}

const toggleRowSelection = (row) => {
  const selected = [...props.selectedRows]
  const index = selected.findIndex(item => item.id === row.id)
  
  if (index > -1) {
    selected.splice(index, 1)
  } else {
    selected.push(row)
  }
  
  emit('selection-change', selected)
}

const getNestedValue = (obj, path) => {
  return path.split('.').reduce((current, key) => current?.[key], obj) || ''
}
</script>
