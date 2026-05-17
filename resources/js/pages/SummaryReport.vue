<template>
  <div class="page-container">
    <div class="page-header">
      <div class="header-content d-flex align-items-center justify-content-between flex-wrap">
        <div class="header-title">
          <h3 class="page-title">SIM-wise Summary Report</h3>
        </div>

        <div class="header-actions d-flex align-items-center gap-2">
          <button
            type="button"
            class="btn btn-white border shadow-sm btn-sm"
            @click="toggleFilters"
            :aria-expanded="filtersOpen ? 'true' : 'false'"
          >
            <i class="fas fa-filter text-orange me-2"></i>
            Filters
          </button>
        </div>
      </div>
    </div>

    <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>
      {{ errorMessage }}
      <button type="button" class="btn-close" @click="errorMessage = ''" aria-label="Close"></button>
    </div>

    <div v-if="queueExportMsg" class="queue-export-banner">
      <div class="queue-export-banner__inner">
        <span class="queue-export-banner__icon"><i class="fas fa-chart-bar"></i></span>
        <span class="queue-export-banner__text">{{ queueExportMsg }}</span>
        <router-link to="/generated-reports" class="queue-export-banner__link">View reports &rarr;</router-link>
      </div>
      <button type="button" class="queue-export-banner__close" @click="queueExportMsg = ''" aria-label="Dismiss">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div v-show="filtersOpen" class="filters-card" @mousedown="onFiltersMousedown">
      <div class="filters-topbar">
        <div class="filters-heading">
          <div class="filters-heading-icon">
            <i class="fas fa-sliders-h"></i>
          </div>
          <div>
            <h4 class="filters-title">Filter Summary</h4>
            <p class="filters-subtitle">Narrow results by date, activity, and assignment for SIM-wise totals.</p>
          </div>
        </div>

        <div class="filters-actions">
          <button
            type="button"
            class="btn btn-light border shadow-sm btn-sm filter-action-btn"
            @click="toggleFilters"
          >
            <i class="fas fa-eye-slash me-2"></i>
            Hide Filters
          </button>
          <button
            type="button"
            class="btn btn-white border shadow-sm btn-sm filter-action-btn"
            :disabled="loading || optionsLoading"
            @click="resetAll"
          >
            <i class="fas fa-undo me-2"></i>
            Reset
          </button>
          <button
            type="button"
            class="btn btn-primary btn-sm filter-action-btn filter-action-primary"
            :disabled="loading || optionsLoading"
            @click="applySearch"
          >
            <span
              v-if="loading"
              class="spinner-border spinner-border-sm me-2"
              role="status"
              aria-hidden="true"
            ></span>
            <i v-else class="fas fa-search me-2"></i>
            {{ loading ? 'Searching...' : 'Search' }}
          </button>
        </div>
      </div>

      <div class="row g-3 filter-grid">
        <div class="col-12 col-lg-4">
          <div class="filter-field">
            <label class="filter-label">Date Range</label>
            <div class="search-box-modern filter-control-shell">
              <i class="fas fa-calendar-alt search-icon-modern"></i>
              <input
                ref="dateRangeEl"
                v-model="filters.date_range"
                type="text"
                class="search-input-modern"
                placeholder="dd/mm/yyyy - dd/mm/yyyy"
                readonly
              />
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-4">
          <div class="filter-field">
            <label class="filter-label">Call Type</label>
            <div class="select-wrapper filter-control-shell">
              <i class="fas fa-exchange-alt filter-icon"></i>
              <select v-model="filters.call_type" class="form-select select-modern">
                <option value="">All Call Types</option>
                <option value="inbound">Inbound</option>
                <option value="outbound">Outbound</option>
              </select>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-4">
          <div class="filter-field">
            <label class="filter-label">Status</label>
            <div class="select-wrapper filter-control-shell">
              <i class="fas fa-phone-volume filter-icon"></i>
              <select v-model="filters.call_status" class="form-select select-modern">
                <option value="">All Status</option>
                <option value="Answered">Answered</option>
                <option value="No Answer">No Answer</option>
                <option value="Missed">Missed</option>
              </select>
            </div>
          </div>
        </div>

        <div v-if="isAdmin" class="col-12 col-lg-4">
          <div class="filter-field">
            <label class="filter-label">Organization</label>
            <div class="select-wrapper filter-control-shell">
              <i class="fas fa-building filter-icon"></i>
              <select v-model="filters.organization_id" class="form-select select-modern" @change="onOrganizationChange">
                <option value="">All Organizations</option>
                <option v-for="org in options.organizations" :key="org.id" :value="org.id">
                  {{ org.name }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <div v-if="!isUser" class="col-12 col-lg-4">
          <div class="filter-field">
            <label class="filter-label">Department</label>
            <div class="select-wrapper filter-control-shell">
              <i :class="optionsLoading ? 'fas fa-spinner fa-spin filter-icon' : 'fas fa-sitemap filter-icon'"></i>
              <select v-model="filters.department_id" class="form-select select-modern" :disabled="optionsLoading || (isAdmin && !filters.organization_id)" @change="onDepartmentChange">
                <option value="">All Departments</option>
                <option v-if="optionsLoading" value="" disabled>Loading...</option>
                <option v-for="dept in options.departments" :key="dept.id" :value="dept.id">
                  {{ dept.name }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <div v-if="!isUser" class="col-12 col-lg-4">
          <div class="filter-field">
            <label class="filter-label">Users</label>
            <div class="select-wrapper filter-control-shell">
              <i class="fas fa-user filter-icon"></i>
              <select v-model="filters.user_id" class="form-select select-modern" :disabled="optionsLoading || (!filters.department_id && options.users.length === 0)" @change="onUserChange">
                <option value="">All Users</option>
                <option v-if="optionsLoading" value="" disabled>Loading...</option>
                <option v-for="u in options.users" :key="u.id" :value="u.id">
                  {{ u.name }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-4">
          <div class="filter-field">
            <label class="filter-label">SIM Cards</label>
            <div class="multiselect-wrapper filter-control-shell">
              <i class="fas fa-sim-card filter-icon"></i>
              <div
                ref="simTriggerEl"
                class="multiselect-trigger"
                tabindex="0"
                role="button"
                aria-haspopup="listbox"
                :aria-expanded="simDropdownOpen"
                @click="toggleSimDropdown"
                @keydown.enter.prevent="toggleSimDropdown"
                @keydown.space.prevent="toggleSimDropdown"
                @keydown.escape="simDropdownOpen = false"
              >
                <span v-if="filters.sim_mobile.length === 0" class="placeholder">All SIM Cards</span>
                <span v-else class="selected-count">{{ filters.sim_mobile.length }} selected</span>
                <i class="fas fa-chevron-down dropdown-arrow"></i>
              </div>
              <div v-if="simDropdownOpen" ref="simMenuEl" class="multiselect-menu" role="listbox">
                <div class="multiselect-header">SIM Cards</div>
                <div v-if="options.sims.length === 0" class="multiselect-empty">
                  <i class="fas fa-info-circle me-2"></i>
                  <span v-if="optionsLoading">Loading SIMs...</span>
                  <span v-else>No SIM cards available</span>
                </div>
                <template v-else>
                  <div
                    class="multiselect-option"
                    role="option"
                    tabindex="0"
                    @click="selectAllSims"
                    @keydown.enter.prevent="selectAllSims"
                    @keydown.space.prevent="selectAllSims"
                  >
                    <input type="checkbox" :checked="isAllSimsSelected" readonly tabindex="-1" />
                    <label>All SIM Cards</label>
                  </div>
                  <div
                    v-for="sim in options.sims"
                    :key="simKey(sim)"
                    class="multiselect-option"
                    role="option"
                    tabindex="0"
                    @click="toggleSim(sim.mobile)"
                    @keydown.enter.prevent="toggleSim(sim.mobile)"
                    @keydown.space.prevent="toggleSim(sim.mobile)"
                  >
                    <input
                      type="checkbox"
                      :checked="filters.sim_mobile.includes(sim.mobile)"
                      readonly
                      tabindex="-1"
                    />
                    <label>{{ sim.mobile }}<span v-if="sim.name"> - {{ sim.name }}</span></label>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card card-round">
      <div class="card-body p-0">
        <div class="datatable-header">
          <div class="datatable-entries">
            <label>Show</label>
            <select v-model="perPage" class="form-select" :disabled="loading" @change="onPerPageChange">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
            <label>entries</label>
          </div>
          <div class="datatable-actions">
            <button type="button" class="btn btn-light border btn-sm" :disabled="loading" @click="refresh" title="Refresh" aria-label="Refresh data">
              <i :class="loading ? 'fas fa-sync-alt fa-spin' : 'fas fa-sync-alt'"></i>
            </button>
            <button
              class="action-icon-btn"
              :disabled="exporting"
              @click="exportFile('excel')"
              title="Queue Excel Export"
              aria-label="Queue Excel Export"
            >
              <i class="fas" :class="exporting ? 'fa-spinner fa-spin' : 'fa-file-excel'"></i>
            </button>
            <button
              class="action-icon-btn"
              :disabled="exporting"
              @click="exportFile('csv')"
              title="Queue CSV Export"
              aria-label="Queue CSV Export"
            >
              <i class="fas" :class="exporting ? 'fa-spinner fa-spin' : 'fa-file-alt'"></i>
            </button>
          </div>
        </div>
        <div class="table-responsive-wrapper">
          <div class="summary-report-table">
            <table class="table">
              <thead>
                <tr>
                  <th rowspan="2" class="text-center sticky-col sticky-col-index">#</th>
                  <th rowspan="2" class="sticky-col sticky-col-phone phone-col">Phone Number</th>
                  <th rowspan="2" class="sticky-col sticky-col-name name-col">Name</th>
                  <th rowspan="2" class="sticky-col sticky-col-team team-col">Team</th>
                  <th colspan="5" class="section-header text-center section-header-uppercase">Summary</th>
                  <th colspan="5" class="section-header-inbound text-center section-header-uppercase">Inbound</th>
                  <th colspan="4" class="section-header-outbound text-center section-header-uppercase">Outbound</th>
                </tr>
                <tr>
                  <!-- Summary columns -->
                  <th class="text-center col-summary">Total Calls</th>
                  <th class="text-center col-summary">Total Unique Calls</th>
                  <th class="text-center col-summary">Total Duration</th>
                  <th class="text-center col-summary">Answered Calls</th>
                  <th class="text-center col-summary">Answered Avg. Duration</th>
                  <!-- Inbound columns -->
                  <th class="text-center col-inbound">Total Calls</th>
                  <th class="text-center col-inbound">Total Duration</th>
                  <th class="text-center col-inbound">Answered Calls</th>
                  <th class="text-center col-inbound">Answered Avg. Duration</th>
                  <th class="text-center col-inbound">Missed Calls</th>
                  <!-- Outbound columns -->
                  <th class="text-center col-outbound">Total Calls</th>
                  <th class="text-center col-outbound">Total Duration</th>
                  <th class="text-center col-outbound">Answered Calls</th>
                  <th class="text-center col-outbound">Answered Avg. Duration</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading && summaryData.length === 0">
                  <td colspan="18" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2 text-muted">Loading summary data...</div>
                  </td>
                </tr>
                <tr v-else-if="summaryData.length === 0">
                  <td colspan="18" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block" style="opacity: 0.3;"></i>
                    No data available
                  </td>
                </tr>
                <template v-else>
                  <tr v-for="(row, index) in summaryData" :key="index">
                    <td class="text-center sticky-col sticky-col-index">{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
                    <td class="sticky-col sticky-col-phone">
                      <div class="phone-number">{{ row.phone_number }}</div>
                    </td>
                    <td class="sticky-col sticky-col-name">
                      <div class="user-name">{{ row.name || '-' }}</div>
                    </td>
                    <td class="sticky-col sticky-col-team">
                      <div class="team-name">{{ row.department || '-' }}</div>
                    </td>
                    <!-- Summary -->
                    <td class="text-center cell-summary">{{ row.total_calls }}</td>
                    <td class="text-center cell-summary">{{ row.unique_clients }}</td>
                    <td class="text-center cell-summary">{{ row.total_duration_formatted }}</td>
                    <td class="text-center cell-summary">{{ row.answered_calls }}</td>
                    <td class="text-center cell-summary">{{ row.answered_avg_duration_formatted }}</td>
                    <!-- Inbound -->
                    <td class="text-center cell-inbound">{{ row.inbound_total_calls }}</td>
                    <td class="text-center cell-inbound">{{ row.inbound_total_duration_formatted }}</td>
                    <td class="text-center cell-inbound">{{ row.inbound_answered }}</td>
                    <td class="text-center cell-inbound">{{ row.inbound_answered_avg_duration_formatted }}</td>
                    <td class="text-center cell-inbound">{{ row.inbound_missed }}</td>
                    <!-- Outbound -->
                    <td class="text-center cell-outbound">{{ row.outbound_total_calls }}</td>
                    <td class="text-center cell-outbound">{{ row.outbound_total_duration_formatted }}</td>
                    <td class="text-center cell-outbound">{{ row.outbound_answered }}</td>
                    <td class="text-center cell-outbound">{{ row.outbound_answered_avg_duration_formatted }}</td>
                  </tr>
                  <tr v-if="loading" class="loading-overlay-row">
                    <td colspan="18">
                      <div class="table-loading-overlay">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <span class="ms-2">Updating...</span>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div v-if="pagination.total > 0" class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        <span>
          Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to
          {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}
          of {{ pagination.total }} entries
        </span>
      </div>
      <Pagination
        :current-page="pagination.current_page"
        :total-pages="pagination.last_page"
        @page-change="fetchSummary"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import flatpickr from 'flatpickr'
import 'flatpickr/dist/flatpickr.css'

import Pagination from '@/components/Pagination.vue'

const authStore = useAuthStore()

const isAdmin = computed(() => authStore.userRole === 'admin')
const isUser = computed(() => authStore.userRole === 'user')

const loading = ref(false)
const exporting = ref(false)
const optionsLoading = ref(false)
const errorMessage = ref('')

let optionsAbort = null
let summaryAbort = null
let debounceTimer = null

const options = ref({
  organizations: [],
  departments: [],
  users: [],
  sims: []
})

const filters = ref({
  date_range: '',
  call_type: '',
  call_status: '',
  organization_id: '',
  department_id: '',
  user_id: '',
  sim_mobile: []
})

const perPage = ref('10')

const filtersOpen = ref(false)

// Multiselect dropdown state
const simDropdownOpen = ref(false)
const simTriggerEl = ref(null)
const simMenuEl = ref(null)

const isAllSimsSelected = computed(() => {
  return filters.value.sim_mobile.length === options.value.sims.length && options.value.sims.length > 0
})

const toggleSim = (mobile) => {
  const index = filters.value.sim_mobile.indexOf(mobile)
  if (index > -1) {
    filters.value.sim_mobile.splice(index, 1)
  } else {
    filters.value.sim_mobile.push(mobile)
  }
}

const selectAllSims = () => {
  if (isAllSimsSelected.value) {
    filters.value.sim_mobile = []
  } else {
    filters.value.sim_mobile = options.value.sims.map(sim => sim.mobile)
  }
}

const toggleSimDropdown = () => {
  simDropdownOpen.value = !simDropdownOpen.value
}

const toggleFilters = () => {
  filtersOpen.value = !filtersOpen.value
  if (!filtersOpen.value) {
    simDropdownOpen.value = false
  }
}

const isClickInside = (targetEl, el) => {
  if (!el) return false
  return el.contains(targetEl)
}

const onFiltersMousedown = (e) => {
  const targetEl = e?.target
  if (!(targetEl instanceof Element)) return

  const inSim = isClickInside(targetEl, simTriggerEl.value) || isClickInside(targetEl, simMenuEl.value)
  
  if (!inSim) simDropdownOpen.value = false
}

const closeDropdowns = (e) => {
  const targetEl = e?.target
  if (!(targetEl instanceof Element)) return

  const inSim = isClickInside(targetEl, simTriggerEl.value) || isClickInside(targetEl, simMenuEl.value)
  const inFilters = targetEl.closest('.filters-card')
  
  if (!inSim && !inFilters) {
    simDropdownOpen.value = false
  }
}

const handleEscapeKey = (e) => {
  if (e.key === 'Escape') {
    simDropdownOpen.value = false
  }
}

const appliedFilters = ref(null)

const dateRangeEl = ref(null)

let dateRangePicker = null

const formatTodayDmy = () => {
  const d = new Date()
  const dd = String(d.getDate()).padStart(2, '0')
  const mm = String(d.getMonth() + 1).padStart(2, '0')
  const yyyy = String(d.getFullYear())
  return `${dd}/${mm}/${yyyy}`
}

const validateDateRange = () => {
  const range = String(filters.value.date_range || '').trim()
  if (!range) return true

  const parts = range
    .split(/\s+(?:-|to)\s+/i)
    .map((p) => p.trim())
    .filter(Boolean)
  
  if (parts.length !== 2) return true

  const startDmy = parts[0]
  const endDmy = parts[1]

  const startYmd = toYmd(startDmy)
  const endYmd = toYmd(endDmy)

  if (!startYmd || !endYmd) return true

  const startDate = new Date(startYmd)
  const endDate = new Date(endYmd)

  if (startDate > endDate) {
    errorMessage.value = 'Start date cannot be after end date. Please select a valid date range.'
    return false
  }

  return true
}

const applyDefaultTodayRange = () => {
  const today = formatTodayDmy()
  filters.value.date_range = `${today} - ${today}`
}

const forceDateRangeToToday = async () => {
  const todayStr = formatTodayDmy()
  const display = `${todayStr} - ${todayStr}`

  filters.value.date_range = display

  await nextTick()

  try {
    const today = new Date()
    dateRangePicker?.setDate?.([today, today], true)
    dateRangePicker?.close?.()
  } catch {
    // ignore
  }

  try {
    if (dateRangeEl.value) {
      dateRangeEl.value.value = display
      dateRangeEl.value.dispatchEvent(new Event('input', { bubbles: true }))
      dateRangeEl.value.dispatchEvent(new Event('change', { bubbles: true }))
    }
  } catch {
    // ignore
  }
}

const summaryData = ref([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0
})

const toYmd = (dmy) => {
  if (!dmy) return null
  const parts = String(dmy).trim().split('/')
  if (parts.length !== 3) return null
  const [dd, mm, yyyy] = parts
  if (!dd || !mm || !yyyy) return null
  return `${yyyy}-${mm.padStart(2, '0')}-${dd.padStart(2, '0')}`
}

const getStartEndDateTime = (src) => {
  const range = String(src.date_range || '').trim()
  if (!range) return { start_date_time: undefined, end_date_time: undefined }

  const parts = range
    .split(/\s+(?:-|to)\s+/i)
    .map((p) => p.trim())
    .filter(Boolean)
  const startDmy = parts[0]
  const endDmy = parts[1] || parts[0]

  const startYmd = toYmd(startDmy)
  const endYmd = toYmd(endDmy)

  if (!startYmd || !endYmd) return { start_date_time: undefined, end_date_time: undefined }

  return {
    start_date_time: `${startYmd} 00:00`,
    end_date_time: `${endYmd} 23:59`
  }
}

const normalizeFilterParams = (src) => ({
  ...getStartEndDateTime(src),
  call_type: src.call_type || undefined,
  call_status: src.call_status || undefined,
  organization_id: src.organization_id || undefined,
  department_id: src.department_id || undefined,
  user_id: src.user_id || undefined,
  sim_mobile: src.sim_mobile || undefined
})

const buildParams = (forExport = false, page = 1, useApplied = true) => {
  const src = useApplied && appliedFilters.value ? appliedFilters.value : filters.value
  const params = normalizeFilterParams(src)

  if (!forExport) {
    params.per_page = Number(perPage.value)
    params.page = page
  }

  return params
}

const fetchOptions = async () => {
  if (optionsAbort) {
    optionsAbort.abort()
  }
  optionsAbort = new AbortController()

  optionsLoading.value = true
  errorMessage.value = ''

  const params = {
    organization_id: filters.value.organization_id || undefined,
    department_id: filters.value.department_id || undefined,
    user_id: filters.value.user_id || undefined
  }

  try {
    const resp = await api.get('/summary-reports/options', { params, signal: optionsAbort.signal })
    options.value = resp.data
  } catch (e) {
    if (e?.name !== 'CanceledError' && e?.code !== 'ERR_CANCELED') {
      console.error('Error fetching filter options:', e)
      errorMessage.value = 'Failed to load filter options. Please try again.'
    }
  } finally {
    optionsLoading.value = false
  }
}

const fetchSummary = async (page = 1) => {
  if (summaryAbort) {
    summaryAbort.abort()
  }
  summaryAbort = new AbortController()

  loading.value = true
  errorMessage.value = ''
  try {
    const resp = await api.get('/summary-reports', { params: buildParams(false, page, true), signal: summaryAbort.signal })
    summaryData.value = resp.data.data || []
    pagination.value = {
      current_page: resp.data.current_page,
      last_page: resp.data.last_page,
      per_page: resp.data.per_page,
      total: resp.data.total
    }
  } catch (e) {
    if (e?.name !== 'CanceledError' && e?.code !== 'ERR_CANCELED') {
      console.error('Error fetching summary data:', e)
      errorMessage.value = 'Failed to load summary report data. Please try again.'
      summaryData.value = []
    }
  } finally {
    loading.value = false
  }
}

const applySearch = async () => {
  if (!validateDateRange()) {
    return
  }
  
  appliedFilters.value = { ...filters.value }
  try {
    await fetchSummary(1)
  } finally {
    filtersOpen.value = false
    simDropdownOpen.value = false
  }
}

const refresh = async () => {
  await fetchOptions()
  if (!appliedFilters.value) {
    appliedFilters.value = { ...filters.value }
  }
  await fetchSummary(1)
}

const onPerPageChange = async () => {
  if (!appliedFilters.value) {
    appliedFilters.value = { ...filters.value }
  }
  await fetchSummary(1)
}

const resetAll = async () => {
  applyDefaultTodayRange()
  filters.value.call_type = ''
  filters.value.call_status = ''
  filters.value.department_id = ''
  filters.value.user_id = ''
  filters.value.sim_mobile = []

  if (isAdmin.value) {
    filters.value.organization_id = ''
  }

  await forceDateRangeToToday()

  simDropdownOpen.value = false

  appliedFilters.value = { ...filters.value }
  await fetchOptions()
  await fetchSummary(1)
}

const onOrganizationChange = async () => {
  filters.value.department_id = ''
  filters.value.user_id = ''
  filters.value.sim_mobile = []

  options.value.departments = []
  options.value.users = []
  options.value.sims = []

  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }
  
  debounceTimer = setTimeout(async () => {
    await fetchOptions()
  }, 300)
}

const onDepartmentChange = async () => {
  filters.value.user_id = ''
  filters.value.sim_mobile = []

  options.value.users = []
  options.value.sims = []

  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }
  
  debounceTimer = setTimeout(async () => {
    await fetchOptions()
  }, 300)
}

const onUserChange = async () => {
  filters.value.sim_mobile = []

  options.value.sims = []

  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }
  
  debounceTimer = setTimeout(async () => {
    await fetchOptions()
  }, 300)
}

const simKey = (sim) => {
  return `${sim.mobile}-${sim.id || ''}`
}

const queueExportMsg = ref('')

const exportFile = async (format) => {
  exporting.value = true
  queueExportMsg.value = ''
  errorMessage.value = ''
  try {
    const filters = buildParams(true, 1, true)
    const resp = await api.post('/summary-reports/queue-export', { format, filters })
    queueExportMsg.value = resp.data?.message ?? "Report queued. You'll be notified when it's ready."
  } catch {
    errorMessage.value = 'Failed to queue report. Please try again.'
  } finally {
    exporting.value = false
  }
}

onMounted(async () => {
  applyDefaultTodayRange()

  if (dateRangeEl.value) {
    const today = new Date()
    dateRangePicker = flatpickr(dateRangeEl.value, {
      mode: 'range',
      dateFormat: 'd/m/Y',
      allowInput: false,
      rangeSeparator: ' - ',
      maxDate: 'today',
      defaultDate: [today, today],
      onReady: (_selectedDates, dateStr) => {
        if (dateStr) filters.value.date_range = dateStr
      },
      onChange: (_selectedDates, dateStr) => {
        filters.value.date_range = dateStr
      }
    })
  }

  await fetchOptions()
  appliedFilters.value = { ...filters.value }
  await fetchSummary(1)
  
  window.addEventListener('mousedown', closeDropdowns, true)
  window.addEventListener('keydown', handleEscapeKey)
})

onBeforeUnmount(() => {
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }
  if (optionsAbort) {
    optionsAbort.abort()
  }
  if (summaryAbort) {
    summaryAbort.abort()
  }
  dateRangePicker?.destroy?.()
  window.removeEventListener('mousedown', closeDropdowns, true)
  window.removeEventListener('keydown', handleEscapeKey)
})
</script>

