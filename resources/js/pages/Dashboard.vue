<template>
  <div class="dashboard-page">
    <!-- Page Header -->
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-0 gap-2">
      <div>
        <h3 class="fw-bold mb-1">Dashboard</h3>
        <p class="text-muted mb-0">Welcome back! Here's your call activity overview.</p>
      </div>
      <div class="ms-md-auto py-2 py-md-0 d-flex align-items-center flex-wrap gap-2 justify-content-start justify-content-md-end">
        <div class="dropdown me-md-3">
          <button class="btn btn-white border dropdown-toggle shadow-sm" type="button" id="dateFilter" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="far fa-calendar-alt me-2 text-orange"></i>
            {{ dateFilterLabel }}
          </button>
          <ul class="dropdown-menu" aria-labelledby="dateFilter">
            <li>
              <button type="button" class="dropdown-item" @click="selectDatePreset('today')">Today</button>
            </li>
            <li>
              <button type="button" class="dropdown-item" @click="selectDatePreset('yesterday')">Yesterday</button>
            </li>
            <li>
              <button type="button" class="dropdown-item" @click="selectDatePreset('last7')">Last 7 Days</button>
            </li>
            <li>
              <button type="button" class="dropdown-item" @click="selectDatePreset('thisMonth')">This Month</button>
            </li>
            <li><hr class="dropdown-divider" /></li>
            <li>
              <button
                type="button"
                class="dropdown-item"
                data-bs-toggle="modal"
                data-bs-target="#dashboardDateRangeModal"
                @click="prepareCustomRange"
              >
                Custom Range
              </button>
            </li>
          </ul>
        </div>

        <button
          type="button"
          class="btn btn-white border shadow-sm"
          data-bs-toggle="modal"
          data-bs-target="#dashboardFiltersModal"
          aria-label="Open filters"
          title="Filters"
        >
          <i class="fas fa-filter text-orange"></i>
        </button>

        <button
          type="button"
          class="btn btn-light border shadow-sm btn-sm"
          @click="refreshData"
          aria-label="Refresh"
          title="Refresh"
        >
          <i class="fas fa-sync-alt"></i>
        </button>
      </div>
    </div>

    <!-- Date Range Modal (Custom Range) -->
    <div
      class="modal fade"
      id="dashboardDateRangeModal"
      tabindex="-1"
      aria-labelledby="dashboardDateRangeModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-round border-0">
          <div class="modal-header border-0">
            <h5 class="modal-title fw-bold" id="dashboardDateRangeModalLabel">Select Date Range</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body pt-0">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label small text-muted">Date Range</label>
                <input
                  ref="customRangeInput"
                  type="text"
                  class="form-control"
                  placeholder="YYYY-MM-DD to YYYY-MM-DD"
                  aria-label="Custom date range"
                />
              </div>
              <div class="col-12">
                <div v-if="customRangeError" class="form-text text-danger">
                  {{ customRangeError }}
                </div>
                <div v-else class="form-text">
                  Range applies to the dashboard counts and Call Breakdown. Max 1 month, no future dates.
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button
              type="button"
              class="btn btn-primary"
              data-bs-dismiss="modal"
              :disabled="!isCustomRangeValid"
              @click="applyCustomRange"
            >
              Apply
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Modal -->
    <div
      class="modal fade"
      id="dashboardFiltersModal"
      tabindex="-1"
      aria-labelledby="dashboardFiltersModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content card-round border-0">
          <div class="modal-header border-0">
            <h5 class="modal-title fw-bold" id="dashboardFiltersModalLabel">Filters</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body pt-0">
            <div class="row g-3">
              <div v-if="isAdmin" class="col-12 col-md-6">
                <label class="form-label small text-muted">Organization</label>
                <select
                  class="form-select"
                  v-model="filters.organization_id"
                  @change="onOrganizationChange"
                  :disabled="optionsLoading"
                >
                  <option value="">All Organizations</option>
                  <option v-for="org in dashboardOptions.organizations" :key="org.id" :value="String(org.id)">
                    {{ org.name }}
                  </option>
                </select>
              </div>

              <div v-if="!isUser" class="col-12 col-md-6">
                <label class="form-label small text-muted">Department</label>
                <select
                  class="form-select"
                  v-model="filters.department_id"
                  @change="onDepartmentChange"
                  :disabled="optionsLoading || (isAdmin && !filters.organization_id)"
                >
                  <option value="">All Departments</option>
                  <option v-if="optionsLoading" value="" disabled>Loading...</option>
                  <option v-for="dept in dashboardOptions.departments" :key="dept.id" :value="String(dept.id)">
                    {{ dept.name }}
                  </option>
                </select>
              </div>

              <div v-if="!isUser" class="col-12 col-md-6">
                <label class="form-label small text-muted">Users</label>
                <select
                  class="form-select"
                  v-model="filters.user_id"
                  @change="onUserChange"
                  :disabled="optionsLoading || (!filters.department_id && dashboardOptions.users.length === 0)"
                >
                  <option value="">All Users</option>
                  <option v-if="optionsLoading" value="" disabled>Loading...</option>
                  <option v-for="u in dashboardOptions.users" :key="u.id" :value="String(u.id)">
                    {{ u.name }}
                  </option>
                </select>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label small text-muted">SIM Cards</label>
                <div class="multiselect-wrapper" :class="{ 'is-disabled': optionsLoading }">
                  <div
                    ref="simTriggerEl"
                    class="multiselect-trigger"
                    role="button"
                    tabindex="0"
                    :aria-expanded="simDropdownOpen ? 'true' : 'false'"
                    @click="toggleSimDropdown"
                    @keydown.enter.prevent="toggleSimDropdown"
                    @keydown.space.prevent="toggleSimDropdown"
                  >
                    <span v-if="filters.sim_mobile.length === 0" class="ms-placeholder">All SIM Cards</span>
                    <span v-else class="selected-count">{{ filters.sim_mobile.length }} selected</span>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                  </div>

                  <div
                    v-if="simDropdownOpen"
                    ref="simMenuEl"
                    class="multiselect-menu"
                    @mousedown.stop
                    @click.stop
                  >
                    <div class="multiselect-header">SIM Cards</div>

                    <div class="multiselect-option" @click="selectAllSims">
                      <input type="checkbox" :checked="isAllSimsSelected" readonly />
                      <label>All SIM Cards</label>
                    </div>

                    <div
                      v-for="sim in dashboardOptions.sims"
                      :key="simKey(sim)"
                      class="multiselect-option"
                      @click="toggleSim(sim.mobile)"
                    >
                      <input type="checkbox" :checked="filters.sim_mobile.includes(sim.mobile)" readonly />
                      <label>{{ sim.mobile }}<span v-if="sim.name"> - {{ sim.name }}</span></label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label small text-muted">Customer Number</label>
                <input
                  type="text"
                  class="form-control"
                  placeholder="Customer number"
                  v-model="filters.caller_number"
                />
              </div>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-outline-secondary" @click="resetFilters">Reset</button>
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @click="applyDashboardFilters">Apply</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Row: Main Stats -->
    <div class="row gy-3 mb-0 align-items-stretch">
      <!-- Total Calls (Large Orange Card) -->
      <div class="col-md-6">
        <div class="card card-round bg-orange-gradient text-white shadow-lg border-0">
          <div class="card-body d-flex flex-column justify-content-between p-3">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <p class="mb-0 opacity-75 fw-bold small">Total Calls</p>
                <h1 class="display-5 fw-bold mb-0">{{ dashboardSummary.total_calls.toLocaleString() }}</h1>
                <p class="mb-0 opacity-75 small">All call activity</p>
              </div>
              <div
                class="badge bg-white bg-opacity-25 rounded-pill px-2 py-1 fw-bold"
                :class="(dashboardSummary.total_calls_change_pct ?? 0) >= 0 ? 'text-white' : 'text-danger'"
              >
                {{ dashboardSummary.total_calls_change_label || '0%' }}
              </div>
            </div>
            <div class="mt-2">
              <div class="d-flex align-items-center opacity-50">
                <i class="fas fa-circle me-2 small"></i>
                <span class="small">Live tracking enabled</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Answer Rate & Avg Duration -->
      <div class="col-md-6 d-flex">
        <div class="row gy-3 flex-fill">
          <div class="col-sm-6 d-flex">
            <div class="card card-round shadow-sm border-0 flex-fill">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div class="icon-box bg-success-light rounded-3">
                    <i class="fas fa-phone text-success small"></i>
                  </div>
                  <span
                    class="fw-bold small"
                    :class="(dashboardSummary.answer_rate_change_pct ?? 0) >= 0 ? 'text-success' : 'text-danger'"
                  >
                    {{ dashboardSummary.answer_rate_change_label || '0%' }}
                  </span>
                </div>
                <h3 class="fw-bold mb-1">{{ dashboardSummary.answer_rate_pct }}%</h3>
                <p class="text-muted mb-0 small">Answer Rate</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6 d-flex">
            <div class="card card-round shadow-sm border-0 flex-fill">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div class="icon-box bg-warning-light rounded-3">
                    <i class="far fa-clock text-warning small"></i>
                  </div>
                  <span
                    class="fw-bold small"
                    :class="(dashboardSummary.avg_duration_change_pct ?? 0) >= 0 ? 'text-success' : 'text-danger'"
                  >
                    {{ dashboardSummary.avg_duration_change_label || '0%' }}
                  </span>
                </div>
                <h3 class="fw-bold mb-1">{{ dashboardSummary.avg_duration_display }}</h3>
                <p class="text-muted mb-0 small">Avg Duration</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Second Row: Outbound/Inbound/Missed/Unique Summary -->
    <div class="row gy-3 mb-0">
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-round shadow-sm border-0">
          <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <div class="icon-box bg-danger-light rounded-3 me-3">
                  <i class="fas fa-phone text-danger" style="font-size: 1.3rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1">Outbound</p>
                  <h2 class="fw-bold mb-0">{{ dashboardSummary.outbound_calls.toLocaleString() }}</h2>
                </div>
              </div>
              <span
                class="fw-bold"
                :class="(dashboardSummary.outbound_calls_change_pct ?? 0) >= 0 ? 'text-success' : 'text-danger'"
              >
                {{ dashboardSummary.outbound_calls_change_label || '0%' }}
              </span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-round shadow-sm border-0">
          <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <div class="icon-box bg-info-light rounded-3 me-3">
                  <i class="fas fa-phone text-info" style="font-size: 1.3rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1">Inbound</p>
                  <h2 class="fw-bold mb-0">{{ dashboardSummary.inbound_calls.toLocaleString() }}</h2>
                </div>
              </div>
              <span
                class="fw-bold"
                :class="(dashboardSummary.inbound_calls_change_pct ?? 0) >= 0 ? 'text-success' : 'text-danger'"
              >
                {{ dashboardSummary.inbound_calls_change_label || '0%' }}
              </span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-round shadow-sm border-0">
          <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <div class="icon-box bg-warning-light rounded-3 me-3">
                  <i class="fas fa-phone-slash text-warning" style="font-size: 1.3rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1">Missed Calls</p>
                  <h2 class="fw-bold mb-0">{{ dashboardSummary.missed_calls.toLocaleString() }}</h2>
                </div>
              </div>
              <span
                class="fw-bold"
                :class="(dashboardSummary.missed_calls_change_pct ?? 0) >= 0 ? 'text-success' : 'text-danger'"
              >
                {{ dashboardSummary.missed_calls_change_label || '0%' }}
              </span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-round shadow-sm border-0">
          <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <div class="icon-box bg-warning-light rounded-3 me-3">
                  <i class="fas fa-users text-orange" style="font-size: 1.3rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1">Unique Calls</p>
                  <h2 class="fw-bold mb-0">{{ dashboardSummary.unique_calls.toLocaleString() }}</h2>
                </div>
              </div>
              <span
                class="fw-bold"
                :class="(dashboardSummary.unique_calls_change_pct ?? 0) >= 0 ? 'text-success' : 'text-danger'"
              >
                {{ dashboardSummary.unique_calls_change_label || '0%' }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Call Breakdown Section -->
    <div class="row gy-3 mb-0">
      <div class="col-12">
        <h5 class="fw-bold mb-0">Call Breakdown</h5>
      </div>
      
      <!-- Outbound Calls Breakdown -->
      <div class="col-12 col-lg-6">
        <div class="card card-round shadow-sm border-0">
          <div class="card-body p-4">
            <div class="d-flex align-items-center mb-3">
              <div class="icon-box bg-danger-light rounded-3 me-3">
                <i class="fas fa-phone text-danger"></i>
              </div>
              <h6 class="fw-bold mb-0">Outbound Calls</h6>
            </div>
            
            <div class="breakdown-items mt-3">
              <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div class="d-flex align-items-center">
                  <i class="fas fa-phone icon-accent me-2"></i>
                  <span>Answered</span>
                </div>
                <span class="fw-bold">{{ dashboardSummary.breakdown.outbound.answered.toLocaleString() }}</span>
              </div>
              
              <div class="d-flex justify-content-between align-items-center py-2 mt-2">
                <div class="d-flex align-items-center">
                  <i class="fas fa-phone-slash text-warning me-2"></i>
                  <span>No Answer</span>
                </div>
                <span class="fw-bold">{{ dashboardSummary.breakdown.outbound.no_answer.toLocaleString() }}</span>
              </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center pt-3 mt-3 border-top">
              <span class="text-muted">Total Outbound</span>
              <h5 class="fw-bold mb-0">{{ dashboardSummary.breakdown.outbound.total.toLocaleString() }}</h5>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Inbound Calls Breakdown -->
      <div class="col-12 col-lg-6">
        <div class="card card-round shadow-sm border-0">
          <div class="card-body p-4">
            <div class="d-flex align-items-center mb-3">
              <div class="icon-box bg-info-light rounded-3 me-3">
                <i class="fas fa-phone text-info"></i>
              </div>
              <h6 class="fw-bold mb-0">Inbound Calls</h6>
            </div>
            
            <div class="breakdown-items mt-3">
              <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div class="d-flex align-items-center">
                  <i class="fas fa-phone text-success me-2"></i>
                  <span>Answered</span>
                </div>
                <span class="fw-bold">{{ dashboardSummary.breakdown.inbound.answered.toLocaleString() }}</span>
              </div>
              
              <div class="d-flex justify-content-between align-items-center py-2 mt-2">
                <div class="d-flex align-items-center">
                  <i class="fas fa-phone-slash text-danger me-2"></i>
                  <span>Missed</span>
                </div>
                <span class="fw-bold">{{ dashboardSummary.breakdown.inbound.missed.toLocaleString() }}</span>
              </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center pt-3 mt-3 border-top">
              <span class="text-muted">Total Inbound</span>
              <h5 class="fw-bold mb-0">{{ dashboardSummary.breakdown.inbound.total.toLocaleString() }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Third Row: Daily Call Volume -->
    <div class="row mb-0">
      <!-- Daily Call Volume (Line Chart Style) -->
      <div class="col-md-12">
        <div class="card card-round shadow-sm border-0">
          <div class="card-header bg-transparent border-0 pt-4 px-4">
            <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
              <div>
                <h5 class="fw-bold mb-0">Daily Call Volume</h5>
                <p class="text-muted small">{{ callVolumeSubtitle }}</p>
              </div>
              <div class="btn-group btn-group-sm flex-shrink-0" role="group" aria-label="Chart type">
                <button
                  type="button"
                  class="btn"
                  :class="callVolumeView === 'line' ? 'btn-primary' : 'btn-outline-primary'"
                  @click="callVolumeView = 'line'"
                >
                  Line
                </button>
                <button
                  type="button"
                  class="btn"
                  :class="callVolumeView === 'bar' ? 'btn-primary' : 'btn-outline-primary'"
                  @click="callVolumeView = 'bar'"
                >
                  Bar
                </button>
              </div>
            </div>
          </div>
          <div class="card-body px-4 pb-4">
            <div class="chart-container position-relative">
              <div
                v-if="dailyCallVolumeLoading"
                class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75 loading-overlay"
              >
                <div class="text-center">
                  <div class="spinner-border text-primary" role="status" aria-label="Loading"></div>
                </div>
              </div>
              <canvas ref="callVolumeCanvas" class="w-100 h-100" aria-label="Daily Call Volume" role="img"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Fourth Row: Peak Hours & Missed Calls -->
    <div class="row gy-3 mb-0">
      <!-- Peak Call Hours -->
      <div class="col-12 col-lg-6">
        <div class="card card-round shadow-sm border-0">
          <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h5 class="fw-bold mb-0">Peak Call Hours</h5>
            <p class="text-muted small">Highest activity periods</p>
          </div>
          <div class="card-body px-4 pb-4">
            <div v-if="peakHours.length === 0" class="text-center text-muted py-4">
              <div class="fw-semibold">No data found</div>
              <div class="small">Try adjusting filters or date range.</div>
            </div>
            <div v-else>
              <div
                v-for="(item, index) in peakHours"
                :key="`${item.label}-${index}`"
                class="peak-hour-item"
                :class="index === 0 ? 'mb-4' : ''"
              >
                <div class="d-flex align-items-center mb-2">
                  <div
                    class="icon-box rounded-3 p-2 me-3"
                    :class="index === 0 ? 'bg-warning-light' : 'bg-danger-light'"
                  >
                    <i
                      class="fas"
                      :class="index === 0 ? 'fa-sun text-warning' : 'fa-cloud-sun text-danger'"
                    ></i>
                  </div>
                  <div>
                    <h6 class="fw-bold mb-0">{{ item.label }}</h6>
                    <p class="text-muted small mb-0">{{ Number(item.count || 0) }} calls</p>
                  </div>
                </div>
                <div class="progress rounded-pill" style="height: 8px;">
                  <div class="progress-bar bg-orange" :style="{ width: `${Number(item.pct || 0)}%` }"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Missed Calls Analysis -->
      <div class="col-12 col-lg-6">
        <div class="card card-round shadow-sm border-0">
          <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h5 class="fw-bold mb-0">Missed Calls Analysis</h5>
            <p class="text-muted small">Total missed calls breakdown</p>
          </div>
          <div class="card-body p-4">
            <div class="row align-items-center">
              <div class="col-md-4 text-center mb-4 mb-md-0">
                <div class="missed-donut position-relative d-inline-block">
                  <div
                    v-if="summaryLoading"
                    class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75 loading-overlay"
                  >
                    <div class="text-center">
                      <div class="spinner-border text-primary" role="status" aria-label="Loading"></div>
                    </div>
                  </div>
                  <canvas
                    ref="missedCallsCanvas"
                    class="w-100 h-100"
                    aria-label="Missed calls breakdown"
                    role="img"
                  ></canvas>
                  <div class="position-absolute top-50 start-50 translate-middle text-center">
                    <h2 class="fw-bold mb-0">{{ missedTotal }}</h2>
                    <p class="text-muted small mb-0">Total Missed</p>
                  </div>
                </div>
              </div>
              <div class="col-md-8">
                <div class="card bg-success-light border-0 mb-3 card-round">
                  <div class="card-body d-flex justify-content-between align-items-center p-3">
                    <div class="d-flex align-items-center">
                      <div class="dot bg-success me-3"></div>
                      <div>
                        <h6 class="fw-bold mb-0">Returned Calls</h6>
                        <p class="text-muted small mb-0">{{ returnedCallsPct }}% of total</p>
                      </div>
                    </div>
                    <h4 class="fw-bold text-success mb-0">{{ missedCalls.returned }}</h4>
                  </div>
                </div>
                <div class="card bg-warning-light border-0 card-round">
                  <div class="card-body d-flex justify-content-between align-items-center p-3">
                    <div class="d-flex align-items-center">
                      <div class="dot bg-warning me-3"></div>
                      <div>
                        <h6 class="fw-bold mb-0">Callback Pending</h6>
                        <p class="text-muted small mb-0">{{ callbackPendingPct }}% of total</p>
                      </div>
                    </div>
                    <h4 class="fw-bold text-warning mb-0">{{ missedCalls.pending }}</h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue'
import Chart from 'chart.js/auto'
import flatpickr from 'flatpickr'
import 'flatpickr/dist/flatpickr.min.css'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const isAdmin = computed(() => authStore.userRole === 'admin')
const isUser = computed(() => authStore.userRole === 'user')

const optionsLoading = ref(false)
const summaryLoading = ref(false)

const dashboardOptions = reactive({
  organizations: [],
  departments: [],
  users: [],
  sims: []
})

const dashboardSummary = reactive({
  total_calls: 0,
  total_calls_change_pct: 0,
  total_calls_change_label: '0%',
  answer_rate_pct: 0,
  answer_rate_change_pct: 0,
  answer_rate_change_label: '0%',
  avg_duration_seconds: 0,
  avg_duration_display: '0s',
  avg_duration_change_pct: 0,
  avg_duration_change_label: '0%',
  outbound_calls: 0,
  outbound_calls_change_pct: 0,
  outbound_calls_change_label: '0%',
  inbound_calls: 0,
  inbound_calls_change_pct: 0,
  inbound_calls_change_label: '0%',
  missed_calls: 0,
  missed_calls_change_pct: 0,
  missed_calls_change_label: '0%',
  unique_calls: 0,
  unique_calls_change_pct: 0,
  unique_calls_change_label: '0%',
  breakdown: {
    outbound: { answered: 0, no_answer: 0, total: 0 },
    inbound: { answered: 0, missed: 0, total: 0 }
  }
})

const filters = reactive({
  organization_id: '',
  department_id: '',
  user_id: '',
  sim_mobile: [],
  caller_number: ''
})

// SIM multiselect dropdown (matches Call Reports behavior)
const simDropdownOpen = ref(false)
const simTriggerEl = ref(null)
const simMenuEl = ref(null)

const isAllSimsSelected = computed(() => {
  return filters.sim_mobile.length === dashboardOptions.sims.length && dashboardOptions.sims.length > 0
})

const toggleSim = (mobile) => {
  const index = filters.sim_mobile.indexOf(mobile)
  if (index > -1) {
    filters.sim_mobile.splice(index, 1)
  } else {
    filters.sim_mobile.push(mobile)
  }
}

const selectAllSims = () => {
  if (isAllSimsSelected.value) {
    filters.sim_mobile = []
  } else {
    filters.sim_mobile = dashboardOptions.sims.map((sim) => sim.mobile)
  }
}

const toggleSimDropdown = () => {
  if (optionsLoading.value) return
  simDropdownOpen.value = !simDropdownOpen.value
}

const closeSimDropdown = () => {
  simDropdownOpen.value = false
}

const onDocumentMouseDown = (e) => {
  if (!simDropdownOpen.value) return

  const target = e.target
  const inTrigger = simTriggerEl.value?.contains?.(target)
  const inMenu = simMenuEl.value?.contains?.(target)

  if (!inTrigger && !inMenu) closeSimDropdown()
}

const datePreset = ref('today')
const dateRange = reactive({
  startDateTime: '',
  endDateTime: ''
})

const customRange = reactive({
  start: '',
  end: ''
})

const customRangeInput = ref(null)
let customRangePicker = null
let suppressCustomRangeOnChange = false
let dateRangeModalEl = null
let onDateRangeModalShown = null

const MAX_CUSTOM_RANGE_DAYS = 31

const parseYmdUtc = (ymd) => {
  if (!ymd) return null
  const parts = String(ymd).split('-').map((v) => Number(v))
  if (parts.length !== 3) return null
  const [y, m, d] = parts
  if (!y || !m || !d) return null
  return new Date(Date.UTC(y, m - 1, d))
}

const addDaysUtc = (dateObj, days) => {
  const d = new Date(dateObj.getTime())
  d.setUTCDate(d.getUTCDate() + days)
  return d
}

const formatYmdUtc = (dateObj) => {
  const year = dateObj.getUTCFullYear()
  const month = String(dateObj.getUTCMonth() + 1).padStart(2, '0')
  const day = String(dateObj.getUTCDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const diffDaysInclusiveUtc = (startYmd, endYmd) => {
  const s = parseYmdUtc(startYmd)
  const e = parseYmdUtc(endYmd)
  if (!s || !e) return null
  const ms = e.getTime() - s.getTime()
  return Math.floor(ms / 86400000) + 1
}

const todayYmd = computed(() => formatYmd(new Date()))

const isCustomRangeValid = computed(() => {
  if (!customRange.start || !customRange.end) return false
  if (customRange.start > customRange.end) return false
  if (customRange.start > todayYmd.value) return false
  if (customRange.end > todayYmd.value) return false
  const days = diffDaysInclusiveUtc(customRange.start, customRange.end)
  if (!days) return false
  return days <= MAX_CUSTOM_RANGE_DAYS
})

const customRangeError = computed(() => {
  if (!customRange.start || !customRange.end) return ''
  if (customRange.start > customRange.end) return 'End date must be after start date.'
  if (customRange.start > todayYmd.value || customRange.end > todayYmd.value) return 'Future dates are not allowed.'
  const days = diffDaysInclusiveUtc(customRange.start, customRange.end)
  if (!days) return 'Invalid date range.'
  if (days > MAX_CUSTOM_RANGE_DAYS) return `Please select a range within ${MAX_CUSTOM_RANGE_DAYS} days.`
  return ''
})

function formatYmd(d) {
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const setRangeFromDates = (startYmd, endYmd) => {
  dateRange.startDateTime = startYmd ? `${startYmd} 00:00:00` : ''
  dateRange.endDateTime = endYmd ? `${endYmd} 23:59:59` : ''
}

const dateFilterLabel = computed(() => {
  if (datePreset.value === 'today') return 'Today'
  if (datePreset.value === 'yesterday') return 'Yesterday'
  if (datePreset.value === 'last7') return 'Last 7 Days'
  if (datePreset.value === 'thisMonth') return 'This Month'
  if (datePreset.value === 'custom') {
    if (customRange.start && customRange.end) return `${customRange.start} → ${customRange.end}`
    return 'Custom Range'
  }
  return 'Today'
})

const simKey = (sim) => `${sim.mobile}-${sim.id || ''}`

const buildDashboardParams = (forOptions = false) => {
  const params = {}

  if (isAdmin.value && filters.organization_id) params.organization_id = filters.organization_id
  if (!isUser.value && filters.department_id) params.department_id = filters.department_id
  if (!isUser.value && filters.user_id) params.user_id = filters.user_id

  if (!forOptions) {
    if (dateRange.startDateTime) params.start_date_time = dateRange.startDateTime
    if (dateRange.endDateTime) params.end_date_time = dateRange.endDateTime
    if (filters.sim_mobile?.length) params.sim_mobile = filters.sim_mobile
    if (filters.caller_number?.trim()) params.caller_number = filters.caller_number.trim()
  }

  return params
}

const buildDailyCallVolumeParams = () => {
  const params = buildDashboardParams(false)

  // Requirement: when preset is Today/Yesterday, this chart should default to Last 7 Days.
  if (datePreset.value === 'today' || datePreset.value === 'yesterday') {
    const end = new Date()
    if (datePreset.value === 'yesterday') end.setDate(end.getDate() - 1)
    const start = new Date(end)
    start.setDate(start.getDate() - 6)

    const startYmd = formatYmd(start)
    const endYmd = formatYmd(end)
    params.start_date_time = `${startYmd} 00:00:00`
    params.end_date_time = `${endYmd} 23:59:59`
  }

  return params
}

const selectDatePreset = async (preset, opts = {}) => {
  datePreset.value = preset

  const today = new Date()
  const endYmd = formatYmd(today)

  if (preset === 'today') {
    customRange.start = ''
    customRange.end = ''
    setRangeFromDates(endYmd, endYmd)
  } else if (preset === 'yesterday') {
    const y = new Date(today)
    y.setDate(y.getDate() - 1)
    const yYmd = formatYmd(y)
    customRange.start = ''
    customRange.end = ''
    setRangeFromDates(yYmd, yYmd)
  } else if (preset === 'last7') {
    const start = new Date(today)
    start.setDate(start.getDate() - 6)
    const startYmd = formatYmd(start)
    customRange.start = ''
    customRange.end = ''
    setRangeFromDates(startYmd, endYmd)
  } else if (preset === 'thisMonth') {
    const start = new Date(today.getFullYear(), today.getMonth(), 1)
    const startYmd = formatYmd(start)
    customRange.start = ''
    customRange.end = ''
    setRangeFromDates(startYmd, endYmd)
  } else if (preset === 'custom') {
    // Applied only via applyCustomRange()
    if (!customRange.start) customRange.start = endYmd
    if (!customRange.end) customRange.end = endYmd
  }

  if (!opts?.deferFetch && preset !== 'custom') {
    await fetchDashboardSummary()
    await fetchDailyCallVolume()
  }
}

const prepareCustomRange = () => {
  const today = new Date()
  const todayYmd = formatYmd(today)

  // If user already has a custom range, keep it.
  // Otherwise default the picker to today.
  if (!customRange.start) customRange.start = todayYmd
  if (!customRange.end) customRange.end = todayYmd

  // Clamp any saved value to today (no future dates)
  if (customRange.start > todayYmd) customRange.start = todayYmd
  if (customRange.end > todayYmd) customRange.end = todayYmd

  // Enforce max 1-month window from the selected start
  const startUtc = parseYmdUtc(customRange.start)
  if (startUtc) {
    const maxAllowedUtc = addDaysUtc(startUtc, MAX_CUSTOM_RANGE_DAYS - 1)
    const maxAllowedYmd = formatYmdUtc(maxAllowedUtc)
    if (customRange.end > maxAllowedYmd) customRange.end = maxAllowedYmd
  }

  if (customRangePicker) {
    const start = parseYmdUtc(customRange.start)
    if (start) {
      const maxAllowed = addDaysUtc(start, MAX_CUSTOM_RANGE_DAYS - 1)
      const todayUtc = parseYmdUtc(todayYmd)
      const maxDateUtc = todayUtc && todayUtc.getTime() < maxAllowed.getTime() ? todayUtc : maxAllowed
      customRangePicker.set('maxDate', new Date(maxDateUtc.getTime()))
    } else {
      customRangePicker.set('maxDate', today)
    }
    customRangePicker.setDate([customRange.start, customRange.end], false)
  }
}

const applyCustomRange = async () => {
  if (!isCustomRangeValid.value) return
  datePreset.value = 'custom'
  setRangeFromDates(customRange.start, customRange.end)
  await fetchDashboardSummary()
  await fetchDailyCallVolume()
}

const fetchDashboardOptions = async () => {
  optionsLoading.value = true
  try {
    const resp = await api.get('/dashboard/options', { params: buildDashboardParams(true) })
    dashboardOptions.organizations = resp.data.organizations || []
    dashboardOptions.departments = resp.data.departments || []
    dashboardOptions.users = resp.data.users || []
    dashboardOptions.sims = resp.data.sims || []
  } finally {
    optionsLoading.value = false
  }
}

const fetchDashboardSummary = async () => {
  summaryLoading.value = true
  try {
    const resp = await api.get('/dashboard/summary', { params: buildDashboardParams(false) })
    const data = resp.data || {}

    dashboardSummary.total_calls = Number(data.total_calls || 0)
    dashboardSummary.total_calls_change_pct = Number(data.total_calls_change_pct || 0)
    dashboardSummary.total_calls_change_label = data.total_calls_change_label || '0%'
    dashboardSummary.answer_rate_pct = Number(data.answer_rate_pct || 0)
    dashboardSummary.answer_rate_change_pct = Number(data.answer_rate_change_pct || 0)
    dashboardSummary.answer_rate_change_label = data.answer_rate_change_label || '0%'
    dashboardSummary.avg_duration_seconds = Number(data.avg_duration_seconds || 0)
    dashboardSummary.avg_duration_display = data.avg_duration_display || '0s'
    dashboardSummary.avg_duration_change_pct = Number(data.avg_duration_change_pct || 0)
    dashboardSummary.avg_duration_change_label = data.avg_duration_change_label || '0%'
    dashboardSummary.outbound_calls = Number(data.outbound_calls || 0)
    dashboardSummary.outbound_calls_change_pct = Number(data.outbound_calls_change_pct || 0)
    dashboardSummary.outbound_calls_change_label = data.outbound_calls_change_label || '0%'
    dashboardSummary.inbound_calls = Number(data.inbound_calls || 0)
    dashboardSummary.inbound_calls_change_pct = Number(data.inbound_calls_change_pct || 0)
    dashboardSummary.inbound_calls_change_label = data.inbound_calls_change_label || '0%'
    dashboardSummary.missed_calls = Number(data.missed_calls || 0)
    dashboardSummary.missed_calls_change_pct = Number(data.missed_calls_change_pct || 0)
    dashboardSummary.missed_calls_change_label = data.missed_calls_change_label || '0%'
    dashboardSummary.unique_calls = Number(data.unique_calls || 0)
    dashboardSummary.unique_calls_change_pct = Number(data.unique_calls_change_pct || 0)
    dashboardSummary.unique_calls_change_label = data.unique_calls_change_label || '0%'

    dashboardSummary.breakdown = {
      outbound: {
        answered: Number(data?.breakdown?.outbound?.answered || 0),
        no_answer: Number(data?.breakdown?.outbound?.no_answer || 0),
        total: Number(data?.breakdown?.outbound?.total || 0)
      },
      inbound: {
        answered: Number(data?.breakdown?.inbound?.answered || 0),
        missed: Number(data?.breakdown?.inbound?.missed || 0),
        total: Number(data?.breakdown?.inbound?.total || 0)
      }
    }

    peakHours.value = Array.isArray(data.peak_hours) ? data.peak_hours : []

    missedCalls.returned = Number(data?.missed_analysis?.returned_calls || 0)
    missedCalls.pending = Number(data?.missed_analysis?.callback_pending || 0)

    await nextTick()
    initMissedCallsChart()
  } finally {
    summaryLoading.value = false
  }
}

const fetchDailyCallVolume = async () => {
  dailyCallVolumeLoading.value = true
  try {
    const resp = await api.get('/dashboard/daily-call-volume', { params: buildDailyCallVolumeParams() })
    const data = resp.data || {}

    dailyCallVolume.labels = Array.isArray(data.labels) ? data.labels : []
    dailyCallVolume.datasets = {
      total: Array.isArray(data?.datasets?.total) ? data.datasets.total : [],
      inbound: Array.isArray(data?.datasets?.inbound) ? data.datasets.inbound : [],
      outbound: Array.isArray(data?.datasets?.outbound) ? data.datasets.outbound : []
    }
    dailyCallVolume.meta = {
      start_date: data?.meta?.start_date || '',
      end_date: data?.meta?.end_date || '',
      days: Number(data?.meta?.days || 0),
      avg_per_day: Number(data?.meta?.avg_per_day || 0)
    }
  } catch (error) {
    console.error('Error fetching daily call volume:', error)
    dailyCallVolume.labels = []
    dailyCallVolume.datasets = { total: [], inbound: [], outbound: [] }
    dailyCallVolume.meta = { start_date: '', end_date: '', days: 0, avg_per_day: 0 }
  } finally {
    dailyCallVolumeLoading.value = false
  }

  await nextTick()
  initCallVolumeChart()
}

const onOrganizationChange = async () => {
  closeSimDropdown()
  filters.department_id = ''
  filters.user_id = ''
  filters.sim_mobile = []
  dashboardOptions.departments = []
  dashboardOptions.users = []
  dashboardOptions.sims = []
  await fetchDashboardOptions()
}

const onDepartmentChange = async () => {
  closeSimDropdown()
  filters.user_id = ''
  filters.sim_mobile = []
  dashboardOptions.users = []
  dashboardOptions.sims = []
  await fetchDashboardOptions()
}

const onUserChange = async () => {
  closeSimDropdown()
  filters.sim_mobile = []
  dashboardOptions.sims = []
  await fetchDashboardOptions()
}

const applyDashboardFilters = async () => {
  closeSimDropdown()
  await fetchDashboardSummary()
  await fetchDailyCallVolume()
}

const missedCallsCanvas = ref(null)
let missedCallsChart = null

const peakHours = ref([])

const missedCalls = reactive({
  returned: 0,
  pending: 0
})

const missedTotal = computed(() => missedCalls.returned + missedCalls.pending)
const returnedCallsPct = computed(() => {
  const total = missedTotal.value
  if (!total) return 0
  return Math.round((missedCalls.returned / total) * 100)
})
const callbackPendingPct = computed(() => {
  const total = missedTotal.value
  if (!total) return 0
  return Math.round((missedCalls.pending / total) * 100)
})

const callVolumeCanvas = ref(null)
let callVolumeChart = null
const callVolumeView = ref('line')

const dailyCallVolumeLoading = ref(false)
const dailyCallVolume = reactive({
  labels: [],
  datasets: {
    total: [],
    inbound: [],
    outbound: []
  },
  meta: {
    start_date: '',
    end_date: '',
    days: 0,
    avg_per_day: 0
  }
})

const callVolumeSubtitle = computed(() => {
  const days = Number(dailyCallVolume.meta?.days || 0)
  const avg = Number(dailyCallVolume.meta?.avg_per_day || 0)
  const start = dailyCallVolume.meta?.start_date
  const end = dailyCallVolume.meta?.end_date

  if (start && end) return `${start} → ${end} • Avg ${avg} calls/day`
  if (days) return `Last ${days} days • Avg ${avg} calls/day`
  return dailyCallVolumeLoading.value ? 'Loading…' : '—'
})

const refreshData = async () => {
  await fetchDashboardSummary()
  await fetchDailyCallVolume()
}

const resetFilters = async () => {
  closeSimDropdown()
  if (isAdmin.value) filters.organization_id = ''
  if (!isUser.value) {
    filters.department_id = ''
    filters.user_id = ''
  }
  filters.sim_mobile = []
  filters.caller_number = ''

  await fetchDashboardOptions()
  await fetchDashboardSummary()
  await fetchDailyCallVolume()
}

const initCallVolumeChart = () => {
  const canvas = callVolumeCanvas.value
  if (!canvas) return

  if (callVolumeChart) {
    callVolumeChart.destroy()
    callVolumeChart = null
  }

  const ctx = canvas.getContext('2d')
  if (!ctx) return

  const totalStroke = '#0d6efd'
  const inboundStroke = '#0dcaf0'
  const outboundStroke = '#ff7e39'
  const pointBg = '#ffffff'

  const labels = Array.isArray(dailyCallVolume.labels) ? dailyCallVolume.labels : []
  const inbound = Array.isArray(dailyCallVolume?.datasets?.inbound) ? dailyCallVolume.datasets.inbound : []
  const outbound = Array.isArray(dailyCallVolume?.datasets?.outbound) ? dailyCallVolume.datasets.outbound : []
  const total = Array.isArray(dailyCallVolume?.datasets?.total) ? dailyCallVolume.datasets.total : []

  const makeFillGradient = (chart, rgb) => {
    const { ctx, chartArea } = chart
    if (!chartArea) return `rgba(${rgb}, 0.15)`
    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom)
    gradient.addColorStop(0, `rgba(${rgb}, 0.35)`)
    gradient.addColorStop(1, `rgba(${rgb}, 0.00)`)
    return gradient
  }

  const isBar = callVolumeView.value === 'bar'

  const lineDatasets = [
    {
      label: 'Outbound',
      data: outbound,
      borderColor: outboundStroke,
      backgroundColor: 'rgba(255, 126, 57, 0)',
      fill: false,
      cubicInterpolationMode: 'monotone',
      tension: 0.45,
      borderWidth: 3,
      pointRadius: 0,
      pointHoverRadius: 6,
      pointHitRadius: 14,
      pointBackgroundColor: pointBg,
      pointBorderColor: outboundStroke,
      pointBorderWidth: 2,
      pointHoverBorderWidth: 3,
      order: 3
    },
    {
      label: 'Total',
      data: total,
      borderColor: totalStroke,
      backgroundColor: (context) => makeFillGradient(context.chart, '13, 110, 253'),
      fill: true,
      cubicInterpolationMode: 'monotone',
      tension: 0.45,
      borderWidth: 4,
      pointRadius: 0,
      pointHoverRadius: 6,
      pointHitRadius: 14,
      pointBackgroundColor: pointBg,
      pointBorderColor: totalStroke,
      pointBorderWidth: 2,
      pointHoverBorderWidth: 3,
      order: 1
    },
    {
      label: 'Inbound',
      data: inbound,
      borderColor: inboundStroke,
      backgroundColor: (context) => makeFillGradient(context.chart, '13, 202, 240'),
      fill: true,
      cubicInterpolationMode: 'monotone',
      tension: 0.45,
      borderWidth: 3,
      pointRadius: 0,
      pointHoverRadius: 6,
      pointHitRadius: 14,
      pointBackgroundColor: pointBg,
      pointBorderColor: inboundStroke,
      pointBorderWidth: 2,
      pointHoverBorderWidth: 3,
      order: 2
    }
  ]

  const barDatasets = [
    {
      label: 'Outbound',
      data: outbound,
      backgroundColor: 'rgba(255, 126, 57, 0.55)',
      hoverBackgroundColor: 'rgba(255, 126, 57, 0.75)',
      borderColor: 'rgba(255, 126, 57, 0.95)',
      borderWidth: 1,
      borderRadius: 10,
      borderSkipped: false
    },
    {
      label: 'Total',
      data: total,
      backgroundColor: 'rgba(13, 110, 253, 0.35)',
      hoverBackgroundColor: 'rgba(13, 110, 253, 0.55)',
      borderColor: 'rgba(13, 110, 253, 0.90)',
      borderWidth: 1,
      borderRadius: 10,
      borderSkipped: false
    },
    {
      label: 'Inbound',
      data: inbound,
      backgroundColor: 'rgba(13, 202, 240, 0.40)',
      hoverBackgroundColor: 'rgba(13, 202, 240, 0.62)',
      borderColor: 'rgba(13, 202, 240, 0.95)',
      borderWidth: 1,
      borderRadius: 10,
      borderSkipped: false
    }
  ]

  const hoverGuidePlugin = {
    id: 'hoverGuide',
    afterDraw(chart) {
      const active = chart.tooltip?.getActiveElements?.() || []
      if (!active.length) return

      const { ctx, chartArea } = chart
      const x = active[0].element.x
      ctx.save()
      ctx.beginPath()
      ctx.moveTo(x, chartArea.top)
      ctx.lineTo(x, chartArea.bottom)
      ctx.lineWidth = 1
      ctx.strokeStyle = 'rgba(148, 163, 184, 0.65)'
      ctx.stroke()
      ctx.restore()
    }
  }

  callVolumeChart = new Chart(ctx, {
    type: isBar ? 'bar' : 'line',
    plugins: [hoverGuidePlugin],
    data: {
      // API returns date labels like YYYY-MM-DD
      labels,
      datasets: isBar ? barDatasets : lineDatasets
    },
    options: {
      devicePixelRatio: 2,
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      layout: { padding: { top: 6, left: 0, right: 6, bottom: 0 } },
      plugins: {
        legend: {
          display: true,
          position: 'top',
          align: 'end',
          labels: {
            usePointStyle: true,
            pointStyle: 'circle',
            boxWidth: 8,
            boxHeight: 8,
            color: '#64748b',
            font: { size: 12 }
          }
        },
        tooltip: {
          enabled: true,
          backgroundColor: 'rgba(17, 24, 39, 0.95)',
          titleColor: '#ffffff',
          bodyColor: '#e5e7eb',
          padding: 10,
          displayColors: true,
          caretPadding: 8,
          caretSize: 6,
          cornerRadius: 10,
          callbacks: {
            title: (items) => items?.[0]?.label ?? '',
            label: (item) => `${item.dataset.label}: ${item.parsed.y}`
          }
        }
      },
      datasets: isBar
        ? {
            bar: {
              barPercentage: 0.85,
              categoryPercentage: 0.62,
              maxBarThickness: 18
            }
          }
        : undefined,
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: '#98a1b3' },
          border: { display: false },
          stacked: false
        },
        y: {
          beginAtZero: true,
          suggestedMax: Math.max(0, ...(total || [])) + 10,
          grid: {
            color: '#eef0f4',
            borderDash: [4, 4],
            drawTicks: false
          },
          ticks: {
            color: '#98a1b3',
            padding: 6,
            maxTicksLimit: 5
          },
          border: { display: false },
          stacked: false
        }
      }
    }
  })
}

const initMissedCallsChart = () => {
  const canvas = missedCallsCanvas.value
  if (!canvas) return

  if (missedCallsChart) {
    missedCallsChart.destroy()
    missedCallsChart = null
  }

  const ctx = canvas.getContext('2d')
  if (!ctx) return

  const dataValues = [missedCalls.returned, missedCalls.pending]

  missedCallsChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Returned Calls', 'Callback Pending'],
      datasets: [
        {
          data: dataValues,
          backgroundColor: ['#2bb930', '#ffad46'],
          borderColor: '#ffffff',
          borderWidth: 6,
          borderRadius: 14,
          spacing: 2,
          hoverOffset: 6
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '72%',
      plugins: {
        legend: { display: false },
        tooltip: {
          enabled: true,
          backgroundColor: 'rgba(17, 24, 39, 0.95)',
          titleColor: '#ffffff',
          bodyColor: '#e5e7eb',
          padding: 10,
          caretPadding: 8,
          caretSize: 6,
          cornerRadius: 10,
          callbacks: {
            label: (context) => {
              const label = context.label || ''
              const value = context.parsed ?? 0
              const total = dataValues.reduce((sum, v) => sum + (Number(v) || 0), 0)
              const pct = total ? Math.round((value / total) * 100) : 0
              return `${label}: ${value} (${pct}%)`
            }
          }
        }
      }
    }
  })
}

