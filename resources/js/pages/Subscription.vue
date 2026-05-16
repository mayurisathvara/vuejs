<template>
  <div class="sub-page">

    <!-- Compact Page Header -->
    <header class="sub-hdr">
      <div class="sub-hdr-meta">
        <h4 class="sub-hdr-title">Subscription</h4>
        <span class="sub-hdr-sub">Billing &amp; Plan Management</span>
      </div>
      <div class="sub-hdr-actions">
        <button type="button" class="act-btn" :disabled="loading" @click="fetchSubscription">
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
          Refresh
        </button>
        <router-link v-if="canRequestRenewal" class="act-btn act-btn--renew" to="/subscription/renew">
          <i class="fas fa-bolt"></i>
          Request Renewal
        </router-link>
        <span v-else class="act-btn act-btn--renew act-btn--locked" :title="renewalLockHint">
          <i class="fas fa-lock"></i>
          Request Renewal
        </span>
        <button type="button" class="act-btn act-btn--ghost" @click="scrollToHistory">
          <i class="fas fa-history"></i>
          History
        </button>
      </div>
    </header>

    <!-- Loading -->
    <div v-if="loading" class="sub-state">
      <div class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></div>
      <span>Loading subscription details…</span>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="sub-state sub-state--err">
      <i class="fas fa-exclamation-circle"></i>
      {{ error }}
    </div>

    <template v-else>
      <!-- Empty state -->
      <div v-if="!subscription" class="sub-empty">
        <div class="sub-empty-icon"><i class="fas fa-receipt"></i></div>
        <h4>No subscription found</h4>
        <p>Your organization does not have a subscription record yet. Request a plan setup from billing to start using plan limits.</p>
        <router-link class="act-btn act-btn--renew" to="/subscription/renew">Request Plan Setup</router-link>
      </div>

      <template v-else>
        <!-- ── 3-column main cards row ── -->
        <div class="main-row">

          <!-- Current Plan -->
          <section class="b-card plan-card">
            <div class="b-card-top">
              <span class="kicker">Current Plan</span>
              <span class="status-pill" :class="statusClass">
                <span class="status-dot"></span>{{ statusLabel }}
              </span>
            </div>
            <h3 class="plan-name">{{ planName }}</h3>
            <p class="plan-tag">{{ planTagline }}</p>

            <div class="price-row">
              <strong>{{ planPrice }}</strong>
              <span>{{ priceCaption }}</span>
            </div>

            <div class="renew-strip" :class="{ urgent: isRenewalSoon || isExpired }">
              <div class="renew-strip-copy">
                <span>{{ renewalHeadline }}</span>
                <strong>{{ renewalSubtext }}</strong>
              </div>
              <router-link v-if="canRequestRenewal" class="accent-link" to="/subscription/renew">{{ renewalActionText }}</router-link>
              <span v-else class="accent-link accent-link--locked" :title="renewalLockHint">{{ renewalActionText }}</span>
            </div>

            <div class="plan-meta">
              <div class="meta-chip">
                <span>Billing Cycle</span>
                <strong>{{ billingCycleLabel }}</strong>
              </div>
              <div class="meta-chip">
                <span>Start Date</span>
                <strong>{{ formatDateDisplay(subscription.start_date) }}</strong>
              </div>
              <div class="meta-chip">
                <span>Expiry</span>
                <strong>{{ endDateLabel }}</strong>
              </div>
            </div>

            <!-- Auto-renew toggle (hidden for trial plans) -->
            <div v-if="!isTrialPlan" class="auto-renew-row">
              <div class="auto-renew-info">
                <i class="fas fa-sync-alt auto-renew-icon" :class="{ 'is-on': autoRenewEnabled }"></i>
                <div>
                  <strong>Auto-Renewal</strong>
                  <span>{{ autoRenewEnabled ? 'Enabled — your plan renews automatically.' : 'Disabled — you must renew manually before expiry.' }}</span>
                </div>
              </div>
              <button
                type="button"
                class="toggle-switch"
                :class="{ 'is-on': autoRenewEnabled, 'is-loading': autoRenewLoading }"
                :disabled="autoRenewLoading"
                :aria-label="autoRenewEnabled ? 'Disable auto-renewal' : 'Enable auto-renewal'"
                :title="autoRenewEnabled ? 'Click to disable auto-renewal' : 'Click to enable auto-renewal'"
                @click="handleAutoRenewToggle"
              >
                <span class="toggle-thumb"></span>
              </button>
            </div>

            <!-- Auto-renew failure notice -->
            <div v-if="autoRenewFailureReason" class="auto-renew-alert">
              <i class="fas fa-exclamation-triangle"></i>
              <span>{{ autoRenewFailureReason }}</span>
            </div>
          </section>

          <!-- SIM Usage -->
          <section class="b-card usage-card">

            <!-- Header -->
            <div class="b-card-top">
              <span class="kicker">SIM Usage</span>
              <router-link to="/sims" class="accent-link">Manage SIMs</router-link>
            </div>

            <!-- Capacity row -->
            <div class="cap-row">
              <div class="cap-numbers">
                <strong class="cap-used">{{ activeSims }}</strong>
                <div class="cap-meta">
                  <span class="cap-of">of <strong>{{ simLimit }}</strong> SIM slots</span>
                  <span class="cap-sub">{{ remainingSlots }} slot{{ remainingSlots === 1 ? '' : 's' }} available</span>
                </div>
              </div>
              <span class="cap-badge" :class="simUsagePercent >= 80 ? 'cap-badge--warn' : 'cap-badge--ok'">
                {{ simUsagePercent }}%
              </span>
            </div>

            <!-- Progress bar -->
            <div class="prog-track prog-track--md">
              <span :style="{ width: `${simUsagePercent}%` }"></span>
            </div>

            <!-- Stat chips -->
            <div class="usage-stats">
              <div class="stat-chip stat-chip--green">
                <span class="stat-dot"></span>
                <div>
                  <span>Active</span>
                  <strong>{{ activeSims }}</strong>
                </div>
              </div>
              <div class="stat-chip stat-chip--blue">
                <span class="stat-dot"></span>
                <div>
                  <span>Available</span>
                  <strong>{{ remainingSlots }}</strong>
                </div>
              </div>
              <div class="stat-chip stat-chip--grey">
                <span class="stat-dot"></span>
                <div>
                  <span>Inactive</span>
                  <strong>{{ inactiveSims }}</strong>
                </div>
              </div>
            </div>

            <!-- Add-on block -->
            <div class="addon-blk" :class="{ 'addon-blk--off': isTrialPlan }">

              <!-- Add-on header -->
              <div class="addon-blk-hdr">
                <div>
                  <span class="kicker">Add-on SIMs</span>
                  <strong class="addon-blk-label">Expand capacity</strong>
                </div>
                <span class="addon-rate">{{ formatCurrency(addonPricePerSim) }}/SIM</span>
              </div>

              <!-- Disabled state note -->
              <p v-if="isTrialPlan || !canPurchaseAddon" class="addon-state-note">
                <i class="fas fa-info-circle"></i>
                <template v-if="isTrialPlan">Not available on trial plan. Upgrade to enable add-ons.</template>
                <template v-else>Requires an active subscription with a future end date.</template>
              </p>

              <!-- Proration indicator strip -->
              <div v-if="canPurchaseAddon" class="proration-strip">
                <div class="proration-strip-track">
                  <div class="proration-strip-fill" :style="{ width: `${Math.round(addonProrationFactor * 100)}%` }"></div>
                </div>
                <div class="proration-strip-labels">
                  <span v-if="addonProrationFactor >= 0.999">
                    <i class="fas fa-calendar-check"></i> Full billing period — {{ addonBillingCycleDays }} days
                  </span>
                  <span v-else>
                    <i class="fas fa-clock"></i> {{ addonRemainingDays }} of {{ addonBillingCycleDays }} days remaining
                  </span>
                  <strong>{{ Math.round(addonProrationFactor * 100) }}%</strong>
                </div>
              </div>

              <!-- Quantity stepper -->
              <div class="addon-qty">
                <button type="button" :disabled="isTrialPlan || addonSimQuantity <= 1" @click="decreaseAddonSims">
                  <i class="fas fa-minus"></i>
                </button>
                <input
                  v-model.number="addonSimQuantity"
                  type="number" min="1" step="1"
                  aria-label="Add-on SIM quantity"
                  :disabled="isTrialPlan"
                  @blur="normalizeAddonSims"
                />
                <button type="button" :disabled="isTrialPlan" @click="increaseAddonSims">
                  <i class="fas fa-plus"></i>
                </button>
              </div>

              <!-- Calculation breakdown -->
              <div class="addon-calc">
                <div class="addon-calc-left">
                  <span class="addon-calc-formula">
                    {{ addonQuantity }} SIM{{ addonQuantity === 1 ? '' : 's' }} × {{ formatCurrency(addonPricePerSim) }}
                    <template v-if="canPurchaseAddon && addonProrationFactor < 0.999"> × {{ Math.round(addonProrationFactor * 100) }}%</template>
                  </span>
                </div>
                <div class="addon-calc-right">
                  <del v-if="canPurchaseAddon && addonProrationFactor < 0.999" class="addon-full-price">
                    {{ formatCurrency(addonTotal) }}
                  </del>
                  <strong class="addon-due">{{ formatCurrency(canPurchaseAddon ? addonProratedTotal : addonTotal) }}</strong>
                </div>
              </div>

              <!-- Payment message -->
              <div v-if="addonPaymentMessage" class="addon-msg" :class="`addon-msg--${addonPaymentMessageType}`">
                <i :class="addonPaymentMessageType === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
                {{ addonPaymentMessage }}
              </div>

              <!-- Pay button with amount shown -->
              <button
                type="button"
                class="btn-addon-pay"
                :disabled="isTrialPlan || addonPaymentLoading || !canPurchaseAddon"
                @click="payAddonSims"
              >
                <span v-if="addonPaymentLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <i v-else class="fas fa-credit-card"></i>
                <span v-if="addonPaymentLoading">Opening Checkout…</span>
                <span v-else>
                  Pay {{ formatCurrency(canPurchaseAddon ? addonProratedTotal : addonTotal) }}
                  · {{ addonQuantity }} SIM{{ addonQuantity === 1 ? '' : 's' }}
                </span>
              </button>

            </div>
          </section>

          <!-- Plan Timeline -->
          <section class="b-card timeline-card">
            <span class="kicker">Plan Timeline</span>
            <h5 class="b-card-title">Subscription Progress</h5>
            <div class="ring-center">
              <div class="timeline-ring" :style="{ '--progress': `${termProgress}deg` }">
                <div>
                  <strong>{{ timelineValue }}</strong>
                  <span>{{ timelineLabel }}</span>
                </div>
              </div>
            </div>
            <div class="timeline-foot">
              <div class="bar-labels">
                <span>{{ formatDateDisplay(subscription.start_date) }}</span>
                <span>{{ endDateLabel }}</span>
              </div>
              <div class="prog-track">
                <span :style="{ width: `${termProgressPercent}%` }"></span>
              </div>
            </div>
          </section>
        </div>

        <!-- ── Included Features ── -->
        <section class="b-card features-card">
          <div class="b-sec-head">
            <span class="kicker">Included Features</span>
            <h5 class="b-card-title">What your plan unlocks</h5>
          </div>
          <div v-if="featureList.length" class="feature-grid">
            <div v-for="feature in featureList" :key="feature.key" class="feature-item">
              <div class="feature-icon"><i :class="feature.icon"></i></div>
              <div>
                <strong>{{ feature.label }}</strong>
                <span>{{ feature.description }}</span>
              </div>
            </div>
          </div>
          <p v-else class="empty-note">Feature details are not configured for this plan yet.</p>
        </section>
      </template>

      <!-- ── Payment History (always visible in v-else context) ── -->
      <section ref="historySection" class="b-card pay-card">
        <div class="b-sec-head">
          <div>
            <span class="kicker">Payment History</span>
            <h5 class="b-card-title">Invoices</h5>
          </div>
          <button type="button" class="act-btn act-btn--sm" :disabled="historyLoading" @click="fetchPaymentHistory">
            <i class="fas fa-sync-alt" :class="{ 'fa-spin': historyLoading }"></i>
            Refresh
          </button>
        </div>

        <div v-if="historyLoading" class="sub-state">
          <div class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></div>
          <span>Loading payment history…</span>
        </div>
        <div v-else-if="historyError" class="sub-state sub-state--err">
          <i class="fas fa-exclamation-circle"></i>
          {{ historyError }}
        </div>
        <p v-else-if="!paymentHistory.length" class="empty-note">No paid invoices are available yet.</p>

        <div v-else class="table-wrap">
          <table class="pay-table">
            <thead>
              <tr>
                <th>Invoice</th>
                <th>Plan</th>
                <th>Period</th>
                <th>Payment ID</th>
                <th class="text-end">Amount</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="payment in paymentHistory" :key="payment.id">
                <td>
                  <strong>{{ payment.invoice_number }}</strong>
                  <span>{{ formatDateDisplay(payment.paid_at) }}</span>
                </td>
                <td>
                  <strong>{{ payment.plan_name }}</strong>
                  <span>{{ toTitleCase(payment.billing_cycle) }} · {{ payment.sim_quantity }} SIMs</span>
                </td>
                <td>
                  <strong>{{ formatDateDisplay(payment.start_date) }}</strong>
                  <span>to {{ formatDateDisplay(payment.end_date) }}</span>
                </td>
                <td><span class="pay-id">{{ payment.razorpay_payment_id }}</span></td>
                <td class="text-end">
                  <strong>{{ formatCurrency(payment.amount, payment.currency) }}</strong>
                  <span class="pay-status">{{ toTitleCase(payment.payment_status) }}</span>
                </td>
                <td class="text-end">
                  <div class="inv-btns">
                    <button type="button" title="View invoice" @click="viewInvoice(payment)">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" title="Download invoice" @click="downloadInvoice(payment)">
                      <i class="fas fa-download"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '@/services/api'