<style scoped>
.text-orange {
  color: #ff6a00 !important;
}

.alert {
  margin-bottom: 24px;
  border-radius: 10px;
  padding: 14px 18px;
  border: 1px solid;
}

.alert-danger {
  background-color: #fef2f2;
  border-color: #fecaca;
  color: #991b1b;
}

.page-header {
  margin-bottom: 24px;
  padding: 0;
}

.page-header .header-content {
  display: flex;
  align-items: center;
  justify-content: space-between !important;
  flex-wrap: wrap;
  gap: 16px !important;
}

.page-header .header-title {
  flex: 1;
  min-width: 200px;
}

.page-title {
  font-size: 24px;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
  letter-spacing: -0.02em;
  line-height: 1.2;
}

.header-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

/* Filters Card */
.filters-card {
  margin-bottom: 24px;
  padding: 18px;
  border: 1px solid #dbe6f3;
  border-radius: 20px;
  background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
  box-shadow: 0 14px 40px rgba(15, 23, 42, 0.06);
}

.filters-topbar {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 16px;
  padding-bottom: 14px;
  border-bottom: 1px solid #e7eef7;
}

.filters-heading {
  display: flex;
  align-items: flex-start;
  gap: 14px;
}

.filters-heading-icon {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #2563eb;
  background: linear-gradient(135deg, #e9f2ff 0%, #dbeafe 100%);
  box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.08);
}