onMounted(async () => {
  // Default to Today
  await selectDatePreset('today', { deferFetch: true })
  await fetchDashboardOptions()
  await fetchDashboardSummary()
  await nextTick()

  if (customRangeInput.value) {
    customRangePicker = flatpickr(customRangeInput.value, {
      mode: 'range',
      dateFormat: 'Y-m-d',
      allowInput: true,
      clickOpens: true,
      maxDate: todayYmd.value,
      defaultDate: customRange.start && customRange.end ? [customRange.start, customRange.end] : null,
      onChange: (selectedDates) => {
        if (suppressCustomRangeOnChange) return
        if (!Array.isArray(selectedDates)) return

        const today = new Date()

        // Cleared
        if (selectedDates.length === 0) {
          customRange.start = ''
          customRange.end = ''
          customRangePicker?.set?.('maxDate', today)
          return
        }

        // First click: constrain selectable end date to (start + 30 days) and never beyond today
        if (selectedDates.length === 1) {
          let start = selectedDates[0]
          if (!start) return
          if (start > today) start = today

          customRange.start = formatYmd(start)
          customRange.end = ''

          const maxAllowed = new Date(start)
          maxAllowed.setDate(maxAllowed.getDate() + (MAX_CUSTOM_RANGE_DAYS - 1))
          if (maxAllowed > today) maxAllowed.setTime(today.getTime())
          customRangePicker?.set?.('maxDate', maxAllowed)
          return
        }

        // Full range picked
        let start = selectedDates[0]
        let end = selectedDates[1]
        if (!start || !end) return

        // Clamp any future start/end (maxDate should block clicks, but manual input may bypass)
        if (start > today) start = today
        if (end > today) end = today

        // Enforce max 1-month window from start
        const maxAllowed = new Date(start)
        maxAllowed.setDate(maxAllowed.getDate() + (MAX_CUSTOM_RANGE_DAYS - 1))
        if (maxAllowed > today) maxAllowed.setTime(today.getTime())
        if (end > maxAllowed) end = maxAllowed

        const newStartYmd = formatYmd(start)
        const newEndYmd = formatYmd(end)
        customRange.start = newStartYmd
        customRange.end = newEndYmd

        // Reset to global "no future" maxDate; per-range limit is applied again on next start selection
        customRangePicker?.set?.('maxDate', today)

        // If we had to clamp, update the picker selection too
        if (formatYmd(selectedDates[1]) !== newEndYmd) {
          suppressCustomRangeOnChange = true
          customRangePicker?.setDate?.([newStartYmd, newEndYmd], false)
          suppressCustomRangeOnChange = false
        }
      }
    })
  }

  dateRangeModalEl = document.getElementById('dashboardDateRangeModal')
  if (dateRangeModalEl) {
    onDateRangeModalShown = () => {
      customRangePicker?.open?.()
    }
    dateRangeModalEl.addEventListener('shown.bs.modal', onDateRangeModalShown)
  }

  document.addEventListener('mousedown', onDocumentMouseDown)

  await fetchDailyCallVolume()
  initMissedCallsChart()
})

