<template>
  <div class="subscription-page">
    <div class="subscription-hero">
      <div class="hero-glow hero-glow-one"></div>
      <div class="hero-glow hero-glow-two"></div>

      <div class="hero-content">
        <span class="eyebrow">Organization Billing</span>
        <h3>Subscription</h3>
        <p>
          Track your current plan, SIM allowance, renewal window, and plan features from one clean billing workspace.
        </p>
      </div>

      <div class="hero-actions">
        <button type="button" class="btn btn-light hero-refresh" :disabled="loading" @click="fetchSubscription">
          <i class="fas fa-sync-alt me-2" :class="{ 'fa-spin': loading }"></i>
          Refresh
        </button>
        <router-link class="btn renew-button" to="/subscription/renew">
          <i class="fas fa-bolt me-2"></i>
          Request Renewal
        </router-link>
      </div>
    </div>

    <div v-if="loading" class="subscription-loading">
      <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
      <span>Loading subscription details...</span>
    </div>

    <div v-else-if="error" class="alert alert-danger subscription-alert">
      <i class="fas fa-exclamation-circle me-2"></i>
      {{ error }}
    </div>

    <template v-else>
      <div v-if="!subscription" class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-receipt"></i>
        </div>
        <h4>No subscription found</h4>
        <p>Your organization does not have a subscription record yet. Request a plan setup from billing to start using plan limits.</p>
        <router-link class="btn renew-button" to="/subscription/renew">Request Plan Setup</router-link>
      </div>

      <div v-else class="subscription-grid">
        <section class="plan-card">
          <div class="plan-card-top">
            <div>
              <span class="card-kicker">Current Plan</span>
              <h2>{{ planName }}</h2>
              <p>{{ planTagline }}</p>
            </div>
            <span class="status-pill" :class="statusClass">
              <span></span>
              {{ statusLabel }}
            </span>
          </div>

          <div class="price-row">
            <strong>{{ planPrice }}</strong>
            <span>{{ priceCaption }}</span>
          </div>

          <div class="renewal-strip" :class="{ urgent: isRenewalSoon || isExpired }">
            <div class="renewal-copy">
              <span>{{ renewalHeadline }}</span>
              <strong>{{ renewalSubtext }}</strong>
            </div>
            <router-link class="renewal-link" to="/subscription/renew">{{ renewalActionText }}</router-link>
          </div>

          <div class="plan-meta">
            <div class="meta-item">
              <span>Billing Cycle</span>
              <strong>{{ billingCycleLabel }}</strong>
            </div>
            <div class="meta-item">
              <span>Start Date</span>
              <strong>{{ formatDateDisplay(subscription.start_date) }}</strong>
            </div>
            <div class="meta-item">
              <span>Expiry Date</span>
              <strong>{{ endDateLabel }}</strong>
            </div>
          </div>
        </section>

        <aside class="timeline-card">
          <span class="card-kicker">Plan Timeline</span>
          <div class="timeline-ring" :style="{ '--progress': `${termProgress}deg` }">
            <div>
              <strong>{{ timelineValue }}</strong>
              <span>{{ timelineLabel }}</span>
            </div>
          </div>
          <div class="timeline-progress">
            <div class="progress-labels">
              <span>{{ formatDateDisplay(subscription.start_date) }}</span>
              <span>{{ endDateLabel }}</span>
            </div>
            <div class="progress-track">
              <span :style="{ width: `${termProgressPercent}%` }"></span>
            </div>
          </div>
        </aside>
      </div>

      <div v-if="subscription" class="details-grid">
        <section class="usage-card">
          <div class="section-heading">
            <div>
              <span class="card-kicker">SIM Usage</span>
              <h5>Plan Capacity</h5>
            </div>
            <router-link to="/sims" class="small-action">Manage SIMs</router-link>
          </div>

          <div class="usage-main">
            <strong>{{ activeSims }}</strong>
            <span>of {{ simLimit }} active SIM slots used</span>
          </div>
          <div class="usage-track">
            <span :style="{ width: `${simUsagePercent}%` }"></span>
          </div>

          <div class="usage-stats">
            <div>
              <span>Active SIMs</span>
              <strong>{{ activeSims }}</strong>
            </div>
            <div>
              <span>Remaining</span>
              <strong>{{ remainingSlots }}</strong>
            </div>
            <div>
              <span>Inactive</span>
              <strong>{{ inactiveSims }}</strong>
            </div>
          </div>

          <div class="addon-sims-card" :class="{ 'opacity-75': isTrialPlan }">
            <div class="addon-heading">
              <div>
                <span class="card-kicker">Add-on SIMs</span>
                <h5>Buy extra SIM capacity</h5>
              </div>
              <span class="addon-rate">{{ formatCurrency(addonPricePerSim) }} / SIM</span>
            </div>

            <p class="addon-copy" v-if="isTrialPlan">
              Add-on SIMs are not available on free trial accounts. Please upgrade your plan to buy extra SIM capacity.
            </p>
            <p class="addon-copy" v-else>
              Add more SIM slots to your current plan. Quantity starts from 1 SIM and the payment flow will be connected later.
            </p>

            <div class="addon-quantity">
              <button type="button" :disabled="isTrialPlan || addonSimQuantity <= 1" @click="decreaseAddonSims">
                <i class="fas fa-minus"></i>
              </button>
              <input
                v-model.number="addonSimQuantity"
                type="number"
                min="1"
                step="1"
                aria-label="Add-on SIM quantity"
                :disabled="isTrialPlan"
                @blur="normalizeAddonSims"
              />
              <button type="button" :disabled="isTrialPlan" @click="increaseAddonSims">
                <i class="fas fa-plus"></i>
              </button>
            </div>

            <div class="addon-total-row">
              <span>Total for {{ addonQuantity }} add-on SIM{{ addonQuantity === 1 ? '' : 's' }}</span>
              <strong>{{ formatCurrency(addonTotal) }}</strong>
            </div>

            <button type="button" class="btn addon-pay-button" :disabled="isTrialPlan">
              <i class="fas fa-credit-card me-2"></i>
              Pay for Add-on SIMs
            </button>
          </div>
        </section>

        <section class="features-card">
          <div class="section-heading">
            <div>
              <span class="card-kicker">Included Features</span>
              <h5>What your plan unlocks</h5>
            </div>
          </div>

          <div v-if="featureList.length" class="feature-list">
            <div v-for="feature in featureList" :key="feature.key" class="feature-item">
              <div class="feature-icon">
                <i :class="feature.icon"></i>
              </div>
              <div>
                <strong>{{ feature.label }}</strong>
                <span>{{ feature.description }}</span>
              </div>
            </div>
          </div>
          <div v-else class="feature-empty">
            Feature details are not configured for this plan yet.
          </div>
        </section>

        <section class="renew-card">
          <div class="renew-card-icon">
            <i class="fas fa-headset"></i>
          </div>
          <span class="card-kicker">Renew Option</span>
          <h5>Need to renew, upgrade, or extend SIM capacity?</h5>
          <p>
            Send a renewal request to billing with your current plan context. The team can extend the subscription or move you to a higher plan.
          </p>
          <router-link class="btn renew-button w-100" to="/subscription/renew">
            <i class="fas fa-paper-plane me-2"></i>
            {{ renewalActionText }}
          </router-link>
        </section>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '@/services/api'