.filters-title {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  color: #0f172a;
}

.filters-subtitle {
  margin: 4px 0 0;
  font-size: 12px;
  color: #64748b;
}

.filters-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  flex-wrap: wrap;
}

.filter-action-btn {
  min-height: 38px;
  padding: 0 14px;
  border-radius: 12px;
  font-weight: 600;
}

.filter-action-primary {
  min-width: 118px;
  box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
}

.filter-action-btn .spinner-border {
  width: 0.9rem;
  height: 0.9rem;
}

.filter-grid {
  row-gap: 14px;
}

.filter-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.filter-label {
  margin: 0;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  letter-spacing: 0.02em;
}

.filter-control-shell {
  position: relative;
}

/* Search Box */
.search-box-modern {
  position: relative;
  display: flex;
  align-items: center;
  border-radius: 12px;
  background: #ffffff;
  border: 1px solid #d7e2f0;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.search-icon-modern {
  position: absolute;
  left: 14px;
  color: #64748b;
  font-size: 14px;
  pointer-events: none;
  z-index: 2;
  transition: color 0.2s ease;
}

.search-input-modern {
  width: 100%;
  min-height: 42px;
  padding: 9px 36px 9px 38px;
  font-size: 13px;
  border: none;
  border-radius: 12px;
  background-color: transparent;
  color: #0f172a;
  box-shadow: none;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.search-input-modern:focus {
  outline: none;
}

.search-box-modern:hover,
.select-wrapper:hover,
.multiselect-trigger:hover {
  border-color: #adc6ea;
}

.search-box-modern:focus-within,
.select-wrapper:focus-within,
.multiselect-wrapper:focus-within .multiselect-trigger {
  border-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
}

.search-box-modern:focus-within .search-icon-modern {
  color: #3b82f6;
}

.search-input-modern::placeholder {
  color: #a0aec0;
  font-size: 13px;
}

/* Select Wrapper */
.select-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  border-radius: 12px;
  background: #ffffff;
  border: 1px solid #d7e2f0;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.filter-icon {
  position: absolute;
  left: 14px;
  color: #64748b;
  font-size: 13px;
  pointer-events: none;
  z-index: 2;
}

