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

    <!-- Subscription expiry warning banner -->
    <div v-if="showExpiryBanner" class="expiry-warning-banner" role="alert">
      <div class="expiry-warning-inner">
        <i class="fas fa-exclamation-triangle expiry-warning-icon" aria-hidden="true"></i>
        <div class="expiry-warning-text">
          <strong>Your plan expires {{ expiryBannerLabel }}!</strong>
          <span>Auto-renewal is off. Renew now to avoid service interruption.</span>
        </div>
        <router-link to="/subscription/renew" class="expiry-warning-btn">Renew Now</router-link>
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
                <label class="form-label small text-muted">Team</label>
                <select
                  class="form-select"
                  v-model="filters.team_id"
                  @change="onTeamChange"
                  :disabled="optionsLoading || (isAdmin && !filters.organization_id)"
                >
                  <option value="">All Teams</option>
                  <option v-if="optionsLoading" value="" disabled>Loading...</option>
                  <option v-for="dept in dashboardOptions.teams" :key="dept.id" :value="String(dept.id)">
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
                  :disabled="optionsLoading || (!filters.team_id && dashboardOptions.users.length === 0)"
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
                class="badge rounded-pill px-2 py-1 fw-bold border"
                :class="
                  (dashboardSummary.total_calls_change_pct ?? 0) >= 0
                    ? 'bg-white bg-opacity-25 text-white border-white'
                    : 'bg-white bg-opacity-100 text-danger border-danger'
                "
              >
                {{ formatChangePct(dashboardSummary.total_calls_change_pct) }}
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
                    {{ formatChangePct(dashboardSummary.answer_rate_change_pct) }}
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
                    {{ formatChangePct(dashboardSummary.avg_duration_change_pct) }}
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
                {{ formatChangePct(dashboardSummary.outbound_calls_change_pct) }}
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
                {{ formatChangePct(dashboardSummary.inbound_calls_change_pct) }}
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
                {{ formatChangePct(dashboardSummary.missed_calls_change_pct) }}
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
                {{ formatChangePct(dashboardSummary.unique_calls_change_pct) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Third Row: Daily Call Volume -->
    <div class="row mb-0">
      <!-- Daily Call Volume (Line Chart Style) -->
      <div class="col-md-12">
        <div class="card card-round shadow-sm border-0 daily-chart-card">
          <div class="card-header bg-transparent border-0 pt-4 px-4">
            <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap chart-header-wrap">
              <div>
                <h5 class="fw-bold mb-0">Daily Call Volume</h5>
                <p class="text-muted small mb-1">{{ callVolumeSubtitle }}</p>
                <p class="chart-insight mb-0 small">{{ callVolumeInsight }}</p>
              </div>
              <div class="d-flex align-items-center gap-2 flex-wrap chart-controls">
                <div class="btn-group btn-group-sm flex-shrink-0 view-toggle" role="group" aria-label="Dataset filter">
                  <button
                    type="button"
                    class="btn"
                    :class="callVolumeDatasetFilter === 'all' ? 'btn-primary' : 'btn-outline-primary'"
                    @click="callVolumeDatasetFilter = 'all'"
                  >
                    All
                  </button>
                  <button
                    type="button"
                    class="btn"
                    :class="callVolumeDatasetFilter === 'inbound' ? 'btn-primary' : 'btn-outline-primary'"
                    @click="callVolumeDatasetFilter = 'inbound'"
                  >
                    Inbound
                  </button>
                  <button
                    type="button"
                    class="btn"
                    :class="callVolumeDatasetFilter === 'outbound' ? 'btn-primary' : 'btn-outline-primary'"
                    @click="callVolumeDatasetFilter = 'outbound'"
                  >
                    Outbound
                  </button>
                </div>
                <div class="btn-group btn-group-sm flex-shrink-0 view-toggle" role="group" aria-label="Chart type">
                  <button
                    type="button"
                    class="btn"
                    :class="callVolumeView === 'bar' ? 'btn-primary' : 'btn-outline-primary'"
                    @click="callVolumeView = 'bar'"
                  >
                    Bar
                  </button>
                  <button
                    type="button"
                    class="btn"
                    :class="callVolumeView === 'line' ? 'btn-primary' : 'btn-outline-primary'"
                    @click="callVolumeView = 'line'"
                  >
                    Line
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div class="card-body px-4 pb-4">
            <div class="chart-container position-relative daily-chart-surface">
              <div
                v-if="dailyCallVolumeLoading"
                class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75 loading-overlay"
              >
                <div class="text-center">
                  <div class="spinner-border text-primary" role="status" aria-label="Loading"></div>
                </div>
              </div>
              <div v-if="!dailyCallVolumeLoading && isDailyCallVolumeEmpty" class="daily-empty-state">
                <div class="daily-empty-icon" aria-hidden="true">
                  <i class="far fa-envelope-open"></i>
                </div>
                <h6 class="daily-empty-title mb-1">No Call Data Available</h6>
                <p class="daily-empty-subtitle mb-3">No calls were recorded during this period.</p>
                <div class="d-flex gap-2 flex-wrap justify-content-center">
                  <button type="button" class="btn btn-outline-primary btn-sm" @click="viewCallLogs">
                    View Call Logs
                  </button>
                  <button type="button" class="btn btn-primary btn-sm" @click="refreshData">
                    Refresh
                  </button>
                </div>
              </div>
              <canvas v-else ref="callVolumeCanvas" class="w-100 h-100" aria-label="Daily Call Volume" role="img"></canvas>
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
                    class="icon-box peak-hour-icon-wrap rounded-3 p-2 me-3"
                    :class="index === 0 ? 'peak-hour-icon-wrap--primary' : 'peak-hour-icon-wrap--secondary'"
                  >
                    <i
                      class="fas peak-hour-icon"
                      :class="index === 0 ? 'fa-sun peak-hour-icon--primary' : 'fa-clock peak-hour-icon--secondary'"
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
        <div class="card card-round shadow-sm border-0 missed-analysis-card">
          <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h5 class="fw-bold mb-0">Missed Calls Analysis</h5>
            <p class="text-muted small">Total missed calls breakdown</p>
          </div>
          <div class="card-body p-4">
            <div class="row align-items-center missed-analysis-layout">
              <div class="col-md-5 text-center mb-4 mb-md-0">
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
                  <div class="position-absolute top-50 start-50 translate-middle text-center missed-center">
                    <h2 class="fw-bold mb-0">{{ missedTotal }}</h2>
                    <p class="text-muted small mb-0">Total Missed</p>
                  </div>
                </div>
              </div>
              <div class="col-md-7">
                <div class="card bg-success-light border-0 mb-3 card-round missed-stat-item">
                  <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center missed-stat-head">
                      <div class="d-flex align-items-center">
                        <div class="dot bg-success me-3"></div>
                        <div>
                          <h6 class="fw-bold mb-0">Returned Calls</h6>
                          <p class="text-muted small mb-0">{{ returnedCallsPct }}% of total</p>
                        </div>
                      </div>
                      <h4 class="fw-bold text-success mb-0">{{ missedCalls.returned }}</h4>
                    </div>
                    <div class="progress rounded-pill missed-progress mt-3">
                      <div class="progress-bar bg-success" :style="{ width: `${returnedCallsPct}%` }"></div>
                    </div>
                  </div>
                </div>
                <div class="card bg-pending-light border-0 card-round missed-stat-item missed-stat-item-pending">
                  <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center missed-stat-head">
                      <div class="d-flex align-items-center">
                        <div class="dot bg-pending me-3"></div>
                        <div>
                          <h6 class="fw-bold mb-0">Callback Pending</h6>
                          <p class="text-muted small mb-0">{{ callbackPendingPct }}% of total</p>
                        </div>
                      </div>
                      <h4 class="fw-bold text-pending mb-0">{{ missedCalls.pending }}</h4>
                    </div>
                    <div class="progress rounded-pill missed-progress mt-3">
                      <div class="progress-bar bg-pending" :style="{ width: `${callbackPendingPct}%` }"></div>
                    </div>
                  </div>
                </div>
                <div class="missed-summary-strip mt-3" :class="{ 'is-alert': callbackPendingPct >= 70 }">
                  <span class="missed-summary-label">Insight</span>
                  <span class="missed-summary-value">{{ missedInsightText }}</span>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm mt-3 missed-cta-btn" @click="viewMissedCalls">
                  View Missed Calls
                </button>
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
import { useRouter } from 'vue-router'
import Chart from 'chart.js/auto'
import flatpickr from 'flatpickr'
import 'flatpickr/dist/flatpickr.min.css'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { formatDateDisplay, formatShortDateDisplay } from '@/utils/dateFormatter'

const authStore = useAuthStore()
const router = useRouter()

const isAdmin = computed(() => authStore.userRole === 'admin')
const isUser = computed(() => authStore.userRole === 'user')

const subscriptionStats = ref(null)

const showExpiryBanner = computed(() => {
  const s = subscriptionStats.value
  if (!s) return false
  if (s.auto_renew !== false) return false
  if (s.subscription_status !== 'active') return false
  const days = s.days_until_expiry
  if (days === null || days === undefined) return false
  const threshold = s.renewal_days_before ?? 2
  return days >= 0 && days <= threshold
})

const expiryBannerLabel = computed(() => {
  const days = subscriptionStats.value?.days_until_expiry
  if (days === 0) return 'today'
  if (days === 1) return 'tomorrow'
  return `in ${days} day${days === 1 ? '' : 's'}`
})

const formatChangePct = (pct) => {
  const n = Number(pct ?? 0)
  const abs = Math.abs(Math.round(n))

  if (!Number.isFinite(n) || abs === 0) return '0%'
  return `${n > 0 ? '↑' : '↓'} ${abs}%`
}

const optionsLoading = ref(false)
const summaryLoading = ref(false)

const dashboardOptions = reactive({
  organizations: [],
  teams: [],
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
  team_id: '',
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

const formatChartDateLabel = (value) => {
  if (!value) return ''
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return String(value)
  return formatShortDateDisplay(d, String(value))
}

const getDayOverDayChange = (arr, index) => {
  if (!Array.isArray(arr) || index <= 0) return null
  const current = Number(arr[index] || 0)
  const previous = Number(arr[index - 1] || 0)
  if (!Number.isFinite(current) || !Number.isFinite(previous)) return null

  if (previous === 0 && current > 0) {
    return { type: 'new', value: null, previous, current }
  }

  if (previous === 0 && current === 0) {
    return { type: 'zero', value: 0, previous, current }
  }

  const value = Math.round(((current - previous) / previous) * 100)
  return { type: 'percent', value, previous, current }
}

const formatTrendLabel = (change) => {
  if (!change) return 'n/a'
  if (change.type === 'new') return 'New'
  if (change.type === 'zero') return '0%'
  if (!Number.isFinite(change.value)) return 'n/a'
  if (change.value > 0) return `\u2191 +${Math.abs(change.value)}%`
  if (change.value < 0) return `\u2193 -${Math.abs(change.value)}%`
  return '0%'
}

const formatChangeWithPercent = (change) => {
  if (!change) return 'n/a'
  if (change.type === 'new') return `+${Math.abs(Number(change.current || 0))} calls (New)`
  if (change.type === 'zero') return '0 calls (0%)'

  const delta = Number(change.current || 0) - Number(change.previous || 0)
  if (!Number.isFinite(delta) || !Number.isFinite(change.value)) return 'n/a'

  if (delta > 0) return `+${Math.abs(delta)} calls (+${Math.abs(change.value)}%)`
  if (delta < 0) return `-${Math.abs(delta)} calls (-${Math.abs(change.value)}%)`
  return '0 calls (0%)'
}

const formatCompactNumber = (value) => {
  const n = Number(value || 0)
  if (!Number.isFinite(n)) return '0'

  const abs = Math.abs(n)
  if (abs >= 1000000) {
    const v = (n / 1000000).toFixed(1).replace(/\.0$/, '')
    return `${v}M`
  }
  if (abs >= 1000) {
    const v = (n / 1000).toFixed(1).replace(/\.0$/, '')
    return `${v}K`
  }
  return `${Math.round(n)}`
}

const formatChangeAbsoluteCompact = (change) => {
  if (!change) return 'n/a'
  if (change.type === 'new') return `+${formatCompactNumber(Math.abs(Number(change.current || 0)))}`
  if (change.type === 'zero') return '0'

  const delta = Number(change.current || 0) - Number(change.previous || 0)
  if (!Number.isFinite(delta)) return 'n/a'
  if (delta > 0) return `+${formatCompactNumber(Math.abs(delta))}`
  if (delta < 0) return `-${formatCompactNumber(Math.abs(delta))}`
  return '0'
}

const formatGrowthPercentOnly = (change) => {
  if (!change) return 'n/a'
  if (change.type === 'new') return 'New'
  if (change.type === 'zero') return '0%'
  if (!Number.isFinite(change.value)) return 'n/a'
  if (change.value > 0) return `+${Math.abs(change.value)}%`
  if (change.value < 0) return `-${Math.abs(change.value)}%`
  return '0%'
}

const formatChangeInsightText = (change) => {
  if (!change) return 'Change data is unavailable.'
  if (change.type === 'new') return `Calls increased by ${Math.abs(Number(change.current || 0))} (new activity).`
  if (change.type === 'zero') return 'Calls were flat day-over-day (0%).'

  const delta = Number(change.current || 0) - Number(change.previous || 0)
  if (!Number.isFinite(delta) || !Number.isFinite(change.value)) return 'Change data is unavailable.'
  if (delta > 0) return `Calls increased by ${Math.abs(delta)} (+${Math.abs(change.value)}%).`
  if (delta < 0) return `Calls decreased by ${Math.abs(delta)} (-${Math.abs(change.value)}%).`
  return 'Calls were flat day-over-day (0%).'
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
  if (!isUser.value && filters.team_id) params.team_id = filters.team_id
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

const fetchSubscriptionStats = async () => {
  if (isAdmin.value) return
  try {
    const resp = await api.get('/subscription/stats')
    subscriptionStats.value = resp.data?.data ?? null
  } catch {
    // non-fatal — banner simply won't show
  }
}

const fetchDashboardOptions = async () => {
  optionsLoading.value = true
  try {
    const resp = await api.get('/dashboard/options', { params: buildDashboardParams(true) })
    dashboardOptions.organizations = resp.data.organizations || []
    dashboardOptions.teams = resp.data.teams || []
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
  filters.team_id = ''
  filters.user_id = ''
  filters.sim_mobile = []
  dashboardOptions.teams = []
  dashboardOptions.users = []
  dashboardOptions.sims = []
  await fetchDashboardOptions()
}

const onTeamChange = async () => {
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
const callVolumeView = ref('bar')
const callVolumeDatasetFilter = ref('all')

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

  if (start && end) return `${formatDateDisplay(start, start)} to ${formatDateDisplay(end, end)} | Avg ${avg} calls/day`
  if (days) return `Last ${days} days | Avg ${avg} calls/day`
  return dailyCallVolumeLoading.value ? 'Loading...' : '-'
})

const hasNonZeroValue = (series) => Array.isArray(series) && series.some((v) => Number(v || 0) > 0)

const isDailyCallVolumeEmpty = computed(() => {
  const labels = Array.isArray(dailyCallVolume.labels) ? dailyCallVolume.labels : []
  const total = Array.isArray(dailyCallVolume?.datasets?.total) ? dailyCallVolume.datasets.total : []
  const inbound = Array.isArray(dailyCallVolume?.datasets?.inbound) ? dailyCallVolume.datasets.inbound : []
  const outbound = Array.isArray(dailyCallVolume?.datasets?.outbound) ? dailyCallVolume.datasets.outbound : []

  if (!labels.length) return true
  if (callVolumeDatasetFilter.value === 'inbound') return !hasNonZeroValue(inbound)
  if (callVolumeDatasetFilter.value === 'outbound') return !hasNonZeroValue(outbound)
  return !(hasNonZeroValue(total) || hasNonZeroValue(inbound) || hasNonZeroValue(outbound))
})

const callVolumeInsight = computed(() => {
  if (isDailyCallVolumeEmpty.value) return 'No call activity detected for this period.'

  const labels = Array.isArray(dailyCallVolume.labels) ? dailyCallVolume.labels : []
  const total = Array.isArray(dailyCallVolume?.datasets?.total) ? dailyCallVolume.datasets.total : []
  if (!labels.length || !total.length) return 'No trend insight available for selected range.'

  let peakIndex = 0
  for (let i = 1; i < total.length; i += 1) {
    if (Number(total[i] || 0) > Number(total[peakIndex] || 0)) peakIndex = i
  }

  const peakDate = formatChartDateLabel(labels[peakIndex])
  const peakValue = Number(total[peakIndex] || 0)
  const change = getDayOverDayChange(total, peakIndex)
  const changeText = formatChangeInsightText(change)
  const mid = Math.floor(total.length / 2)
  const firstHalf = total.slice(0, Math.max(1, mid))
  const secondHalf = total.slice(Math.max(1, mid))
  const avgFirst = firstHalf.length ? firstHalf.reduce((a, b) => a + Number(b || 0), 0) / firstHalf.length : 0
  const avgSecond = secondHalf.length ? secondHalf.reduce((a, b) => a + Number(b || 0), 0) / secondHalf.length : 0

  let weeklyTone = 'Momentum stayed stable through the week.'
  if (avgSecond > avgFirst * 1.1) weeklyTone = 'Growth accelerated after mid-week.'
  if (avgSecond < avgFirst * 0.9) weeklyTone = 'Volume softened after mid-week.'

  return `Calls peaked on ${peakDate} (${peakValue} calls). ${changeText} ${weeklyTone}`
})

const missedInsightText = computed(() => {
  if (!missedTotal.value) return 'No missed calls in selected period.'
  if (callbackPendingPct.value >= 90) return `${callbackPendingPct.value}% missed calls are pending follow-up (high priority).`
  if (callbackPendingPct.value >= 70) return `${callbackPendingPct.value}% missed calls are still pending callbacks.`
  if (callbackPendingPct.value >= 40) return `${callbackPendingPct.value}% pending callbacks need follow-up.`
  return `Good coverage: ${returnedCallsPct.value}% missed calls are already returned.`
})

const viewMissedCalls = () => {
  router.push({
    path: '/call-reports',
    query: {
      call_type: 'inbound',
      call_status: 'Missed',
      call_back: 'N'
    }
  })
}

const viewCallLogs = () => {
  router.push('/call-reports')
}

const refreshData = async () => {
  await fetchDashboardSummary()
  await fetchDailyCallVolume()
}

const resetFilters = async () => {
  closeSimDropdown()
  if (isAdmin.value) filters.organization_id = ''
  if (!isUser.value) {
    filters.team_id = ''
    filters.user_id = ''
  }
  filters.sim_mobile = []
  filters.caller_number = ''

  await fetchDashboardOptions()
  await fetchDashboardSummary()
  await fetchDailyCallVolume()
}

const initCallVolumeChart = () => {
  if (callVolumeChart) {
    callVolumeChart.destroy()
    callVolumeChart = null
  }

  if (isDailyCallVolumeEmpty.value) return

  const canvas = callVolumeCanvas.value
  if (!canvas) return

  const ctx = canvas.getContext('2d')
  if (!ctx) return

  const labels = Array.isArray(dailyCallVolume.labels) ? dailyCallVolume.labels : []
  const inbound = Array.isArray(dailyCallVolume?.datasets?.inbound) ? dailyCallVolume.datasets.inbound : []
  const outbound = Array.isArray(dailyCallVolume?.datasets?.outbound) ? dailyCallVolume.datasets.outbound : []
  const total = Array.isArray(dailyCallVolume?.datasets?.total) ? dailyCallVolume.datasets.total : []

  const totalColor = 'rgba(53, 89, 184, 1)'
  const totalColorSoft = 'rgba(53, 89, 184, 0.22)'
  const inboundColor = 'rgba(34, 139, 91, 1)'
  const inboundColorSoft = 'rgba(34, 139, 91, 0.22)'
  const outboundColor = 'rgba(224, 139, 71, 1)'
  const outboundColorSoft = 'rgba(224, 139, 71, 0.22)'

  const isBar = callVolumeView.value === 'bar'
  const filter = callVolumeDatasetFilter.value
  const showAll = filter === 'all'
  const showInbound = showAll || filter === 'inbound'
  const showOutbound = showAll || filter === 'outbound'
  const showTotal = showAll
  const singleDatasetMode = !showAll

  const datasets = isBar
    ? [
        showOutbound
          ? {
              label: 'Outbound',
              data: outbound,
              backgroundColor: 'rgba(224, 139, 71, 0.64)',
              hoverBackgroundColor: 'rgba(224, 139, 71, 0.84)',
              borderColor: 'rgba(224, 139, 71, 0.95)',
              borderWidth: 1,
              borderRadius: { topLeft: 2, topRight: 2, bottomLeft: 0, bottomRight: 0 },
              borderSkipped: 'bottom',
              categoryPercentage: singleDatasetMode ? 0.72 : 0.56,
              barPercentage: singleDatasetMode ? 0.9 : 0.56,
              maxBarThickness: singleDatasetMode ? 34 : 16
            }
          : null,
        showTotal
          ? {
              label: 'Total',
              data: total,
              backgroundColor: 'rgba(53, 89, 184, 0.92)',
              hoverBackgroundColor: 'rgba(53, 89, 184, 1)',
              borderColor: 'rgba(48, 78, 163, 1)',
              borderWidth: 1.2,
              borderRadius: { topLeft: 3, topRight: 3, bottomLeft: 0, bottomRight: 0 },
              borderSkipped: 'bottom',
              categoryPercentage: 0.56,
              barPercentage: 0.8,
              maxBarThickness: 24
            }
          : null,
        showInbound
          ? {
              label: 'Inbound',
              data: inbound,
              backgroundColor: 'rgba(34, 139, 91, 0.64)',
              hoverBackgroundColor: 'rgba(34, 139, 91, 0.84)',
              borderColor: 'rgba(34, 139, 91, 0.95)',
              borderWidth: 1,
              borderRadius: { topLeft: 2, topRight: 2, bottomLeft: 0, bottomRight: 0 },
              borderSkipped: 'bottom',
              categoryPercentage: singleDatasetMode ? 0.72 : 0.56,
              barPercentage: singleDatasetMode ? 0.9 : 0.56,
              maxBarThickness: singleDatasetMode ? 34 : 16
            }
          : null
      ].filter(Boolean)
    : [
        showOutbound
          ? {
              label: 'Outbound',
              data: outbound,
              borderColor: outboundColor,
              backgroundColor: outboundColorSoft,
              _baseColor: outboundColor,
              _mutedColor: 'rgba(224, 139, 71, 0.24)',
              tension: 0.35,
              borderWidth: 2,
              pointRadius: 2.5,
              pointHoverRadius: 4.5,
              fill: false
            }
          : null,
        showTotal
          ? {
              label: 'Total',
              data: total,
              borderColor: totalColor,
              backgroundColor: totalColorSoft,
              _baseColor: totalColor,
              _mutedColor: 'rgba(53, 89, 184, 0.24)',
              tension: 0.35,
              borderWidth: 3,
              pointRadius: 3,
              pointHoverRadius: 5.5,
              fill: false
            }
          : null,
        showInbound
          ? {
              label: 'Inbound',
              data: inbound,
              borderColor: inboundColor,
              backgroundColor: inboundColorSoft,
              _baseColor: inboundColor,
              _mutedColor: 'rgba(34, 139, 91, 0.24)',
              tension: 0.35,
              borderWidth: 2,
              pointRadius: 2.5,
              pointHoverRadius: 4.5,
              fill: false
            }
          : null
      ].filter(Boolean)

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
      ctx.strokeStyle = 'rgba(100, 116, 139, 0.25)'
      ctx.setLineDash([5, 4])
      ctx.stroke()
      ctx.restore()
    }
  }

  const totalLabelsAndTrendPlugin = {
    id: 'totalLabelsAndTrend',
    afterDatasetsDraw(chart) {
      if (chart.config.type !== 'bar') return
      const { ctx } = chart
      ctx.save()
      ctx.textAlign = 'center'

      const datasetIndexes = showAll
        ? [chart.data.datasets.findIndex((d) => d.label === 'Total')].filter((idx) => idx >= 0)
        : chart.data.datasets.map((_, idx) => idx)

      datasetIndexes.forEach((datasetIndex) => {
        const meta = chart.getDatasetMeta(datasetIndex)
        const data = chart.data.datasets[datasetIndex]?.data || []
        if (!meta || meta.hidden) return

        meta.data.forEach((bar, index) => {
          const value = Number(data[index] || 0)
          const change = getDayOverDayChange(data, index)
          const valueY = Math.max(12, bar.y - 6)
          const changeY = Math.max(10, bar.y - 20)

          ctx.font = '600 10px system-ui, -apple-system, Segoe UI, Roboto, sans-serif'
          ctx.fillStyle = '#334155'
          ctx.fillText(formatCompactNumber(value), bar.x, valueY)

          if (index > 0 && Math.abs(valueY - changeY) > 8) {
            ctx.font = '700 10px system-ui, -apple-system, Segoe UI, Roboto, sans-serif'
            ctx.fillStyle = change?.type === 'percent'
              ? (change.value > 0 ? '#16a34a' : change.value < 0 ? '#dc2626' : '#64748b')
              : (change?.type === 'new' ? '#16a34a' : '#64748b')
            ctx.fillText(formatChangeAbsoluteCompact(change), bar.x, changeY)
          }
        })
      })

      ctx.restore()
    }
  }

  callVolumeChart = new Chart(ctx, {
    type: isBar ? 'bar' : 'line',
    plugins: [hoverGuidePlugin, totalLabelsAndTrendPlugin],
    data: {
      labels,
      datasets
    },
    options: {
      devicePixelRatio: 2,
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      animation: { duration: 850, easing: 'easeOutCubic' },
      layout: { padding: { top: 8, left: 4, right: 8, bottom: 0 } },
      plugins: {
        legend: {
          display: true,
          position: 'top',
          align: 'start',
          onHover: (event, legendItem, legend) => {
            legend.chart.canvas.style.cursor = 'pointer'
            if (isBar) return
            const chart = legend.chart
            chart.data.datasets.forEach((ds, idx) => {
              const active = idx === legendItem.datasetIndex
              ds.borderColor = active ? ds._baseColor : ds._mutedColor
              ds.borderWidth = active ? 3.5 : 1.6
              ds.pointRadius = active ? 3.5 : 2
              ds.pointHoverRadius = active ? 6 : 3.5
            })
            chart.update('none')
          },
          onLeave: (event, legendItem, legend) => {
            legend.chart.canvas.style.cursor = 'default'
            if (isBar) return
            const chart = legend.chart
            chart.data.datasets.forEach((ds) => {
              ds.borderColor = ds._baseColor || ds.borderColor
              ds.borderWidth = ds.label === 'Total' ? 3 : 2
              ds.pointRadius = ds.label === 'Total' ? 3 : 2.5
              ds.pointHoverRadius = ds.label === 'Total' ? 5.5 : 4.5
            })
            chart.update('none')
          },
          labels: {
            usePointStyle: true,
            pointStyle: 'circle',
            boxWidth: 8,
            boxHeight: 8,
            color: '#334155',
            font: { size: 12, weight: '600' },
            padding: 14
          }
        },
        tooltip: {
          enabled: true,
          backgroundColor: 'rgba(15, 23, 42, 0.96)',
          titleColor: '#ffffff',
          bodyColor: '#e2e8f0',
          padding: 12,
          displayColors: false,
          cornerRadius: 10,
          borderWidth: 1,
          borderColor: 'rgba(148, 163, 184, 0.26)',
          callbacks: {
            title: (items) => formatChartDateLabel(items?.[0]?.label ?? ''),
            label: () => null,
            afterBody: (items) => {
              const idx = items?.[0]?.dataIndex
              if (idx === undefined || idx === null) return []
              const totalVal = Number(total[idx] || 0)
              const inboundVal = Number(inbound[idx] || 0)
              const outboundVal = Number(outbound[idx] || 0)
              const change = getDayOverDayChange(total, idx)
              return [
                `Total Calls: ${formatCompactNumber(totalVal)} (${totalVal})`,
                `Inbound Calls: ${formatCompactNumber(inboundVal)} (${inboundVal})`,
                `Outbound Calls: ${formatCompactNumber(outboundVal)} (${outboundVal})`,
                `Absolute change: ${formatChangeAbsoluteCompact(change)}`,
                `Growth: ${formatGrowthPercentOnly(change)} vs previous day`
              ]
            }
          }
        }
      },
      scales: {
        x: {
          offset: true,
          grid: { display: false },
          ticks: {
            color: '#64748b',
            font: { size: 11, weight: '500' },
            autoSkip: true,
            maxTicksLimit: 8,
            callback: (value, index) => formatChartDateLabel(labels[index])
          },
          border: { display: false }
        },
        y: {
          beginAtZero: true,
          suggestedMax: Math.max(0, ...(total || [])) + 6,
          grid: {
            color: 'rgba(148, 163, 184, 0.16)',
            drawTicks: false,
            lineWidth: 1
          },
          ticks: {
            color: '#64748b',
            padding: 8,
            maxTicksLimit: 5,
            font: { size: 11, weight: '500' }
          },
          border: { display: false }
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
          backgroundColor: ['#16a34a', '#ea580c'],
          borderColor: '#ffffff',
          borderWidth: 5,
          borderRadius: 16,
          spacing: 3,
          hoverOffset: 6
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '76%',
      plugins: {
        legend: { display: false },
        tooltip: {
          enabled: true,
          backgroundColor: 'rgba(17, 24, 39, 0.96)',
          titleColor: '#ffffff',
          bodyColor: '#ffffff',
          padding: 12,
          caretPadding: 10,
          caretSize: 7,
          cornerRadius: 12,
          borderWidth: 1,
          borderColor: 'rgba(255, 255, 255, 0.1)',
          titleFont: {
            size: 14,
            weight: '600'
          },
          bodyFont: {
            size: 13,
            weight: '500'
          },
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
  fetchSubscriptionStats()
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

watch([callVolumeView, callVolumeDatasetFilter], async () => {
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
  height: 320px;
  border-radius: 14px;
  border: 1px solid #e6edf7;
  background: linear-gradient(180deg, #fbfdff 0%, #f2f6fc 100%);
  padding: 10px;
}

.daily-chart-card .card-header {
  padding-bottom: 0.35rem;
}

.chart-header-wrap {
  row-gap: 0.75rem !important;
}

.chart-controls {
  justify-content: flex-end;
}

.chart-insight {
  color: #475569;
  font-weight: 600;
}

.view-toggle {
  background: #edf2f7;
  border-radius: 12px;
  padding: 3px;
}

.view-toggle .btn {
  border-radius: 8px !important;
  border: none !important;
  min-width: 68px;
  font-weight: 700;
}

.view-toggle .btn.btn-primary {
  background: #3559b8;
}

.view-toggle .btn.btn-outline-primary {
  color: #334155;
  background: transparent;
}

.daily-chart-surface {
  background:
    radial-gradient(circle at 0% 0%, rgba(53, 89, 184, 0.09), transparent 30%),
    linear-gradient(180deg, #fbfdff 0%, #f2f6fc 100%);
}

.daily-empty-state {
  height: 100%;
  min-height: 240px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding: 1rem;
  border-radius: 10px;
  background:
    linear-gradient(180deg, rgba(148, 163, 184, 0.06), rgba(148, 163, 184, 0.02)),
    repeating-linear-gradient(
      to bottom,
      transparent 0,
      transparent 35px,
      rgba(148, 163, 184, 0.16) 35px,
      rgba(148, 163, 184, 0.16) 36px
    );
}

.daily-empty-icon {
  width: 54px;
  height: 54px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  color: #475569;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  margin-bottom: 0.65rem;
}

.daily-empty-title {
  font-size: 1.08rem;
  font-weight: 700;
  color: #0f172a;
}

.daily-empty-subtitle {
  font-size: 0.92rem;
  color: #64748b;
}

.missed-donut {
  width: 200px;
  height: 200px;
  filter: drop-shadow(0 8px 14px rgba(15, 23, 42, 0.1));
}

.missed-center h2 {
  font-size: 2.2rem;
}

.missed-center p {
  color: #64748b !important;
}

.missed-analysis-card .card-header {
  padding-bottom: 0.35rem;
}

.missed-analysis-layout {
  row-gap: 0.8rem;
}

.missed-stat-item {
  border-radius: 14px !important;
  border: 1px solid rgba(148, 163, 184, 0.26) !important;
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
}

.missed-stat-item h4 {
  font-size: 1.45rem;
}

.missed-progress {
  height: 8px;
  background: rgba(148, 163, 184, 0.24);
}

.missed-progress .progress-bar {
  border-radius: 999px;
}

.bg-pending-light {
  background-color: rgba(234, 88, 12, 0.12) !important;
}

.bg-pending {
  background-color: #ea580c !important;
}

.text-pending {
  color: #ea580c !important;
}

.missed-summary-strip {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.8rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 0.6rem 0.8rem;
}

.missed-summary-strip.is-alert {
  background: #fff7ed;
  border-color: #fdba74;
}

.missed-summary-label {
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #64748b;
}

.missed-summary-value {
  font-size: 0.9rem;
  font-weight: 700;
  color: #0f172a;
}

.missed-cta-btn {
  border-color: #cbd5e1;
  color: #334155;
  font-weight: 600;
}

.missed-cta-btn:hover {
  border-color: #94a3b8;
  background: #f8fafc;
  color: #0f172a;
}

.loading-overlay {
  z-index: 2;
}

@media (max-width: 575.98px) {
  .missed-donut {
    width: 170px;
    height: 170px;
  }

  .missed-center h2 {
    font-size: 1.9rem;
  }
}

@media (max-width: 575.98px) {
  .chart-container {
    height: 250px;
  }

  .chart-controls {
    width: 100%;
    justify-content: stretch;
    gap: 0.5rem !important;
  }

  .chart-controls .view-toggle {
    flex: 1 1 auto;
    width: 100%;
  }

  .view-toggle .btn {
    min-width: 0;
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
.peak-hour-icon-wrap {
  width: 48px;
  height: 48px;
}
.peak-hour-icon-wrap--primary {
  background-color: #fef3e7 !important;
}
.peak-hour-icon-wrap--secondary {
  background-color: #f3f4f6 !important;
}
.peak-hour-icon {
  font-size: 18px;
  line-height: 1;
}
.peak-hour-icon--primary {
  color: #f59e0b !important;
}
.peak-hour-icon--secondary {
  color: #374151 !important;
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

/* Subscription expiry warning banner */
.expiry-warning-banner {
  background: linear-gradient(90deg, #fff7ed 0%, #fffbeb 100%);
  border: 1.5px solid #f97316;
  border-radius: 12px;
  padding: 14px 20px;
}

.expiry-warning-inner {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
}

.expiry-warning-icon {
  color: #f97316;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.expiry-warning-text {
  flex: 1;
  min-width: 0;
}

.expiry-warning-text strong {
  display: block;
  color: #9a3412;
  font-size: 0.95rem;
  font-weight: 700;
}

.expiry-warning-text span {
  color: #7c3211;
  font-size: 0.875rem;
}

.expiry-warning-btn {
  background: #f97316;
  color: #fff;
  border-radius: 8px;
  padding: 8px 18px;
  font-weight: 700;
  font-size: 0.875rem;
  text-decoration: none;
  white-space: nowrap;
  flex-shrink: 0;
}

.expiry-warning-btn:hover {
  background: #ea6c10;
  color: #fff;
}
</style>
