<template>
  <div class="page-container">
    <div class="page-header">
      <div class="header-content d-flex align-items-center gap-2 flex-wrap">
        <div class="header-title">
          <h3 class="page-title">Call Report</h3>
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

    <div v-if="queueExportMsg" class="queue-export-banner">
      <div class="queue-export-banner__inner">
        <span class="queue-export-banner__icon"><i class="fas fa-phone"></i></span>
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
            <h4 class="filters-title">Filter Calls</h4>
            <p class="filters-subtitle">Narrow results by date, time, call activity, and ownership.</p>
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
            :disabled="loading"
            @click="resetAll"
          >
            <i class="fas fa-undo me-2"></i>
            Reset
          </button>
          <button
            type="button"
            class="btn btn-primary btn-sm filter-action-btn filter-action-primary"
            :disabled="loading"
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

        <div class="col-6 col-lg-4">
          <div class="filter-field">
            <label class="filter-label">Start Time</label>
            <div class="timepicker-wrapper">
              <div ref="startTimeTriggerEl" class="search-box-modern filter-control-shell" @click="toggleTimeDropdown('start')">
                <i class="fas fa-clock search-icon-modern"></i>
                <input
                  ref="startTimeEl"
                  v-model="filters.start_time"
                  type="text"
                  class="search-input-modern time-input"
                  placeholder="HH:MM"
                  readonly
                />
              </div>

              <div v-if="timeDropdownOpen === 'start'" ref="startTimeMenuEl" class="timepicker-menu">
                <div class="timepicker-selected">
                  <div class="timepicker-selected-box">{{ startTimeParts.hh }}</div>
                  <div class="timepicker-selected-box">{{ startTimeParts.mm }}</div>
                </div>
                <div class="timepicker-body">
                  <div class="timepicker-col hours">
                    <div
                      v-for="h in timeHours"
                      :key="`h-${h}`"
                      class="timepicker-item"
                      :class="{ 'is-active': h === startTimeParts.hh }"
                      :data-value="h"
                      @click="setTimeHour('start', h)"
                    >
                      {{ h }}
                    </div>
                  </div>
                  <div class="timepicker-col minutes">
                    <div
                      v-for="m in timeMinutes"
                      :key="`m-${m}`"
                      class="timepicker-item"
                      :class="{ 'is-active': m === startTimeParts.mm }"
                      :data-value="m"
                      @click="setTimeMinute('start', m)"
                    >
                      {{ m }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-4">
          <div class="filter-field">
            <label class="filter-label">End Time</label>
            <div class="timepicker-wrapper">
              <div ref="endTimeTriggerEl" class="search-box-modern filter-control-shell" @click="toggleTimeDropdown('end')">
                <i class="fas fa-clock search-icon-modern"></i>
                <input
                  ref="endTimeEl"
                  v-model="filters.end_time"
                  type="text"
                  class="search-input-modern time-input"
                  placeholder="HH:MM"
                  readonly
                />
              </div>

              <div v-if="timeDropdownOpen === 'end'" ref="endTimeMenuEl" class="timepicker-menu">
                <div class="timepicker-selected">
                  <div class="timepicker-selected-box">{{ endTimeParts.hh }}</div>
                  <div class="timepicker-selected-box">{{ endTimeParts.mm }}</div>
                </div>
                <div class="timepicker-body">
                  <div class="timepicker-col hours">
                    <div
                      v-for="h in timeHours"
                      :key="`eh-${h}`"
                      class="timepicker-item"
                      :class="{ 'is-active': h === endTimeParts.hh }"
                      :data-value="h"
                      @click="setTimeHour('end', h)"
                    >
                      {{ h }}
                    </div>
                  </div>
                  <div class="timepicker-col minutes">
                    <div
                      v-for="m in timeMinutes"
                      :key="`em-${m}`"
                      class="timepicker-item"
                      :class="{ 'is-active': m === endTimeParts.mm }"
                      :data-value="m"
                      @click="setTimeMinute('end', m)"
                    >
                      {{ m }}
                    </div>
                  </div>
                </div>
              </div>
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

        <div class="col-12 col-lg-4">
          <div class="filter-field">
            <label class="filter-label">Return Calls</label>
            <div class="select-wrapper filter-control-shell">
              <i class="fas fa-undo filter-icon"></i>
              <select v-model="filters.call_back" class="form-select select-modern">
                <option value="">All Return Calls</option>
                <option value="N">Pending</option>
                <option value="Y">Returned</option>
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
            <label class="filter-label">Team</label>
            <div class="select-wrapper filter-control-shell">
              <i class="fas fa-sitemap filter-icon"></i>
              <select v-model="filters.team_id" class="form-select select-modern" :disabled="optionsLoading || (isAdmin && !filters.organization_id)" @change="onTeamChange">
                <option value="">All Teams</option>
                <option v-if="optionsLoading" value="" disabled>Loading...</option>
                <option v-for="dept in options.teams" :key="dept.id" :value="dept.id">
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
              <select v-model="filters.user_id" class="form-select select-modern" :disabled="optionsLoading || (!filters.team_id && options.users.length === 0)" @change="onUserChange">
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
              <div ref="simTriggerEl" class="multiselect-trigger" @click="toggleSimDropdown">
                <span v-if="filters.sim_mobile.length === 0" class="placeholder">All SIM Cards</span>
                <span v-else class="selected-count">{{ filters.sim_mobile.length }} selected</span>
                <i class="fas fa-chevron-down dropdown-arrow"></i>
              </div>
              <div v-if="simDropdownOpen" ref="simMenuEl" class="multiselect-menu">
                <div class="multiselect-header">SIM Cards</div>
                <div class="multiselect-option" @click="selectAllSims">
                  <input type="checkbox" :checked="isAllSimsSelected" readonly />
                  <label>All SIM Cards</label>
                </div>
                <div
                  v-for="sim in options.sims"
                  :key="simKey(sim)"
                  class="multiselect-option"
                  @click="toggleSim(sim.mobile)"
                >
                  <input
                    type="checkbox"
                    :checked="filters.sim_mobile.includes(sim.mobile)"
                    readonly
                  />
                  <label>{{ sim.mobile }}<span v-if="sim.name"> - {{ sim.name }}</span></label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-4">
          <div class="filter-field">
            <label class="filter-label">Customer Number</label>
            <div class="search-box-modern filter-control-shell">
              <i class="fas fa-hashtag search-icon-modern"></i>
              <input
                v-model="filters.caller_number"
                type="text"
                class="search-input-modern"
                placeholder="Customer number"
              />
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
            <button type="button" class="btn btn-light border btn-sm" :disabled="loading" @click="refresh" title="Refresh">
              <i class="fas fa-sync-alt"></i>
            </button>
            <button class="action-icon-btn" :disabled="exporting" @click="exportFile('excel')" title="Queue Excel Export">
              <i class="fas" :class="exporting ? 'fa-spinner fa-spin' : 'fa-file-excel'"></i>
            </button>
            <button class="action-icon-btn" :disabled="exporting" @click="exportFile('csv')" title="Queue CSV Export">
              <i class="fas" :class="exporting ? 'fa-spinner fa-spin' : 'fa-file-alt'"></i>
            </button>
          </div>
        </div>
        <div class="table-responsive-wrapper">
          <div class="call-reports-table">
            <Table
              :data="logs"
              :headers="headers"
              :loading="loading"
              :actions="false"
            >
          <template #cell-date="{ row }">
            {{ formatDateOnly(row) }}
          </template>
          <template #cell-time="{ row }">
            {{ formatTimeOnly(row) }}
          </template>
          <template #cell-call_type="{ row }">
            {{ (row.call_type || 'N/A').toUpperCase() }}
          </template>
          <template #cell-call_status="{ row }">
            <!-- Outbound + Answered (Blue Icon) -->
            <span v-if="row.call_type === 'outbound' && row.call_status === 'Answered'" class="status-text">
              <i class="fas fa-phone-volume icon-accent"></i> Answered
            </span>
            <!-- Outbound + No Answer (Orange/Yellow Icon) -->
            <span v-else-if="row.call_type === 'outbound' && row.call_status === 'No Answer'" class="status-text">
              <i class="fas fa-phone-slash text-warning"></i> No Answer
            </span>
            <!-- Inbound + Answered (Green Icon) -->
            <span v-else-if="row.call_type === 'inbound' && row.call_status === 'Answered'" class="status-text">
              <i class="fas fa-phone-volume text-success"></i> Answered
            </span>
            <!-- Inbound + Missed (Red Icon) -->
            <span v-else-if="row.call_type === 'inbound' && row.call_status === 'Missed'" class="status-text">
              <i class="fas fa-phone-slash text-danger"></i> Missed
            </span>
            <!-- Fallback -->
            <span v-else class="text-muted">{{ row.call_status || 'N/A' }}</span>
          </template>
          <template #cell-team="{ row }">
            {{ row.team_name || row.user?.team?.name || 'N/A' }}
          </template>
          <template #cell-user="{ row }">
            {{ row.name || row.user?.name || 'N/A' }}
          </template>
          <template #cell-caller_duration="{ value }">
            {{ value || 'N/A' }}
          </template>
          <template #cell-call_back="{ value }">
            <span v-if="value === 'N'" class="badge bg-warning">Pending</span>
            <span v-else-if="value === 'Y'" class="badge bg-success">Returned</span>
            <span v-else class="text-muted">N/A</span>
          </template>
          </Table>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        <span v-if="pagination.total > 0">
          Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to
          {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}
          of {{ pagination.total }} entries
        </span>
        <span v-else>No entries</span>
      </div>
      <Pagination
        :current-page="pagination.current_page"
        :total-pages="pagination.last_page"
        @page-change="fetchLogs"
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

import Table from '@/components/Table.vue'
import Pagination from '@/components/Pagination.vue'
import { formatDateDisplay, formatDateTimeDisplay } from '@/utils/dateFormatter'

const authStore = useAuthStore()

const isAdmin = computed(() => authStore.userRole === 'admin')
const isUser = computed(() => authStore.userRole === 'user')
const isManager = computed(() => authStore.userRole === 'manager')

const loading = ref(false)
const exporting = ref(false)
const optionsLoading = ref(false)

let optionsAbort = null
let logsAbort = null

const options = ref({
  organizations: [],
  teams: [],
  users: [],
  sims: []
})

const filters = ref({
  date_range: '',
  start_time: '00:00',
  end_time: '23:59',
  call_type: '',
  call_status: '',
  organization_id: '',
  team_id: '',
  user_id: '',
  sim_mobile: [],
  caller_number: '',
  call_back: ''
})

const perPage = ref('10')

const filtersOpen = ref(false)

// Multiselect dropdown state
const simDropdownOpen = ref(false)
const simTriggerEl = ref(null)
const simMenuEl = ref(null)

const startTimeTriggerEl = ref(null)
const endTimeTriggerEl = ref(null)

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

// Time picker state (custom dropdown)
const timeDropdownOpen = ref(null) // 'start' | 'end' | null
const startTimeMenuEl = ref(null)
const endTimeMenuEl = ref(null)

const timeHours = Array.from({ length: 24 }, (_, i) => String(i).padStart(2, '0'))
const timeMinutes = Array.from({ length: 60 }, (_, i) => String(i).padStart(2, '0'))

const parseTime = (value, fallback = '00:00') => {
  const src = String(value || '').trim()
  const m = /^(\d{1,2}):(\d{1,2})$/.exec(src)
  const fb = /^(\d{1,2}):(\d{1,2})$/.exec(String(fallback).trim())

  const hh = m ? Number(m[1]) : fb ? Number(fb[1]) : 0
  const mm = m ? Number(m[2]) : fb ? Number(fb[2]) : 0

  const clampedH = Math.min(23, Math.max(0, Number.isFinite(hh) ? hh : 0))
  const clampedM = Math.min(59, Math.max(0, Number.isFinite(mm) ? mm : 0))

  return {
    hh: String(clampedH).padStart(2, '0'),
    mm: String(clampedM).padStart(2, '0')
  }
}

const startTimeParts = computed(() => parseTime(filters.value.start_time, '00:00'))
const endTimeParts = computed(() => parseTime(filters.value.end_time, '23:59'))

const setTimeHour = (which, hh) => {
  const parts = which === 'start' ? startTimeParts.value : endTimeParts.value
  const key = which === 'start' ? 'start_time' : 'end_time'
  filters.value[key] = `${hh}:${parts.mm}`
}

const setTimeMinute = (which, mm) => {
  const parts = which === 'start' ? startTimeParts.value : endTimeParts.value
  const key = which === 'start' ? 'start_time' : 'end_time'
  filters.value[key] = `${parts.hh}:${mm}`
}

const scrollTimeMenuToActive = (which) => {
  const menu = which === 'start' ? startTimeMenuEl.value : endTimeMenuEl.value
  if (!menu) return

  const parts = which === 'start' ? startTimeParts.value : endTimeParts.value

  const hourEl = menu.querySelector(`.timepicker-col.hours .timepicker-item[data-value="${parts.hh}"]`)
  const minuteEl = menu.querySelector(`.timepicker-col.minutes .timepicker-item[data-value="${parts.mm}"]`)
  hourEl?.scrollIntoView?.({ block: 'center' })
  minuteEl?.scrollIntoView?.({ block: 'center' })
}

const toggleTimeDropdown = async (which) => {
  // Close SIM dropdown when opening time picker
  simDropdownOpen.value = false
  
  timeDropdownOpen.value = timeDropdownOpen.value === which ? null : which
  if (timeDropdownOpen.value) {
    await nextTick()
    scrollTimeMenuToActive(which)
  }
}

const toggleSimDropdown = () => {
  // Close time picker when opening SIM dropdown
  timeDropdownOpen.value = null
  
  simDropdownOpen.value = !simDropdownOpen.value
}

const toggleFilters = () => {
  filtersOpen.value = !filtersOpen.value
  if (!filtersOpen.value) {
    simDropdownOpen.value = false
    timeDropdownOpen.value = null
  }
}

/**
 * Helper to check if event target is within a given element
 */
const isClickInside = (targetEl, el) => {
  if (!el) return false
  return el.contains(targetEl)
}

/**
 * Close dropdowns when clicking within filters but outside dropdown menus
 */
const onFiltersMousedown = (e) => {
  const targetEl = e?.target
  if (!(targetEl instanceof Element)) return

  // Check if click is inside any dropdown trigger or menu
  const inSim = isClickInside(targetEl, simTriggerEl.value) || isClickInside(targetEl, simMenuEl.value)
  const inStartTime = isClickInside(targetEl, startTimeTriggerEl.value) || isClickInside(targetEl, startTimeMenuEl.value)
  const inEndTime = isClickInside(targetEl, endTimeTriggerEl.value) || isClickInside(targetEl, endTimeMenuEl.value)
  
  // Close dropdowns if clicking outside them (but inside filters card)
  if (!inSim) simDropdownOpen.value = false
  if (!inStartTime && !inEndTime) timeDropdownOpen.value = null
}

/**
 * Close all dropdowns when clicking outside filters area
 */
const closeDropdowns = (e) => {
  const targetEl = e?.target
  if (!(targetEl instanceof Element)) return

  // If click is outside the entire filters card, close all dropdowns
  if (!targetEl.closest('.filters-card')) {
    simDropdownOpen.value = false
    timeDropdownOpen.value = null
  }
}

// Snapshot of last searched filters.
// Pagination + export use this, so changing filters doesn't auto-change results.
const appliedFilters = ref(null)

const dateRangeEl = ref(null)
const startTimeEl = ref(null)
const endTimeEl = ref(null)

let dateRangePicker = null

const formatTodayDmy = () => {
  const d = new Date()
  const dd = String(d.getDate()).padStart(2, '0')
  const mm = String(d.getMonth() + 1).padStart(2, '0')
  const yyyy = String(d.getFullYear())
  return `${dd}/${mm}/${yyyy}`
}

const applyDefaultTodayRange = () => {
  const today = formatTodayDmy()
  filters.value.date_range = `${today} - ${today}`
  filters.value.start_time = '00:00'
  filters.value.end_time = '23:59'
}

const forceDateRangeToToday = async () => {
  const todayStr = formatTodayDmy()
  const display = `${todayStr} - ${todayStr}`

  // Update reactive model first (so UI has a value even if flatpickr is not ready)
  filters.value.date_range = display

  await nextTick()

  try {
    const today = new Date()
    dateRangePicker?.setDate?.([today, today], true)
    dateRangePicker?.close?.()
  } catch {
    // ignore
  }

  // Extra safety: ensure the input shows the value immediately
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

const logs = ref([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0
})

const headers = [
  { key: 'date', label: 'Date' },
  { key: 'time', label: 'Time' },
  { key: 'caller_duration', label: 'Caller Duration' },
  { key: 'call_type', label: 'Call Type' },
  { key: 'call_status', label: 'Status' },
  { key: 'team', label: 'Team' },
  { key: 'user', label: 'Name' },
  { key: 'caller_id', label: 'SIM' },
  { key: 'caller_number', label: 'Customer No.' },
  { key: 'call_back', label: 'Return Call' }
]

const toYmd = (dmy) => {
  // dmy: dd/mm/yyyy
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

  // Flatpickr range separator can be " to " (default) or custom like " - ".
  const parts = range
    .split(/\s+(?:-|to)\s+/i)
    .map((p) => p.trim())
    .filter(Boolean)
  const startDmy = parts[0]
  const endDmy = parts[1] || parts[0]

  const startYmd = toYmd(startDmy)
  const endYmd = toYmd(endDmy)

  if (!startYmd || !endYmd) return { start_date_time: undefined, end_date_time: undefined }

  const startTime = (src.start_time || '00:00').trim()
  const endTime = (src.end_time || '23:59').trim()

  return {
    start_date_time: `${startYmd} ${startTime}`,
    end_date_time: `${endYmd} ${endTime}`
  }
}

const normalizeFilterParams = (src) => ({
  ...getStartEndDateTime(src),
  call_type: src.call_type || undefined,
  call_status: src.call_status || undefined,
  organization_id: src.organization_id || undefined,
  team_id: src.team_id || undefined,
  user_id: src.user_id || undefined,
  sim_mobile: src.sim_mobile || undefined,
  caller_number: src.caller_number || undefined,
  call_back: src.call_back || undefined
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

  const params = {
    organization_id: filters.value.organization_id || undefined,
    team_id: filters.value.team_id || undefined,
    user_id: filters.value.user_id || undefined
  }

  try {
    const resp = await api.get('/call-reports/options', { params, signal: optionsAbort.signal })
    options.value = resp.data
  } catch (e) {
    // Ignore abort errors
    if (e?.name !== 'CanceledError' && e?.code !== 'ERR_CANCELED') {
      throw e
    }
  } finally {
    optionsLoading.value = false
  }
}

const fetchLogs = async (page = 1) => {
  if (logsAbort) {
    logsAbort.abort()
  }
  logsAbort = new AbortController()

  loading.value = true
  try {
    const resp = await api.get('/call-reports', { params: buildParams(false, page, true), signal: logsAbort.signal })
    logs.value = resp.data.data || []
    pagination.value = {
      current_page: resp.data.current_page,
      last_page: resp.data.last_page,
      per_page: resp.data.per_page,
      total: resp.data.total
    }
  } finally {
    loading.value = false
  }
}

const applySearch = async () => {
  appliedFilters.value = { ...filters.value }
  try {
    await fetchLogs(1)
  } finally {
    // Close filters after search (requested UX)
    filtersOpen.value = false
    simDropdownOpen.value = false
    timeDropdownOpen.value = null
  }
}

const refresh = async () => {
  await fetchOptions()
  if (!appliedFilters.value) {
    appliedFilters.value = { ...filters.value }
  }
  await fetchLogs(1)
}

const onPerPageChange = async () => {
  if (!appliedFilters.value) {
    appliedFilters.value = { ...filters.value }
  }
  await fetchLogs(1)
}

const resetAll = async () => {
  // Reset to today's full-day range
  applyDefaultTodayRange()
  filters.value.call_type = ''
  filters.value.call_status = ''
  filters.value.team_id = ''
  filters.value.user_id = ''
  filters.value.sim_mobile = []
  filters.value.caller_number = ''
  filters.value.call_back = ''

  if (isAdmin.value) {
    filters.value.organization_id = ''
  }

  // Keep date picker + input UI in sync with the default values
  await forceDateRangeToToday()

  timeDropdownOpen.value = null

  appliedFilters.value = { ...filters.value }
  await fetchOptions()
  await fetchLogs(1)
}

const onOrganizationChange = async () => {
  filters.value.team_id = ''
  filters.value.user_id = ''
  filters.value.sim_mobile = []

  // Clear dependent option lists immediately to avoid showing stale items
  options.value.teams = []
  options.value.users = []
  options.value.sims = []

  await fetchOptions()
}

const onTeamChange = async () => {
  filters.value.user_id = ''
  filters.value.sim_mobile = []

  options.value.users = []
  options.value.sims = []

  await fetchOptions()
}

const onUserChange = async () => {
  filters.value.sim_mobile = []

  options.value.sims = []

  await fetchOptions()
}

const simKey = (sim) => {
  return `${sim.mobile}-${sim.id || ''}`
}

const queueExportMsg = ref('')

const exportFile = async (format) => {
  exporting.value = true
  queueExportMsg.value = ''
  try {
    const filters = buildParams(true, 1, true)
    const resp = await api.post('/call-reports/queue-export', { format, filters })
    queueExportMsg.value = resp.data?.message ?? "Report queued. You'll be notified when it's ready."
  } catch {
    queueExportMsg.value = 'Failed to queue report. Please try again.'
  } finally {
    exporting.value = false
  }
}

const formatDateTime = (value) => {
  return formatDateTimeDisplay(value, value || 'N/A')
}

const formatDateOnly = (row) => {
  const value = row?.date || row?.date_time
  return formatDateDisplay(value, value || 'N/A')
}

const formatTimeOnly = (row) => {
  const value = row?.time || row?.date_time
  if (!value) return 'N/A'
  // If we got a dedicated time string, just show HH:MM(:SS)
  if (typeof value === 'string' && /^\d{2}:\d{2}(:\d{2})?$/.test(value.trim())) {
    return value.trim()
  }
  try {
    const dt = new Date(value)
    if (!Number.isNaN(dt.getTime())) return dt.toLocaleTimeString()
  } catch {
    // ignore
  }
  // Fallback: if datetime string, extract time part
  const s = String(value)
  const m = /\b(\d{2}:\d{2}:\d{2})\b/.exec(s)
  return m ? m[1] : s
}

onMounted(async () => {
  // Default to today's full-day range on first load
  applyDefaultTodayRange()

  // Date range picker
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
  await fetchLogs(1)
  
  // Close dropdowns when clicking outside filters area
  // Use mousedown in capture phase for reliability across all roles
  window.addEventListener('mousedown', closeDropdowns, true)
})

onBeforeUnmount(() => {
  dateRangePicker?.destroy?.()
  window.removeEventListener('mousedown', closeDropdowns, true)
})
</script>

<style scoped>

.text-orange {
  color: #ff6a00 !important;
}

/* Reduce gap between title and Filters button (Call Report only) */
.page-header .header-content {
  justify-content: flex-start !important;
  gap: 10px !important;
}

.page-header .header-title {
  flex: 0 0 auto !important;
  min-width: 0 !important;
}

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

.search-box-modern,
.select-wrapper,
.multiselect-trigger {
  border-radius: 12px;
}

.search-box-modern,
.select-wrapper {
  background: #ffffff;
  border: 1px solid #d7e2f0;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
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

.search-icon-modern,
.filter-icon,
.dropdown-arrow {
  color: #64748b;
}

.search-input-modern,
.select-modern,
.multiselect-trigger {
  min-height: 42px;
  font-size: 13px;
  color: #0f172a;
  background: transparent;
}

.search-input-modern,
.select-modern {
  border: none !important;
  box-shadow: none !important;
  padding: 9px 36px 9px 38px;
}

.select-modern {
  border-radius: 12px;
}

.multiselect-trigger {
  min-height: 42px;
  padding-left: 38px;
  padding-right: 36px;
  border: 1px solid #d7e2f0;
}

.filter-action-btn .spinner-border {
  width: 0.9rem;
  height: 0.9rem;
}

.multiselect-menu,
.timepicker-menu {
  border: 1px solid #d7e2f0;
  border-radius: 14px;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
}

.multiselect-header {
  background: #f8fbff;
  color: #334155;
  border-bottom: 1px solid #e2e8f0;
}

.multiselect-option:hover,
.timepicker-item:hover {
  background-color: #f8fbff;
}

.timepicker-selected-box {
  background: #2563eb;
}

/* Responsive Table Wrapper */
.table-responsive-wrapper {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

/* Time input specific styling */
.time-input {
  text-align: left;
  font-variant-numeric: tabular-nums;
}

/* Custom time picker (matches provided design) */
.timepicker-wrapper {
  position: relative;
}

.timepicker-menu {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  width: 170px;
  background: #fff;
  z-index: 1100;
  overflow: hidden;
}

.timepicker-selected {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  padding: 8px;
}

.timepicker-selected-box {
  background: var(--bs-primary);
  color: #fff;
  border-radius: 4px;
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  font-variant-numeric: tabular-nums;
}

.timepicker-body {
  display: grid;
  grid-template-columns: 1fr 1fr;
  border-top: 1px solid #e9ecef;
}

.timepicker-col {
  max-height: 190px;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.timepicker-col.hours {
  border-right: 1px solid #e9ecef;
}

.timepicker-item {
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 14px;
  color: #111827;
  font-variant-numeric: tabular-nums;
  user-select: none;
}

.timepicker-item:hover {
  background: #f8f9fa;
}

.timepicker-item.is-active {
  background: rgba(var(--bs-primary-rgb), 0.12);
  font-weight: 700;
}

/* Simple table look (Call Report only) */
.call-reports-table :deep(.table-responsive) {
  border: 1px solid #e3e6f0;
  border-radius: 10px;
  overflow: hidden;
}

.call-reports-table :deep(table.table) {
  width: 100%;
  min-width: 1200px;
  border-collapse: collapse;
  margin: 0;
  font-size: 14px;
}

.call-reports-table :deep(table.table thead th) {
  background: #f8f9fa;
  color: #111827;
  font-weight: 600;
  text-transform: none;
  letter-spacing: normal;
  border-bottom: 1px solid #e3e6f0;
  border-right: 1px solid #e3e6f0;
  padding: 10px 12px !important;
  text-align: left;
  vertical-align: middle;
  white-space: nowrap;
}

.call-reports-table :deep(table.table tbody td) {
  border-top: 1px solid #e3e6f0;
  border-right: 1px solid #e3e6f0;
  padding: 10px 12px !important;
  vertical-align: middle;
  background: #fff;
}

.call-reports-table :deep(table.table thead th:last-child),
.call-reports-table :deep(table.table tbody td:last-child) {
  border-right: none;
}

.call-reports-table :deep(table.table tbody tr:hover) {
  background: transparent;
}

/* Status Text with Colored Icons */
.status-text {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #374151;
}

.status-text i {
  width: 16px;
  text-align: center;
  line-height: 1;
}

.status-text i.fa-phone-volume {
  font-size: 14px !important;
}

.status-text i.fa-phone-slash {
  font-size: 11px !important;
}

/* Datatable Header Style */
.datatable-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #dee2e6;
  background: #fff;
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
  color: #6c757d;
  margin: 0;
}

.datatable-entries .form-select {
  width: 70px;
  padding: 5px 8px;
  font-size: 14px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  height: 32px;
}

.datatable-actions {
  display: flex;
  gap: 8px;
}

.action-icon-btn {
  width: 36px;
  height: 36px;
  border: 1px solid #ced4da;
  background: #fff;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  color: #6c757d;
}

.action-icon-btn:hover:not(:disabled) {
  background: #f8f9fa;
  border-color: #adb5bd;
  color: #495057;
}

.action-icon-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
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

.action-icon-btn:hover:not(:disabled) i.fa-file-excel {
  color: #059669;
}

.action-icon-btn:hover:not(:disabled) i.fa-file-alt {
  color: #2563eb;
}

/* Queue export banner */
.queue-export-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 16px;
  padding: 12px 16px;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-left: 4px solid #3b82f6;
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
  background: #dbeafe;
  color: #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  flex-shrink: 0;
}
.queue-export-banner__text {
  color: #1e40af;
  font-weight: 500;
}
.queue-export-banner__link {
  color: #2563eb;
  font-weight: 600;
  text-decoration: none;
  white-space: nowrap;
}
.queue-export-banner__link:hover { text-decoration: underline; }
.queue-export-banner__close {
  background: none;
  border: none;
  color: #93c5fd;
  font-size: 13px;
  cursor: pointer;
  padding: 2px 4px;
  line-height: 1;
  flex-shrink: 0;
}
.queue-export-banner__close:hover { color: #2563eb; }

/* Multiselect styling */
.select-modern[multiple] {
  height: auto !important;
  min-height: 38px;
  padding: 6px 12px;
}

.select-modern[multiple]:focus {
  height: auto;
}

/* Custom Multiselect Dropdown */
.multiselect-wrapper {
  position: relative;
}

.multiselect-trigger {
  position: relative;
  display: flex;
  align-items: center;
  padding-top: 8px;
  padding-bottom: 8px;
  background: #fff;
  cursor: pointer;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  line-height: 1.5;
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
  color: #6c757d;
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
  z-index: 1000;
}

.multiselect-header {
  padding: 10px 12px;
  font-size: 13px;
  font-weight: 600;
  color: #495057;
  background: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
  position: sticky;
  top: 0;
  z-index: 1;
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
  background-color: #f8f9fa;
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
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
  font-size: 14px;
  z-index: 1;
  pointer-events: none;
}

/* Responsive adjustments */
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

  .filters-card .row {
    row-gap: 20px !important;
  }

  .page-title {
    font-size: 20px;
  }

  small.text-muted {
    font-size: 11px;
    margin-top: 4px;
  }

  .call-reports-table :deep(table.table) {
    font-size: 12px;
  }

  .call-reports-table :deep(table.table thead th) {
    font-size: 11px;
    padding: 8px 10px !important;
  }

  .call-reports-table :deep(table.table tbody td) {
    font-size: 12px;
    padding: 8px 10px !important;
  }

  .timepicker-menu {
    max-height: 250px;
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

  .timepicker-wrapper {
    width: 100%;
  }

  .timepicker-menu {
    left: 0;
    right: 0;
    max-height: 200px;
  }
}
</style>