.select-modern {
  width: 100%;
  min-height: 42px;
  padding: 9px 36px 9px 38px;
  font-size: 13px;
  border: none;
  border-radius: 12px;
  background-color: transparent;
  color: #0f172a;
  cursor: pointer;
  box-shadow: none;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236c757d' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 12px 10px;
}

.select-modern:focus {
  outline: none;
}

.select-modern:disabled {
  background-color: transparent;
  cursor: not-allowed;
  opacity: 0.6;
}

small.text-muted {
  display: block;
  margin-top: 6px;
  font-size: 12px;
  color: #718096;
  font-weight: 500;
}

/* Card Round */
.card-round {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid #e9ecef;
  background: #fff;
  margin-bottom: 24px;
}

/* Loading overlay for table */
.loading-overlay-row {
  position: absolute;
  width: 100%;
  pointer-events: none;
}

.table-loading-overlay {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: rgba(255, 255, 255, 0.95);
  padding: 12px 24px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  font-size: 14px;
  color: #4a5568;
  font-weight: 500;
}

.table-responsive-wrapper {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  border-radius: 0;
}

.summary-report-table {
  width: 100%;
  overflow-x: auto;
}

.summary-report-table table {
  width: 100%;
  min-width: 2000px;
  border-collapse: collapse;
  margin: 0;
  font-size: 13px;
  background: #fff;
}