import { formatDateDisplay } from '@/utils/dateFormatter'
import { showError, showSuccess } from '@/services/toast'

const loading = ref(false)
const error = ref('')
const payload = ref(null)
const addonSimQuantity = ref(1)
const addonPaymentLoading = ref(false)
const addonPaymentMessage = ref('')
const addonPaymentMessageType = ref('')
const historyLoading = ref(false)
const historyError = ref('')
const paymentHistory = ref([])
const historySection = ref(null)
const autoRenewLoading = ref(false)

const stats = computed(() => payload.value?.stats || {})
const subscription = computed(() => payload.value?.subscription || null)
const plan = computed(() => payload.value?.plan || subscription.value?.plan || null)

const featureCopy = {
  call_tracking: {
    label: 'Call Tracking',
    description: 'Capture inbound and outbound call activity across your organization.',
    icon: 'fas fa-phone-volume'
  },
  reports: {
    label: 'Reports',
    description: 'View team, SIM, and performance reporting dashboards.',
    icon: 'fas fa-chart-line'
  },
  ai: {
    label: 'AI Insights',
    description: 'Unlock smarter analytics for call trends and operational decisions.',
    icon: 'fas fa-magic'
  },
  api: {
    label: 'Developer API',
    description: 'Connect your call data with external systems and workflows.',
    icon: 'fas fa-code'
  }
}