import { formatDateDisplay } from '@/utils/dateFormatter'

const loading = ref(false)
const error = ref('')
const payload = ref(null)
const addonSimQuantity = ref(1)

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
  } catch (err) {
    error.value = err.response?.data?.message || 'Unable to load subscription details. Please try again.'
  } finally {
    loading.value = false
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

const formatCurrency = (value) => {
  const numeric = Number(value || 0)
  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
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

const normalizeAddonSims = () => {
  addonSimQuantity.value = addonQuantity.value
}

const decreaseAddonSims = () => {
  addonSimQuantity.value = Math.max(1, addonQuantity.value - 1)
}

const increaseAddonSims = () => {
  addonSimQuantity.value = addonQuantity.value + 1
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

onMounted(fetchSubscription)
</script>

<style scoped>
.subscription-page {
  --sub-ink: #102033;
  --sub-muted: #667085;
  --sub-line: #e5ebf3;
  --sub-navy: #10233f;
  --sub-blue: #2563eb;
  --sub-teal: #12b8a6;
  --sub-orange: #f97316;
  display: flex;
  flex-direction: column;
  gap: 22px;
}

.subscription-hero {
  position: relative;
  overflow: hidden;
  border-radius: 20px;
  padding: 20px 28px;
  color: #fff;
  background:
    radial-gradient(circle at 14% 18%, rgba(18, 184, 166, 0.38), transparent 28%),
    radial-gradient(circle at 92% 8%, rgba(249, 115, 22, 0.34), transparent 22%),
    linear-gradient(135deg, #071522 0%, #10233f 52%, #183b5c 100%);
  box-shadow: 0 20px 40px rgba(16, 35, 63, 0.15);
}

.hero-glow {
  position: absolute;
  border-radius: 999px;
  filter: blur(4px);
  opacity: 0.55;
  pointer-events: none;
}

.hero-glow-one {
  width: 230px;
  height: 230px;
  right: -90px;
  bottom: -120px;
  background: rgba(18, 184, 166, 0.42);
}

.hero-glow-two {
  width: 170px;
  height: 170px;
  left: 42%;
  top: -92px;
  background: rgba(249, 115, 22, 0.34);
}

.hero-content,
.hero-actions {
  position: relative;
  z-index: 1;
}

.hero-content {
  max-width: 640px;
}

.eyebrow,
.card-kicker {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.eyebrow {
  color: rgba(255, 255, 255, 0.72);
}

.card-kicker {
  color: #7c8aa0;
}

.subscription-hero h3 {
  margin: 6px 0 6px;
  font-size: clamp(1.5rem, 2.5vw, 2.2rem);
  line-height: 1.1;
  font-weight: 900;
  letter-spacing: -0.05em;
}

.subscription-hero p {
  max-width: 580px;
  margin: 0;
  color: rgba(255, 255, 255, 0.76);
  font-size: 0.98rem;
  line-height: 1.6;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 18px;
}

.hero-refresh {
  border: 0;
  font-weight: 800;
  color: var(--sub-navy);
}

.renew-button {
  border: 0;
  color: #fff !important;
  font-weight: 800;
  background: linear-gradient(135deg, var(--sub-orange), #ffb454);
  box-shadow: 0 16px 32px rgba(249, 115, 22, 0.28);
}

.subscription-loading,
.empty-state,
.subscription-alert {
  border-radius: 22px;
  background: #fff;
  border: 1px solid var(--sub-line);
  box-shadow: 0 18px 40px rgba(16, 32, 51, 0.06);
}

.subscription-loading {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 28px;
  color: var(--sub-muted);
  font-weight: 700;
}

.subscription-alert {
  padding: 18px 20px;
}

.empty-state {
  padding: 54px 28px;
  text-align: center;
}

.empty-icon {
  width: 76px;
  height: 76px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 24px;
  margin-bottom: 18px;
  color: var(--sub-orange);
  background: #fff4e8;
  font-size: 1.8rem;
}

.empty-state h4 {
  margin: 0 0 8px;
  font-weight: 900;
  color: var(--sub-ink);
}

.empty-state p {
  max-width: 540px;
  margin: 0 auto 20px;
  color: var(--sub-muted);
}

.subscription-grid,
.details-grid {
  display: grid;
  gap: 22px;
}

.subscription-grid {
  grid-template-columns: minmax(0, 1.65fr) minmax(300px, 0.75fr);
  align-items: stretch;
}

.details-grid {
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(280px, 0.72fr);
}

.plan-card,
.timeline-card,
.usage-card,
.features-card,
.renew-card {
  border: 1px solid var(--sub-line);
  border-radius: 26px;
  background: #fff;
  box-shadow: 0 18px 40px rgba(16, 32, 51, 0.06);
}

.plan-card {
  padding: 28px;
}

.plan-card-top {
  display: flex;
  justify-content: space-between;
  gap: 18px;
  align-items: flex-start;
}

.plan-card h2 {
  margin: 8px 0 8px;
  color: var(--sub-ink);
  font-size: clamp(2rem, 3.6vw, 3rem);
  font-weight: 900;
  letter-spacing: -0.06em;
}

.plan-card p {
  max-width: 520px;
  margin: 0;
  color: var(--sub-muted);
  line-height: 1.65;
}

.status-pill {
  flex: 0 0 auto;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 13px;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 900;
}

.status-pill span {
  width: 8px;
  height: 8px;
  border-radius: 999px;
  background: currentColor;
}

.status-pill.is-active {
  color: #067647;
  background: #dcfae6;
}

.status-pill.is-expired,
.status-pill.is-cancelled {
  color: #b42318;
  background: #fee4e2;
}

.status-pill.is-empty {
  color: #475467;
  background: #f2f4f7;
}

.price-row {
  display: flex;
  align-items: flex-end;
  gap: 10px;
  margin-top: 24px;
  padding-top: 22px;
  border-top: 1px solid var(--sub-line);
}

.price-row strong {
  font-size: 2rem;
  line-height: 1;
  color: var(--sub-ink);
  font-weight: 900;
  letter-spacing: -0.05em;
}

.price-row span {
  margin-bottom: 3px;
  color: var(--sub-muted);
  font-weight: 700;
}

.renewal-strip {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-top: 22px;
  padding: 16px;
  border: 1px solid #c7f0eb;
  border-radius: 18px;
  background:
    radial-gradient(circle at 0 0, rgba(18, 184, 166, 0.15), transparent 32%),
    #f2fffd;
}

.renewal-strip.urgent {
  border-color: #fed7aa;
  background:
    radial-gradient(circle at 0 0, rgba(249, 115, 22, 0.15), transparent 34%),
    #fff7ed;
}

.renewal-copy span {
  display: block;
  color: var(--sub-muted);
  font-size: 0.82rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.renewal-copy strong {
  display: block;
  margin-top: 2px;
  color: var(--sub-ink);
  font-size: 1.06rem;
}

.renewal-link,
.small-action {
  color: var(--sub-blue);
  font-weight: 900;
  text-decoration: none;
}

.plan-meta {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
  margin-top: 18px;
}

.meta-item {
  padding: 15px;
  border: 1px solid var(--sub-line);
  border-radius: 16px;
  background: #fbfdff;
}

.meta-item span,
.usage-stats span {
  display: block;
  color: var(--sub-muted);
  font-size: 0.78rem;
  font-weight: 800;
}

.meta-item strong,
.usage-stats strong {
  display: block;
  margin-top: 4px;
  color: var(--sub-ink);
  font-size: 1rem;
}

.timeline-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 22px;
  padding: 28px;
}

.timeline-ring {
  width: 190px;
  height: 190px;
  display: grid;
  place-items: center;
  border-radius: 999px;
  background:
    radial-gradient(circle closest-side, #fff 72%, transparent 73%),
    conic-gradient(var(--sub-teal) var(--progress), #edf2f7 0);
}

.timeline-ring div {
  text-align: center;
}

.timeline-ring strong {
  display: block;
  color: var(--sub-ink);
  font-size: 2.1rem;
  font-weight: 900;
  letter-spacing: -0.05em;
}

.timeline-ring span {
  display: block;
  color: var(--sub-muted);
  font-weight: 800;
}

.timeline-progress {
  width: 100%;
}

.progress-labels {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 8px;
  color: var(--sub-muted);
  font-size: 0.78rem;
  font-weight: 800;
}

.progress-track,
.usage-track {
  height: 10px;
  overflow: hidden;
  border-radius: 999px;
  background: #edf2f7;
}

.progress-track span,
.usage-track span {
  display: block;
  height: 100%;
  border-radius: inherit;
  background: linear-gradient(90deg, var(--sub-teal), var(--sub-blue));
}

.usage-card,
.features-card,
.renew-card {
  padding: 24px;
}

.section-heading {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 20px;
}

.section-heading h5,
.renew-card h5 {
  margin: 5px 0 0;
  color: var(--sub-ink);
  font-size: 1.14rem;
  font-weight: 900;
}

.usage-main {
  margin-bottom: 14px;
}

.usage-main strong {
  display: block;
  color: var(--sub-ink);
  font-size: 3rem;
  line-height: 1;
  font-weight: 900;
  letter-spacing: -0.06em;
}

.usage-main span {
  color: var(--sub-muted);
  font-weight: 700;
}

.usage-stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
  margin-top: 18px;
}

.usage-stats div {
  padding: 14px;
  border-radius: 16px;
  background: #f8fafc;
  border: 1px solid var(--sub-line);
}

.addon-sims-card {
  margin-top: 24px;
  padding: 20px;
  border: 1px solid rgba(37, 99, 235, 0.18);
  border-radius: 22px;
  background:
    radial-gradient(circle at 0 0, rgba(18, 184, 166, 0.13), transparent 34%),
    linear-gradient(180deg, #fbfdff 0%, #f6fbff 100%);
}

.addon-heading {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 10px;
}

.addon-heading h5 {
  margin: 5px 0 0;
  color: var(--sub-ink);
  font-size: 1.08rem;
  font-weight: 900;
}

.addon-rate {
  flex: 0 0 auto;
  padding: 7px 11px;
  border-radius: 999px;
  color: #175cd3;
  background: #eff8ff;
  font-size: 0.82rem;
  font-weight: 900;
}

.addon-copy {
  margin: 0 0 16px;
  color: var(--sub-muted);
  line-height: 1.55;
}

.addon-quantity {
  display: grid;
  grid-template-columns: 50px minmax(0, 1fr) 50px;
  gap: 10px;
  align-items: center;
}

.addon-quantity button,
.addon-quantity input {
  height: 52px;
  border: 1px solid var(--sub-line);
  border-radius: 16px;
}

.addon-quantity button {
  color: var(--sub-navy);
  background: #fff;
  font-weight: 900;
}

.addon-quantity button:disabled {
  opacity: 0.48;
  cursor: not-allowed;
}

.addon-quantity input:disabled {
  opacity: 0.48;
  cursor: not-allowed;
  background-color: #f8fafc;
}

.addon-quantity input {
  width: 100%;
  text-align: center;
  color: var(--sub-ink);
  background: #fff;
  font-size: 1.35rem;
  font-weight: 900;
  outline: none;
}

.addon-quantity input:focus {
  border-color: var(--sub-blue);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.addon-total-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-top: 16px;
  padding: 14px 0;
  border-top: 1px solid var(--sub-line);
  border-bottom: 1px solid var(--sub-line);
}

.addon-total-row span {
  color: var(--sub-muted);
  font-weight: 800;
}

.addon-total-row strong {
  color: var(--sub-ink);
  font-size: 1.35rem;
  font-weight: 900;
  letter-spacing: -0.04em;
}

.addon-pay-button {
  width: 100%;
  margin-top: 16px;
  border: 0;
  color: #fff;
  font-weight: 900;
  background: linear-gradient(135deg, var(--sub-blue), var(--sub-teal));
  box-shadow: 0 14px 26px rgba(37, 99, 235, 0.22);
}

.addon-pay-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: var(--sub-muted);
  box-shadow: none;
}

.feature-list {
  display: grid;
  gap: 12px;
}

.feature-item {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  padding: 14px;
  border-radius: 18px;
  border: 1px solid var(--sub-line);
  background: linear-gradient(180deg, #fff 0%, #fbfdff 100%);
}

.feature-icon {
  flex: 0 0 auto;
  width: 42px;
  height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 14px;
  color: var(--sub-blue);
  background: #eaf1ff;
}

.feature-item strong {
  display: block;
  color: var(--sub-ink);
  font-weight: 900;
}

.feature-item span,
.feature-empty,
.renew-card p {
  color: var(--sub-muted);
  line-height: 1.55;
}

.feature-empty {
  padding: 18px;
  border-radius: 16px;
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
}

.renew-card {
  display: flex;
  flex-direction: column;
}

.renew-card-icon {
  width: 58px;
  height: 58px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 18px;
  border-radius: 20px;
  color: #fff;
  background: linear-gradient(135deg, var(--sub-navy), var(--sub-blue));
  box-shadow: 0 18px 30px rgba(37, 99, 235, 0.2);
}

.renew-card p {
  margin: 12px 0 22px;
}

.renew-card .btn {
  margin-top: auto;
}

@media (max-width: 1199.98px) {
  .subscription-grid,
  .details-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 767.98px) {
  .subscription-hero {
    padding: 26px;
    border-radius: 22px;
  }

  .hero-actions,
  .renewal-strip,
  .plan-card-top,
  .section-heading {
    align-items: stretch;
    flex-direction: column;
  }

  .plan-meta,
  .usage-stats {
    grid-template-columns: 1fr;
  }

  .addon-heading,
  .addon-total-row {
    align-items: stretch;
    flex-direction: column;
  }

  .price-row {
    align-items: flex-start;
    flex-direction: column;
  }

  .timeline-ring {
    width: 160px;
    height: 160px;
  }
}
</style>