.summary-report-table table thead th {
  background: #f8f9fc;
  color: #1a202c;
  font-weight: 600;
  border: 1px solid #e3e6f0;
  padding: 6px 8px !important;
  text-align: left;
  vertical-align: middle;
  white-space: nowrap;
  position: sticky;
  top: 0;
  z-index: 10;
  text-transform: none;
  font-size: 12px;
  letter-spacing: 0.01em;
}

.summary-report-table table thead tr:first-child th {
  border-bottom: none;
}

.summary-report-table table thead .section-header {
  background: #f6fff8;
  font-weight: 700;
}

.summary-report-table table thead .section-header-inbound {
  background: #f6fbff;
  font-weight: 700;
}

.summary-report-table table thead .section-header-outbound {
  background: #fefbf5;
  font-weight: 700;
}

.summary-report-table table thead .section-header-missed {
  background: #fff4f6;
  font-weight: 700;
}

/* Column headers with section colors */
.summary-report-table table thead th.col-summary {
  background: #f6fff8;
}

.summary-report-table table thead th.col-inbound {
  background: #f6fbff;
}

.summary-report-table table thead th.col-outbound {
  background: #fefbf5;
}

/* Data cells with section colors */
.summary-report-table table tbody td.cell-summary {
  background: #f6fff8;
}