watch(callVolumeView, async () => {
  await nextTick()
  initCallVolumeChart()
})

onBeforeUnmount(() => {
  if (callVolumeChart) {
    callVolumeChart.destroy()
    callVolumeChart = null
  }
  if (missedCallsChart) {
    missedCallsChart.destroy()
    missedCallsChart = null
  }

  if (dateRangeModalEl && onDateRangeModalShown) {
    dateRangeModalEl.removeEventListener('shown.bs.modal', onDateRangeModalShown)
  }
  dateRangeModalEl = null
  onDateRangeModalShown = null

  if (customRangePicker) {
    customRangePicker.destroy()
    customRangePicker = null
  }

  document.removeEventListener('mousedown', onDocumentMouseDown)
})
</script>

<style scoped>
.dashboard-page {
  /* Single fixed spacing used between ALL consecutive dashboard sections */
  --dash-section-gap: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: var(--dash-section-gap);
}

/* Chart sizing: make it feel balanced on mobile */
.chart-container {
  height: 260px;
}

.missed-donut {
  width: 180px;
  height: 180px;
}

.loading-overlay {
  z-index: 2;
}

@media (max-width: 575.98px) {
  .missed-donut {
    width: 160px;
    height: 160px;
  }
}

@media (max-width: 575.98px) {
  .chart-container {
    height: 210px;
  }
}