const fetchSubscription = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await api.get('/subscription/overview')
    payload.value = response.data?.data || null
    await fetchPaymentHistory()
  } catch (err) {
    error.value = err.response?.data?.message || 'Unable to load subscription details. Please try again.'
  } finally {
    loading.value = false
  }
}

const fetchPaymentHistory = async () => {
  historyLoading.value = true
  historyError.value = ''

  try {
    const response = await api.get('/subscription/payments')
    paymentHistory.value = Array.isArray(response.data?.data) ? response.data.data : []
  } catch (err) {
    historyError.value = err.response?.data?.message || 'Unable to load payment history.'
  } finally {
    historyLoading.value = false
  }
}

const parseDate = (value) => {
  if (!value) return null
  const [year, month, day] = String(value).slice(0, 10).split('-').map(Number)
  if (!year || !month || !day) return null
  return new Date(year, month - 1, day)
}

const startOfToday = () => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return today
}

const formatCurrency = (value, currency = 'INR') => {
  const numeric = Number(value || 0)
  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: currency || 'INR',
    maximumFractionDigits: numeric % 1 === 0 ? 0 : 2
  }).format(numeric)
}

const toTitleCase = (value) => {
  return String(value || '')
    .replace(/[_-]/g, ' ')
    .replace(/\w\S*/g, (word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
}

const clamp = (value, min, max) => Math.min(Math.max(value, min), max)

const planName = computed(() => plan.value?.display_name || stats.value.plan_name || 'No Plan')
const planSlug = computed(() => plan.value?.name || stats.value.plan_slug || '')
const isTrialPlan = computed(() => planSlug.value === 'free_trial')
const planTagline = computed(() => {
  if (planSlug.value === 'free_trial') return 'Explore Callytics with trial access before moving to a paid plan.'
  if (planSlug.value === 'advance') return 'Built for growing teams that need scale, automation, and API access.'
  if (planSlug.value === 'basic') return 'Essential call tracking and reporting for your organization.'
  return 'Your organization subscription details and usage limits.'
})

const status = computed(() => subscription.value?.status || stats.value.subscription_status || 'none')
const statusLabel = computed(() => toTitleCase(status.value))
const statusClass = computed(() => ({
  'is-active': status.value === 'active',
  'is-expired': status.value === 'expired',
  'is-cancelled': status.value === 'cancelled',
  'is-empty': !['active', 'expired', 'cancelled'].includes(status.value)
}))

const billingCycleLabel = computed(() => toTitleCase(subscription.value?.billing_cycle || stats.value.billing_cycle || 'Not Set'))
const simLimit = computed(() => Number(subscription.value?.sim_limit ?? stats.value.sim_limit ?? 0))
const activeSims = computed(() => Number(stats.value.active_sims ?? 0))
const inactiveSims = computed(() => Number(stats.value.inactive_sims ?? 0))
const remainingSlots = computed(() => Math.max(0, simLimit.value - activeSims.value))
const simUsagePercent = computed(() => (simLimit.value ? clamp(Math.round((activeSims.value / simLimit.value) * 100), 0, 100) : 0))
const addonQuantity = computed(() => {
  const quantity = Number.parseInt(addonSimQuantity.value, 10)
  return Number.isFinite(quantity) ? Math.max(1, quantity) : 1
})
const addonPricePerSim = computed(() => Number(plan.value?.price_per_sim || 0))
const addonTotal = computed(() => addonPricePerSim.value * addonQuantity.value)

// Whether add-on SIMs can currently be purchased (active subscription with future end date).
const canPurchaseAddon = computed(() => {
  if (isTrialPlan.value) return false
  if (status.value !== 'active') return false
  const end = parseDate(subscription.value?.end_date)
  if (!end) return false
  return end > startOfToday()
})

// Proration calculation (mirrors backend logic so the UI shows exact amounts).
const addonBillingCycleDays = computed(() => {
  const start = parseDate(subscription.value?.start_date)
  const end   = parseDate(subscription.value?.end_date)
  if (!start || !end) return 30
  return Math.max(1, Math.floor((end.getTime() - start.getTime()) / 86400000))
})

const addonRemainingDays = computed(() => {
  if (!canPurchaseAddon.value) return 0
  const end   = parseDate(subscription.value?.end_date)
  const today = startOfToday()
  return Math.max(0, Math.floor((end.getTime() - today.getTime()) / 86400000))
})

const addonProrationFactor = computed(() =>
  addonBillingCycleDays.value > 0
    ? Math.min(1, addonRemainingDays.value / addonBillingCycleDays.value)
    : 1
)

const addonProratedTotal = computed(() =>
  Math.round(addonPricePerSim.value * addonQuantity.value * addonProrationFactor.value * 100) / 100
)

const normalizeAddonSims = () => {
  addonSimQuantity.value = addonQuantity.value
}

const decreaseAddonSims = () => {
  addonSimQuantity.value = Math.max(1, addonQuantity.value - 1)
}

const increaseAddonSims = () => {
  addonSimQuantity.value = addonQuantity.value + 1
}

const openInvoiceBlob = (blob, filename, shouldDownload = false) => {
  const url = URL.createObjectURL(blob)

  if (shouldDownload) {
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)
    return
  }

  window.open(url, '_blank', 'noopener,noreferrer')
  window.setTimeout(() => URL.revokeObjectURL(url), 60000)
}

const invoiceFilename = (payment) => `${String(payment.invoice_number || `invoice-${payment.id}`).toLowerCase()}.html`

const viewInvoice = async (payment) => {
  const invoiceWindow = window.open('', '_blank', 'noopener,noreferrer')

  try {
    const response = await api.get(`/subscription/invoices/${payment.id}`, {
      responseType: 'blob',
      headers: { Accept: 'text/html' }
    })

    const url = URL.createObjectURL(response.data)

    if (invoiceWindow) {
      invoiceWindow.location.href = url
      window.setTimeout(() => URL.revokeObjectURL(url), 60000)
    } else {
      openInvoiceBlob(response.data, invoiceFilename(payment), false)
    }
  } catch (err) {
    if (invoiceWindow) invoiceWindow.close()
    historyError.value = err.response?.data?.message || 'Unable to open invoice.'
  }
}

const downloadInvoice = async (payment) => {
  try {
    const response = await api.get(`/subscription/invoices/${payment.id}/download`, {
      responseType: 'blob',
      headers: { Accept: 'text/html' }
    })
    openInvoiceBlob(response.data, invoiceFilename(payment), true)
  } catch (err) {
    historyError.value = err.response?.data?.message || 'Unable to download invoice.'
  }
}

const scrollToHistory = async () => {
  if (!paymentHistory.value.length && !historyLoading.value) {
    await fetchPaymentHistory()
  }

  historySection.value?.scrollIntoView({
    behavior: 'smooth',
    block: 'start'
  })
}

const priceCaption = computed(() => {
  const cycle = subscription.value?.billing_cycle || plan.value?.billing_type
  if (cycle === 'trial') return 'trial plan'
  return `per SIM / ${cycle || 'cycle'}`
})
const planPrice = computed(() => {
  if (!plan.value || Number(plan.value.price_per_sim || 0) === 0) return 'Free'
  return formatCurrency(plan.value.price_per_sim)
})

const endDate = computed(() => parseDate(subscription.value?.end_date))
const startDate = computed(() => parseDate(subscription.value?.start_date))
const endDateLabel = computed(() => subscription.value?.end_date ? formatDateDisplay(subscription.value.end_date) : 'No expiry')

const daysRemaining = computed(() => {
  if (!endDate.value) return null
  const diff = endDate.value.getTime() - startOfToday().getTime()
  return Math.ceil(diff / 86400000)
})
const isExpired = computed(() => status.value === 'expired' || (daysRemaining.value !== null && daysRemaining.value < 0))
const isRenewalSoon = computed(() => daysRemaining.value !== null && daysRemaining.value >= 0 && daysRemaining.value <= 7)

const renewalDaysBefore = computed(() => stats.value?.renewal_days_before ?? 2)

const canRequestRenewal = computed(() => {
  if (isExpired.value) return true
  if (daysRemaining.value === null) return false
  return daysRemaining.value <= renewalDaysBefore.value
})

const renewalLockHint = computed(() => {
  if (daysRemaining.value === null) return 'Renewal is not available for this plan type.'
  if (daysRemaining.value <= renewalDaysBefore.value) return ''
  const d = daysRemaining.value
  const threshold = renewalDaysBefore.value
  return `Renewal opens when ${threshold} day${threshold === 1 ? '' : 's'} remain (${d} day${d === 1 ? '' : 's'} left).`
})

const renewalHeadline = computed(() => {
  if (isExpired.value) return 'Subscription expired'
  if (daysRemaining.value === null) return 'Active until cancelled'
  if (daysRemaining.value === 0) return 'Expires today'
  if (isRenewalSoon.value) return 'Renewal coming up'
  return 'Next renewal window'
})

const renewalSubtext = computed(() => {
  if (isExpired.value) return 'Renew now to keep SIM capacity active.'
  if (daysRemaining.value === null) return 'No end date is configured for this plan.'
  if (daysRemaining.value === 0) return 'Your plan reaches expiry today.'
  return `${daysRemaining.value} day${daysRemaining.value === 1 ? '' : 's'} remaining`
})

const renewalActionText = computed(() => (isExpired.value || isRenewalSoon.value ? 'Renew Now' : 'Request Renewal'))

const timelineValue = computed(() => {
  if (isExpired.value) return 'Expired'
  if (daysRemaining.value === null) return 'Always'
  return daysRemaining.value <= 0 ? 'Today' : daysRemaining.value
})
const timelineLabel = computed(() => {
  if (isExpired.value) return 'needs renewal'
  if (daysRemaining.value === null) return 'active plan'
  return daysRemaining.value === 1 ? 'day left' : 'days left'
})

const termProgressPercent = computed(() => {
  if (!startDate.value || !endDate.value) return status.value === 'active' ? 100 : 0
  const total = endDate.value.getTime() - startDate.value.getTime()
  if (total <= 0) return isExpired.value ? 100 : 0
  const elapsed = startOfToday().getTime() - startDate.value.getTime()
  return clamp(Math.round((elapsed / total) * 100), 0, 100)
})
const termProgress = computed(() => Math.round((termProgressPercent.value / 100) * 360))

const autoRenewEnabled = computed(() => subscription.value?.auto_renew ?? true)
const autoRenewFailureReason = computed(() => subscription.value?.auto_renew_failure_reason || null)

const handleAutoRenewToggle = async () => {
  if (autoRenewLoading.value || !subscription.value) return

  const newValue = !autoRenewEnabled.value
  autoRenewLoading.value = true

  try {
    await api.post('/subscription/auto-renew/toggle', { auto_renew: newValue })
    // Optimistically update the local payload
    if (payload.value?.subscription) {
      payload.value.subscription.auto_renew = newValue
      if (newValue) {
        payload.value.subscription.auto_renew_failure_reason = null
        payload.value.subscription.auto_renew_failed_at = null
      }
    }
    showSuccess(newValue ? 'Auto-renewal enabled.' : 'Auto-renewal disabled.')
  } catch (err) {
    showError(err.response?.data?.message || 'Failed to update auto-renewal setting.')
  } finally {
    autoRenewLoading.value = false
  }
}

const featureList = computed(() => {
  const features = plan.value?.features || stats.value.features || []
  if (!Array.isArray(features)) return []

  return features.map((feature) => {
    const key = String(feature)
    return {
      key,
      label: featureCopy[key]?.label || toTitleCase(key),
      description: featureCopy[key]?.description || 'Included in your current organization plan.',
      icon: featureCopy[key]?.icon || 'fas fa-check'
    }
  })
})

const loadRazorpayCheckout = () =>
  new Promise((resolve, reject) => {
    if (window.Razorpay) { resolve(); return }
    const script = document.createElement('script')
    script.src = 'https://checkout.razorpay.com/v1/checkout.js'
    script.async = true
    script.onload = resolve
    script.onerror = () => reject(new Error('Unable to load Razorpay checkout.'))
    document.head.appendChild(script)
  })

const payAddonSims = async () => {
  if (!canPurchaseAddon.value || addonPaymentLoading.value) return

  normalizeAddonSims()
  addonPaymentLoading.value = true
  addonPaymentMessage.value = ''
  addonPaymentMessageType.value = ''

  try {
    await loadRazorpayCheckout()

    const response = await api.post('/subscription/addon-sim/order', {
      sim_quantity: addonQuantity.value
    })

    const data  = response.data?.data || {}
    const order = data.order || {}
    const quote = data.quote || {}

    if (!order.id || !data.key) {
      throw new Error('Razorpay order response is incomplete.')
    }

    const user = JSON.parse(localStorage.getItem('user') || '{}')
    let paymentHandled = false

    const checkout = new window.Razorpay({
      key: data.key,
      amount: order.amount,
      currency: order.currency || quote.currency || 'INR',
      name: 'Callytics',
      description: `Add-on SIMs — ${addonQuantity.value} SIM(s) prorated for ${quote.remaining_days ?? addonRemainingDays.value} days`,
      order_id: order.id,
      prefill: {
        name: user.name || '',
        email: user.email || '',
        contact: user.mobile || user.phone || ''
      },
      notes: {
        type: 'addon_sim',
        sim_quantity: String(addonQuantity.value)
      },
      theme: { color: '#12b8a6' },
      handler: async (payment) => {
        paymentHandled = true
        await verifyAddonPayment(payment)
      },
      modal: {
        ondismiss: () => {
          if (paymentHandled) return
          addonPaymentLoading.value = false
          addonPaymentMessage.value = 'Checkout closed before payment was completed.'
          addonPaymentMessageType.value = 'danger'
        }
      }
    })

    checkout.on('payment.failed', (failure) => {
      paymentHandled = true
      addonPaymentLoading.value = false
      addonPaymentMessage.value = failure.error?.description || 'Payment failed. Please try again.'
      addonPaymentMessageType.value = 'danger'
      showError(addonPaymentMessage.value)
    })

    checkout.open()
  } catch (err) {
    addonPaymentLoading.value = false
    addonPaymentMessage.value = err.response?.data?.message || err.message || 'Unable to start add-on payment.'
    addonPaymentMessageType.value = 'danger'
    showError(addonPaymentMessage.value)
  }
}

const verifyAddonPayment = async (payment) => {
  try {
    const response = await api.post('/subscription/addon-sim/verify', {
      razorpay_order_id:   payment.razorpay_order_id,
      razorpay_payment_id: payment.razorpay_payment_id,
      razorpay_signature:  payment.razorpay_signature
    })

    addonPaymentMessage.value = response.data?.message || 'Add-on SIMs activated successfully.'
    addonPaymentMessageType.value = 'success'
    showSuccess(addonPaymentMessage.value)
    await fetchSubscription()
  } catch (err) {
    addonPaymentMessage.value = err.response?.data?.message || 'Payment verification failed. Please contact support.'
    addonPaymentMessageType.value = 'danger'
    showError(addonPaymentMessage.value)
  } finally {
    addonPaymentLoading.value = false
  }
}

onMounted(fetchSubscription)
</script>

<style scoped>
/* ── Design tokens ── */
.sub-page {
  --ink:    #102033;
  --muted:  #667085;
  --line:   #e5ebf3;
  --navy:   #10233f;
  --blue:   #2563eb;
  --teal:   #12b8a6;
  --orange: #f97316;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* ── Compact Page Header ── */
.sub-hdr {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  padding: 14px 20px;
  border-radius: 16px;
  background: #fff;
  border: 1px solid var(--line);
  box-shadow: 0 2px 8px rgba(16, 32, 51, 0.05);
}

.sub-hdr-meta {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.sub-hdr-title {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--ink);
  letter-spacing: -0.02em;
}

.sub-hdr-sub {
  font-size: 0.76rem;
  color: var(--muted);
  font-weight: 600;
}

.sub-hdr-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

/* ── Action buttons ── */
.act-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  border-radius: 10px;
  font-size: 0.82rem;
  font-weight: 700;
  border: 1px solid var(--line);
  background: #fff;
  color: var(--ink);
  cursor: pointer;
  text-decoration: none;
  line-height: 1.4;
  transition: background 0.15s, border-color 0.15s;
}

.act-btn:hover {
  background: #f4f6fa;
  border-color: #c9d3e0;
  color: var(--ink);
}

.act-btn--renew {
  border-color: transparent;
  background: linear-gradient(135deg, var(--orange), #ffb454);
  color: #fff !important;
  box-shadow: 0 4px 14px rgba(249, 115, 22, 0.28);
}

.act-btn--renew:hover {
  background: linear-gradient(135deg, #e56a0b, #ffa53e);
  border-color: transparent;
}

.act-btn--ghost {
  background: #f4f6fa;
  border-color: transparent;
}

.act-btn--ghost:hover {
  background: #eaeef4;
  border-color: transparent;
}

.act-btn--sm {
  padding: 5px 10px;
  font-size: 0.78rem;
}

.act-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  pointer-events: none;
}

/* ── State boxes ── */
.sub-state {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px 20px;
  border-radius: 14px;
  background: #fff;
  border: 1px solid var(--line);
  color: var(--muted);
  font-weight: 600;
  font-size: 0.9rem;
}

.sub-state--err {
  color: #b42318;
  border-color: #fecdca;
  background: #fffbfa;
}

/* ── Empty state ── */
.sub-empty {
  text-align: center;
  padding: 48px 24px;
  border-radius: 18px;
  background: #fff;
  border: 1px solid var(--line);
  box-shadow: 0 2px 12px rgba(16, 32, 51, 0.05);
}

.sub-empty-icon {
  width: 64px;
  height: 64px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 20px;
  margin-bottom: 14px;
  color: var(--orange);
  background: #fff4e8;
  font-size: 1.5rem;
}

.sub-empty h4 { margin: 0 0 8px; font-weight: 800; color: var(--ink); }
.sub-empty p  { max-width: 500px; margin: 0 auto 18px; color: var(--muted); line-height: 1.55; }

/* ── Shared card base ── */
.b-card {
  border: 1px solid var(--line);
  border-radius: 20px;
  background: #fff;
  box-shadow: 0 2px 12px rgba(16, 32, 51, 0.06);
}

/* ── Main 3-col row ── */
.main-row {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
  align-items: stretch;
}

/* ── Shared card internals ── */
.kicker {
  display: block;
  font-size: 0.68rem;
  font-weight: 800;
  letter-spacing: 0.13em;
  text-transform: uppercase;
  color: var(--muted);
}

.b-card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 10px;
}

.b-card-title {
  margin: 4px 0 12px;
  font-size: 0.96rem;
  font-weight: 800;
  color: var(--ink);
}

.accent-link {
  font-size: 0.8rem;
  font-weight: 800;
  color: var(--blue);
  text-decoration: none;
  white-space: nowrap;
}

.accent-link:hover { text-decoration: underline; }

.accent-link--locked {
  opacity: 0.45;
  cursor: not-allowed;
  pointer-events: none;
}

/* ── Locked renewal button ── */
.act-btn--locked {
  opacity: 0.45;
  cursor: not-allowed;
  pointer-events: none;
  user-select: none;
}

/* ── Status pill ── */
.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 0.71rem;
  font-weight: 800;
  flex-shrink: 0;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: currentColor;
  flex-shrink: 0;
}