.summary-report-table table tbody td.cell-inbound {
  background: #f6fbff;
}

.summary-report-table table tbody td.cell-outbound {
  background: #fefbf5;
}

.summary-report-table table tbody td.cell-missed {
  background: #fff4f6;
}

.summary-report-table table tbody td {
  border: 1px solid #e3e6f0;
  padding: 6px 8px !important;
  vertical-align: middle;
  background: #fff;
  font-size: 13px;
  color: #2d3748;
  transition: background-color 0.15s ease;
}

.summary-report-table table tbody tr:hover td.cell-summary {
  background: #eaf9ed;
}

.summary-report-table table tbody tr:hover td.cell-inbound {
  background: #e8f4ff;
}

.summary-report-table table tbody tr:hover td.cell-outbound {
  background: #fdf6ea;
}

.summary-report-table table tbody tr:hover td.cell-missed {
  background: #ffe8ed;
}

.summary-report-table table tbody tr:hover td:not(.cell-summary):not(.cell-inbound):not(.cell-outbound):not(.cell-missed) {
  background: #f8f9fa;
}

/* Sticky columns */
.summary-report-table .sticky-col {
  position: sticky;
  z-index: 20;
  background: #fff;
  box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
}

.summary-report-table .sticky-col-index {
  left: 0;
  min-width: 50px;
  box-shadow: 2px 0 4px rgba(0, 0, 0, 0.08);
}