.bg-orange-gradient {
  background: linear-gradient(135deg, #ff9a44 0%, #ff6a00 100%) !important;
}
.bg-success-light {
  background-color: rgba(43, 185, 48, 0.1) !important;
}
.bg-warning-light {
  background-color: rgba(255, 173, 70, 0.1) !important;
}
.bg-danger-light {
  background-color: rgba(242, 89, 97, 0.1) !important;
}
.bg-info-light {
  background-color: rgba(72, 171, 247, 0.1) !important;
}
.text-orange {
  color: #ff6a00 !important;
}
.bg-orange {
  background-color: #ff6a00 !important;
}
.dot {
  height: 12px;
  width: 12px;
  border-radius: 50%;
  display: inline-block;
}
.icon-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 46px;
  height: 46px;
  box-sizing: border-box;
}
.icon-box i {
  font-size: 1.25rem;
}
.card-round {
  border-radius: 16px;
}
.shadow-sm {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}
.shadow-lg {
  box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
}
.btn-white {
  background-color: #fff;
  color: #333;
}

/* Fix: global .card-round has overflow:hidden (in app.css), which clips dropdowns inside modals */
#dashboardFiltersModal .modal-content.card-round,
#dashboardDateRangeModal .modal-content.card-round {
  overflow: visible;
}