.is-active   { color: #067647; background: #dcfae6; }
.is-expired,
.is-cancelled { color: #b42318; background: #fee4e2; }
.is-empty    { color: #475467; background: #f2f4f7; }

/* ── Current Plan card ── */
.plan-card {
  padding: 22px;
  display: flex;
  flex-direction: column;
}

.plan-name {
  margin: 0 0 4px;
  font-size: clamp(1.3rem, 1.8vw, 1.75rem);
  font-weight: 900;
  letter-spacing: -0.04em;
  color: var(--ink);
  line-height: 1.1;
}

.plan-tag {
  margin: 0;
  font-size: 0.83rem;
  color: var(--muted);
  line-height: 1.5;
}

.price-row {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  margin-top: 16px;
  padding-top: 14px;
  border-top: 1px solid var(--line);
}

.price-row strong {
  font-size: 1.55rem;
  font-weight: 900;
  letter-spacing: -0.04em;
  color: var(--ink);
  line-height: 1;
}

.price-row span {
  color: var(--muted);
  font-size: 0.82rem;
  font-weight: 600;
  margin-bottom: 2px;
}

.renew-strip {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-top: 14px;
  padding: 12px 14px;
  border-radius: 14px;
  border: 1px solid #c7f0eb;
  background: linear-gradient(135deg, rgba(18, 184, 166, 0.08), #f2fffd);
}

.renew-strip.urgent {
  border-color: #fed7aa;
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.08), #fff7ed);
}

.renew-strip-copy span {
  display: block;
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--muted);
}

.renew-strip-copy strong {
  display: block;
  margin-top: 2px;
  color: var(--ink);
  font-size: 0.92rem;
  font-weight: 700;
}

.plan-meta {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
  margin-top: 14px;
}

.meta-chip {
  padding: 10px 12px;
  border: 1px solid var(--line);
  border-radius: 12px;
  background: #fbfdff;
}

.meta-chip span {
  display: block;
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.09em;
  color: var(--muted);
}

.meta-chip strong {
  display: block;
  margin-top: 3px;
  font-size: 0.86rem;
  font-weight: 800;
  color: var(--ink);
}

/* ── SIM Usage card ── */
.usage-card {
  padding: 22px;
  display: flex;
  flex-direction: column;
}

/* Capacity row */
.cap-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 12px;
}

.cap-numbers {
  display: flex;
  align-items: center;
  gap: 12px;
}

.cap-used {
  font-size: 2.4rem;
  font-weight: 900;
  letter-spacing: -0.06em;
  color: var(--ink);
  line-height: 1;
}

.cap-meta {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.cap-of {
  font-size: 0.82rem;
  color: var(--muted);
  font-weight: 600;
}

.cap-of strong {
  color: var(--ink);
  font-weight: 900;
}

.cap-sub {
  font-size: 0.74rem;
  color: var(--teal);
  font-weight: 700;
}

.cap-badge {
  padding: 5px 11px;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 900;
  flex-shrink: 0;
}

.cap-badge--ok   { background: #dcfae6; color: #067647; }
.cap-badge--warn { background: #fef0c7; color: #b54708; }

/* Progress bar variants */
.prog-track {
  height: 8px;
  border-radius: 999px;
  background: #edf2f7;
  overflow: hidden;
}

.prog-track--md { height: 10px; }

.prog-track span {
  display: block;
  height: 100%;
  border-radius: inherit;
  background: linear-gradient(90deg, var(--teal), var(--blue));
  transition: width 0.4s ease;
}

/* Stat chips */
.usage-stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
  margin-top: 14px;
}

.stat-chip {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 12px;
  border-radius: 13px;
  border: 1px solid var(--line);
  background: #f8fafc;
}

.stat-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

.stat-chip--green .stat-dot { background: var(--teal); }
.stat-chip--blue  .stat-dot { background: var(--blue); }
.stat-chip--grey  .stat-dot { background: #94a3b8; }

.stat-chip span {
  display: block;
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.09em;
  color: var(--muted);
}

.stat-chip strong {
  display: block;
  margin-top: 2px;
  font-size: 1.05rem;
  font-weight: 900;
  color: var(--ink);
  letter-spacing: -0.02em;
}

/* ── Add-on block ── */
.addon-blk {
  margin-top: 16px;
  padding: 16px;
  border-radius: 16px;
  border: 1px solid rgba(18, 184, 166, 0.2);
  background: linear-gradient(160deg, #f8fffd 0%, #f0fef9 100%);
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.addon-blk--off { opacity: 0.6; pointer-events: none; }

.addon-blk-hdr {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
}

.addon-blk-label {
  display: block;
  margin-top: 3px;
  font-size: 0.88rem;
  font-weight: 800;
  color: var(--ink);
}

.addon-rate {
  padding: 4px 10px;
  border-radius: 999px;
  background: rgba(18, 184, 166, 0.12);
  color: #0d9488;
  font-size: 0.74rem;
  font-weight: 900;
  white-space: nowrap;
  flex-shrink: 0;
}

/* Disabled state note */
.addon-state-note {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  padding: 10px 12px;
  border-radius: 11px;
  background: #f1f5f9;
  border: 1px solid var(--line);
  font-size: 0.8rem;
  color: var(--muted);
  font-weight: 600;
  line-height: 1.45;
  margin: 0;
}

.addon-state-note i { color: #94a3b8; margin-top: 1px; flex-shrink: 0; }

/* Proration indicator */
.proration-strip {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.proration-strip-track {
  height: 6px;
  border-radius: 999px;
  background: rgba(18, 184, 166, 0.15);
  overflow: hidden;
}

.proration-strip-fill {
  height: 100%;
  border-radius: inherit;
  background: linear-gradient(90deg, var(--teal), #0d9488);
  transition: width 0.4s ease;
}

.proration-strip-labels {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  font-size: 0.77rem;
}

.proration-strip-labels span { color: var(--muted); font-weight: 600; }
.proration-strip-labels span i { margin-right: 4px; font-size: 0.7em; color: var(--teal); }
.proration-strip-labels strong { color: var(--teal); font-weight: 900; }

/* Quantity stepper */
.addon-qty {
  display: grid;
  grid-template-columns: 38px 1fr 38px;
  gap: 8px;
  align-items: center;
}

.addon-qty button,
.addon-qty input {
  height: 40px;
  border: 1px solid rgba(18, 184, 166, 0.25);
  border-radius: 11px;
}

.addon-qty button {
  color: #0d9488;
  background: rgba(18, 184, 166, 0.08);
  font-weight: 900;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
}

.addon-qty button:hover { background: rgba(18, 184, 166, 0.16); }
.addon-qty button:disabled { opacity: 0.44; cursor: not-allowed; }

.addon-qty input {
  width: 100%;
  text-align: center;
  font-size: 1.05rem;
  font-weight: 900;
  color: var(--ink);
  background: #fff;
  outline: none;
}

.addon-qty input:disabled { opacity: 0.44; cursor: not-allowed; background: #f8fafc; }
.addon-qty input:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(18, 184, 166, 0.15); }

/* Calculation row */
.addon-calc {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 11px;
  background: #fff;
  border: 1px solid rgba(18, 184, 166, 0.18);
}

.addon-calc-formula {
  font-size: 0.8rem;
  color: var(--muted);
  font-weight: 600;
}

.addon-calc-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
}

.addon-full-price {
  font-size: 0.75rem;
  color: var(--muted);
  font-weight: 600;
  text-decoration: line-through;
}

.addon-due {
  font-size: 1.1rem;
  font-weight: 900;
  color: var(--ink);
  letter-spacing: -0.03em;
}

/* Payment message */
.addon-msg {
  display: flex;
  align-items: flex-start;
  gap: 7px;
  padding: 9px 12px;
  border-radius: 10px;
  font-size: 0.8rem;
  font-weight: 600;
  line-height: 1.45;
}

.addon-msg--success { background: #dcfae6; color: #067647; }
.addon-msg--danger  { background: #fee4e2; color: #b42318; }

.btn-addon-pay {
  width: 100%;
  padding: 11px 14px;
  border: 0;
  border-radius: 13px;
  color: #fff;
  font-size: 0.85rem;
  font-weight: 800;
  background: linear-gradient(135deg, #0d9488, var(--teal));
  box-shadow: 0 4px 16px rgba(18, 184, 166, 0.32);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  transition: opacity 0.15s, box-shadow 0.15s;
  letter-spacing: -0.01em;
}

.btn-addon-pay:hover:not(:disabled) {
  opacity: 0.9;
  box-shadow: 0 6px 20px rgba(18, 184, 166, 0.4);
}

.btn-addon-pay:disabled { opacity: 0.45; cursor: not-allowed; background: #94a3b8; box-shadow: none; }

/* ── Plan Timeline card ── */
.timeline-card {
  padding: 22px;
  display: flex;
  flex-direction: column;
}

.ring-center {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 14px 0;
}

.timeline-ring {
  width: 158px;
  height: 158px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background:
    radial-gradient(circle closest-side, #fff 72%, transparent 73%),
    conic-gradient(var(--teal) var(--progress), #edf2f7 0);
}

.timeline-ring > div { text-align: center; }

.timeline-ring strong {
  display: block;
  font-size: 1.75rem;
  font-weight: 900;
  letter-spacing: -0.05em;
  color: var(--ink);
  line-height: 1;
}

.timeline-ring span {
  display: block;
  font-size: 0.78rem;
  font-weight: 700;
  color: var(--muted);
  margin-top: 3px;
}

.timeline-foot { width: 100%; }

.bar-labels {
  display: flex;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 6px;
  font-size: 0.71rem;
  font-weight: 700;
  color: var(--muted);
}

/* ── Included Features ── */
.features-card { padding: 20px 22px; }

.b-sec-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 14px;
}

.feature-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.feature-item {
  display: flex;
  gap: 10px;
  align-items: flex-start;
  padding: 12px;
  border-radius: 14px;
  border: 1px solid var(--line);
  background: linear-gradient(180deg, #fff 0%, #fbfdff 100%);
}

.feature-icon {
  flex-shrink: 0;
  width: 34px;
  height: 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  color: var(--blue);
  background: #eaf1ff;
  font-size: 0.82rem;
}

.feature-item strong {
  display: block;
  font-size: 0.88rem;
  font-weight: 800;
  color: var(--ink);
}

.feature-item span {
  display: block;
  font-size: 0.78rem;
  color: var(--muted);
  line-height: 1.45;
  margin-top: 2px;
}

.empty-note {
  padding: 14px 16px;
  border-radius: 12px;
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
  color: var(--muted);
  font-size: 0.86rem;
  margin: 0;
}

/* ── Payment History ── */
.pay-card {
  padding: 20px 22px;
  overflow: hidden;
}

.table-wrap {
  width: 100%;
  overflow-x: auto;
}

.pay-table {
  width: 100%;
  min-width: 820px;
  border-collapse: collapse;
}

.pay-table th {
  padding: 10px 12px;
  font-size: 0.69rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.09em;
  color: var(--muted);
  background: #f8fafc;
  border-bottom: 1px solid var(--line);
  white-space: nowrap;
}

.pay-table td {
  padding: 12px;
  border-bottom: 1px solid var(--line);
  vertical-align: middle;
}

.pay-table tbody tr:last-child td { border-bottom: 0; }

.pay-table strong {
  display: block;
  font-size: 0.88rem;
  font-weight: 800;
  color: var(--ink);
}

.pay-table td > span {
  display: block;
  font-size: 0.76rem;
  color: var(--muted);
  font-weight: 600;
  margin-top: 2px;
}

.pay-id {
  max-width: 180px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 0.78rem !important;
  color: var(--muted) !important;
  margin-top: 0 !important;
}

.pay-status { color: #067647 !important; }

.inv-btns {
  display: inline-flex;
  gap: 6px;
}

.inv-btns button {
  width: 32px;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--line);
  border-radius: 8px;
  color: var(--blue);
  background: #fff;
  cursor: pointer;
  transition: background 0.15s, color 0.15s, border-color 0.15s;
}

.inv-btns button:hover {
  color: #fff;
  border-color: var(--blue);
  background: var(--blue);
}

/* ── Auto-renew toggle ── */
.auto-renew-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-top: 14px;
  padding: 12px 14px;
  border-radius: 14px;
  border: 1px solid var(--line);
  background: #f8fafc;
}

.auto-renew-info {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
  min-width: 0;
}

.auto-renew-icon {
  font-size: 1rem;
  color: #94a3b8;
  flex-shrink: 0;
  transition: color 0.2s;
}

.auto-renew-icon.is-on { color: #067647; }

.auto-renew-info strong {
  display: block;
  font-size: 0.84rem;
  font-weight: 800;
  color: var(--ink);
}

.auto-renew-info span {
  display: block;
  font-size: 0.75rem;
  color: var(--muted);
  font-weight: 600;
  margin-top: 1px;
  line-height: 1.4;
}

/* Toggle switch */
.toggle-switch {
  position: relative;
  width: 46px;
  height: 26px;
  border: none;
  border-radius: 999px;
  background: #e2e8f0;
  cursor: pointer;
  flex-shrink: 0;
  transition: background 0.2s;
  padding: 0;
}

.toggle-switch.is-on  { background: #067647; }
.toggle-switch:disabled { opacity: 0.55; cursor: not-allowed; }
.toggle-switch.is-loading { opacity: 0.7; }

.toggle-thumb {
  position: absolute;
  top: 3px;
  left: 3px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #fff;
  box-shadow: 0 1px 4px rgba(0,0,0,0.18);
  transition: transform 0.2s;
}

.toggle-switch.is-on .toggle-thumb { transform: translateX(20px); }

.auto-renew-alert {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  margin-top: 8px;
  padding: 10px 12px;
  border-radius: 11px;
  background: #fef0c7;
  border: 1px solid #fde68a;
  font-size: 0.8rem;
  color: #92400e;
  font-weight: 600;
  line-height: 1.45;
}

.auto-renew-alert i { flex-shrink: 0; margin-top: 1px; }

/* ── Responsive ── */
@media (max-width: 1100px) {
  .main-row {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .timeline-card {
    grid-column: 1 / -1;
    flex-direction: row;
    align-items: center;
    gap: 24px;
  }

  .ring-center {
    flex: 0 0 auto;
    padding: 0;
  }

  .timeline-foot {
    flex: 1;
  }

  .feature-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 767px) {
  .sub-hdr {
    flex-direction: column;
    align-items: flex-start;
  }

  .main-row {
    grid-template-columns: 1fr;
  }

  .timeline-card {
    grid-column: auto;
    flex-direction: column;
    align-items: stretch;
  }

  .ring-center {
    flex: 1;
    padding: 14px 0;
  }

  .plan-meta,
  .usage-stats {
    grid-template-columns: 1fr 1fr;
  }

  .feature-grid {
    grid-template-columns: 1fr;
  }

  .renew-strip {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