.summary-report-table .sticky-col-phone {
  left: 50px;
  min-width: 140px;
}

.summary-report-table .sticky-col-name {
  left: 190px;
  min-width: 150px;
}

.summary-report-table .sticky-col-team {
  left: 340px;
  min-width: 150px;
}

.summary-report-table .phone-col {
  min-width: 140px;
}

.summary-report-table .name-col {
  min-width: 150px;
}

.summary-report-table .team-col {
  min-width: 150px;
}

.section-header-uppercase {
  text-transform: uppercase;
}

.summary-report-table thead .sticky-col {
  z-index: 30;
}

/* Maintain background colors for sticky cells */
.summary-report-table tbody tr:hover .sticky-col-index {
  background: #f8f9fa;
}

.summary-report-table tbody tr:hover .sticky-col-phone {
  background: #fff;
}

.summary-report-table tbody tr:hover .sticky-col-name {
  background: #fff;
}

.summary-report-table tbody tr:hover .sticky-col-team {
  background: #fff;
}

.datatable-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #e9ecef;
  background: #fafbfc;
  flex-wrap: wrap;
  gap: 12px;
}

.datatable-entries {
  display: flex;
  align-items: center;
  gap: 8px;
}

.datatable-entries label {
  font-size: 14px;
  color: #4a5568;
  margin: 0;
  font-weight: 500;
}

.datatable-entries .form-select {
  width: 70px;
  padding: 6px 10px;
  font-size: 14px;
  border: 1px solid #e3e6f0;
  border-radius: 6px;
  height: 34px;
  background-color: #fff;
  color: #2d3748;
  font-weight: 500;
  transition: all 0.2s ease;
}

.datatable-entries .form-select:focus {
  border-color: #4e73df;
  box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
  outline: none;
}

.datatable-actions {
  display: flex;
  gap: 8px;
}

.datatable-actions .btn {
  height: 34px;
  padding: 6px 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.datatable-actions .btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Multiselect styling */
.multiselect-wrapper {
  position: relative;
}

.multiselect-trigger {
  position: relative;
  display: flex;
  align-items: center;
  min-height: 42px;
  padding: 9px 36px 9px 38px;
  border: 1px solid #d7e2f0;
  border-radius: 12px;
  background: #fff;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 13px;
  line-height: 1.5;
}

.multiselect-trigger:focus {
  outline: none;
}

.multiselect-trigger .placeholder {
  color: #6c757d;
  background: transparent;
  display: inline;
}

.multiselect-trigger .selected-count {
  color: #212529;
  font-weight: 500;
  background: transparent;
  display: inline;
}

.dropdown-arrow {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 12px;
  color: #64748b;
  pointer-events: none;
}

.multiselect-menu {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 0;
  max-height: 300px;
  overflow-y: auto;
  background: #fff;
  border: 1px solid #d7e2f0;
  border-radius: 14px;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
  z-index: 1050;
}

.multiselect-header {
  padding: 10px 12px;
  font-size: 13px;
  font-weight: 600;
  color: #334155;
  background: #f8fbff;
  border-bottom: 1px solid #e2e8f0;
  position: sticky;
  top: 0;
  z-index: 1;
}

.multiselect-empty {
  padding: 20px 12px;
  text-align: center;
  color: #6c757d;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.multiselect-empty i {
  color: #4e73df;
}

.multiselect-option {
  display: flex;
  align-items: center;
  padding: 10px 12px;
  cursor: pointer;
  transition: background-color 0.15s;
  user-select: none;
}

.multiselect-option:hover {
  background-color: #f8fbff;
}

.multiselect-option input[type="checkbox"] {
  margin-right: 10px;
  cursor: pointer;
  width: 16px;
  height: 16px;
  flex-shrink: 0;
  pointer-events: none;
}

.multiselect-option label {
  flex: 1;
  cursor: pointer;
  margin: 0;
  font-size: 14px;
  color: #212529;
  user-select: none;
  pointer-events: none;
}

.multiselect-wrapper .filter-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
  font-size: 13px;
  z-index: 1;
  pointer-events: none;
}