#dashboardFiltersModal .modal-body,
#dashboardDateRangeModal .modal-body {
  overflow: visible;
}

/* Dashboard filter modal multiselect */
.multiselect-wrapper {
  position: relative;
}

.multiselect-wrapper.is-disabled {
  opacity: 0.7;
  pointer-events: none;
}

.multiselect-trigger {
  height: calc(1.5em + 0.75rem + 2px);
  padding: 0.375rem 0.75rem;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
}

.multiselect-trigger:focus {
  outline: none;
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.multiselect-trigger .ms-placeholder {
  color: #6c757d;
  background: transparent;
  opacity: 1;
}

.multiselect-trigger .selected-count {
  color: #212529;
  font-weight: 600;
}

.multiselect-trigger .dropdown-arrow {
  color: #6c757d;
  font-size: 0.85rem;
}

.multiselect-menu {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 0;
  max-height: 260px;
  overflow: auto;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
  z-index: 1080;
  padding: 8px;
}

.multiselect-header {
  font-weight: 700;
  font-size: 0.9rem;
  color: #111827;
  padding: 6px 10px;
}

.multiselect-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  border-radius: 10px;
  cursor: pointer;
}

.multiselect-option:hover {
  background: rgba(13, 110, 253, 0.06);
}

.multiselect-option input {
  pointer-events: none;
}

.multiselect-option label {
  margin: 0;
  cursor: pointer;
  color: #111827;
  font-size: 0.95rem;
  line-height: 1.2;
}
</style>