@media (max-width: 768px) {
  .page-header .header-content {
    flex-direction: column;
    align-items: flex-start !important;
    gap: 12px !important;
  }

  .header-actions {
    width: 100%;
  }

  .header-actions button {
    flex: 1;
  }

  .datatable-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .filters-topbar {
    flex-direction: column;
    align-items: stretch;
  }

  .filters-actions {
    justify-content: stretch;
  }

  .filter-action-btn {
    flex: 1 1 160px;
  }
  
  .datatable-entries,
  .datatable-actions {
    width: 100%;
    justify-content: space-between;
  }

  .filters-card {
    padding: 16px;
  }

  .page-title {
    font-size: 20px;
  }

  .summary-report-table table {
    font-size: 12px;
  }

  .summary-report-table table thead th {
    font-size: 11px;
    padding: 5px 6px !important;
  }

  .summary-report-table table tbody td {
    font-size: 12px;
    padding: 5px 6px !important;
  }

  .phone-number {
    font-size: 13px;
  }

  .user-name {
    font-size: 12px;
  }

  .team-name {
    font-size: 12px;
  }

  .phone-meta {
    font-size: 11px;
  }

  .sticky-col-phone {
    min-width: 120px !important;
  }

  .sticky-col-name {
    min-width: 130px !important;
    left: 170px !important;
  }

  .sticky-col-team {
    min-width: 130px !important;
    left: 300px !important;
  }

  .alert {
    font-size: 13px;
    padding: 12px 14px;
  }

  .d-flex.justify-content-between.align-items-center.mt-4 {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start !important;
  }
}

@media (max-width: 576px) {
  .page-title {
    font-size: 18px;
  }

  .filters-card {
    padding: 14px;
    border-radius: 16px;
  }

  .filters-heading {
    gap: 10px;
  }

  .filters-heading-icon {
    width: 36px;
    height: 36px;
    border-radius: 12px;
  }

  .filters-title {
    font-size: 18px;
  }

  .filters-subtitle {
    font-size: 12px;
  }

  .btn-sm {
    font-size: 12px;
    padding: 6px 12px;
  }

  .search-input-modern,
  .select-modern {
    font-size: 12px;
    min-height: 40px;
    padding: 9px 34px 9px 36px;
  }

  .multiselect-trigger {
    font-size: 12px;
    min-height: 40px;
    padding: 9px 34px 9px 36px;
  }

  .action-icon-btn {
    width: 32px;
    height: 32px;
  }

  .datatable-entries .form-select {
    width: 60px;
    font-size: 12px;
  }

  .datatable-entries label {
    font-size: 12px;
  }
}

.action-icon-btn {
  position: relative;
  width: 34px;
  height: 34px;
  border: 1px solid #e3e6f0;
  background: #fff;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #4a5568;
}

.action-icon-btn:hover:not(:disabled) {
  background: #f7fafc;
  border-color: #cbd5e0;
  color: #2d3748;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.action-icon-btn:focus {
  outline: 2px solid #4e73df;
  outline-offset: 2px;
}

.action-icon-btn:focus-visible {
  outline: 2px solid #4e73df;
  outline-offset: 2px;
  box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
}

.action-icon-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: #f7fafc;
}

.action-icon-btn i {
  font-size: 14px;
}

.action-icon-btn i.fa-file-excel {
  color: #10b981;
}

.action-icon-btn i.fa-file-alt {
  color: #3b82f6;
}

/* Queue export banner */
.queue-export-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 16px;
  padding: 12px 16px;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-left: 4px solid #22c55e;
  border-radius: 8px;
  font-size: 13.5px;
}
.queue-export-banner__inner {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
  min-width: 0;
  flex-wrap: wrap;
}
.queue-export-banner__icon {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: #dcfce7;
  color: #16a34a;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  flex-shrink: 0;
}
.queue-export-banner__text {
  color: #166534;
  font-weight: 500;
}
.queue-export-banner__link {
  color: #16a34a;
  font-weight: 600;
  text-decoration: none;
  white-space: nowrap;
}
.queue-export-banner__link:hover { text-decoration: underline; }
.queue-export-banner__close {
  background: none;
  border: none;
  color: #86efac;
  font-size: 13px;
  cursor: pointer;
  padding: 2px 4px;
  line-height: 1;
  flex-shrink: 0;
}
.queue-export-banner__close:hover { color: #16a34a; }

.action-icon-btn:hover:not(:disabled) i.fa-file-excel {
  color: #059669;
}

.action-icon-btn:hover:not(:disabled) i.fa-file-alt {
  color: #2563eb;
}

.action-icon-btn .btn-spinner {
  position: absolute;
  width: 14px;
  height: 14px;
  border: 2px solid #e3e6f0;
  border-top-color: #4a5568;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Focus styles for accessibility */
.multiselect-trigger:focus {
  outline: 2px solid #4e73df;
  outline-offset: 2px;
}

.multiselect-option:focus {
  background-color: #e7f3ff;
  outline: none;
}

.multiselect-option:focus-visible {
  background-color: #e7f3ff;
  box-shadow: inset 0 0 0 2px #4e73df;
}

/* Phone info styling */
.phone-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 4px 0;
}

.phone-number {
  font-weight: 600;
  color: #1a202c;
  font-size: 14px;
}

.phone-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  font-size: 12px;
  max-width: 100%;
  overflow: hidden;
}

.user-name {
  color: #1a202c;
  font-weight: 500;
  font-size: 13px;
}

.team-name {
  color: #4a5568;
  font-weight: 400;
  font-size: 13px;
}

.department-name {
  color: #718096;
  font-weight: 400;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  max-width: 150px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.department-name::before {
  content: '\f1ad';
  font-family: 'Font Awesome 5 Free';
  font-weight: 900;
  font-size: 11px;
  opacity: 0.7;
}
</style>
