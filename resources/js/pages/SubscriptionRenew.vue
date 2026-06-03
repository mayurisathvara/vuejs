<template>
  <div class="rn-page">

    <!-- Loading -->
    <div v-if="loading" class="rn-state">
      <div class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></div>
      <span>Loading renewal options…</span>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="rn-state rn-state--err">
      <i class="fas fa-exclamation-circle"></i>
      {{ error }}
    </div>

    <template v-else>
      <!-- Page Header -->
      <header class="rn-hdr rn-card">
        <div class="rn-hdr-info">
          <router-link class="back-link" to="/subscription">
            <i class="fas fa-arrow-left"></i>
            Back to Subscription
          </router-link>
          <h4 class="rn-hdr-title">Select a Plan</h4>
          <p class="rn-hdr-sub">Choose your billing cycle and plan to renew or upgrade your subscription.</p>
        </div>
        <span v-if="currentPlanName" class="current-chip">
          <i class="fas fa-tag"></i>
          Current: {{ currentPlanName }}
        </span>
      </header>

      <!-- 2-column layout -->
      <div class="rn-layout">

        <!-- Plans Panel -->
        <section class="rn-card plans-panel">

          <!-- Billing tabs -->
          <div v-if="billingPlans.length" class="billing-tabs" role="tablist" aria-label="Billing cycle">
            <button
              v-for="tab in billingTabs"
              :key="tab.value"
              type="button"
              class="billing-tab"
              :class="{ active: activeBillingCycle === tab.value }"
              role="tab"
              :aria-selected="activeBillingCycle === tab.value"
              @click="selectBillingCycle(tab.value)"
            >
              <div class="bt-left">
                <span class="bt-icon">
                  <i :class="tab.value === 'monthly' ? 'fas fa-calendar' : 'fas fa-calendar-alt'"></i>
                </span>
                <div class="bt-text">
                  <span class="bt-label">{{ tab.label }}</span>
                  <span class="bt-sub">{{ planCountByCycle(tab.value) }} plan{{ planCountByCycle(tab.value) === 1 ? '' : 's' }} available</span>
                </div>
              </div>
              <span class="bt-check" v-if="activeBillingCycle === tab.value">
                <i class="fas fa-check"></i>
              </span>
            </button>
          </div>

          <!-- Plan list -->
          <div v-if="filteredPlans.length" class="plan-list">
            <button
              v-for="plan in filteredPlans"
              :key="plan.id"
              type="button"
              class="plan-item"
              :class="{ selected: selectedPlanId === plan.id, 'plan-item--popular': plan.name === 'advance' }"
              @click="selectPlan(plan.id)"
            >
              <!-- Popular badge -->
              <span v-if="plan.name === 'advance'" class="popular-badge">
                <i class="fas fa-bolt"></i> Most Popular
              </span>

              <!-- Top row: icon + radio -->
              <div class="plan-item-top">
                <div class="plan-avatar" :class="`plan-avatar--${plan.name}`">
                  <i :class="planIcon(plan)"></i>
                </div>
                <div class="plan-sel-dot" :class="{ checked: selectedPlanId === plan.id }">
                  <i v-if="selectedPlanId === plan.id" class="fas fa-check"></i>
                </div>
              </div>

              <!-- Name + cycle -->
              <div class="plan-name-row">
                <h5>{{ plan.display_name }}</h5>
                <span class="cycle-badge">{{ cycleLabel(plan.billing_type) }}</span>
              </div>

              <!-- Description -->
              <p class="plan-desc-text">{{ planDescription(plan) }}</p>

              <!-- Feature pills -->
              <div class="feat-pills" v-if="normalizeFeatures(plan.features).length">
                <span v-for="feat in normalizeFeatures(plan.features)" :key="`${plan.id}-${feat}`">
                  <i class="fas fa-check"></i> {{ featureLabel(feat) }}
                </span>
              </div>

              <!-- Price -->
              <div class="plan-price">
                <strong>{{ formatCurrency(plan.price_per_sim) }}</strong>
                <span>{{ plan.billing_type === 'yearly' ? 'per SIM / mo · billed yearly' : 'per SIM / mo' }}</span>
              </div>
            </button>
          </div>

          <p v-else class="empty-plans">
            No {{ cycleLabel(activeBillingCycle).toLowerCase() }} plans are available right now.
          </p>
        </section>

        <!-- Summary / Quote Panel -->
        <aside class="quote-col">
          <div class="rn-card summary-card">

            <!-- Header -->
            <div class="sum-hdr">
              <div>
                <span class="kicker">Calculation</span>
                <h5 class="sum-title">Renewal Summary</h5>
              </div>
              <span class="sum-badge" :class="`sum-badge--${paymentMessageType || 'default'}`">
                {{ paymentStatusLabel }}
              </span>
            </div>

            <!-- Selected plan display -->
            <div class="sel-plan">
              <div class="sel-plan-icon">
                <i class="fas fa-layer-group"></i>
              </div>
              <div>
                <span class="kicker">Selected Plan</span>
                <strong>{{ selectedPlan?.display_name || 'No plan selected' }}</strong>
              </div>
            </div>

            <!-- SIM Quantity -->
            <div class="qty-block">
              <div class="qty-block-hdr">
                <div>
                  <span class="kicker">SIM Quantity</span>
                  <strong class="qty-value">{{ quotedQuantity }} SIMs</strong>
                </div>
                <span class="min-tag">Min {{ MIN_SIM_QUANTITY }}</span>
              </div>

              <div class="qty-ctrl">
                <button type="button" :disabled="simQuantity <= MIN_SIM_QUANTITY" @click="decreaseQuantity">
                  <i class="fas fa-minus"></i>
                </button>
                <input
                  v-model.number="simQuantity"
                  type="number"
                  :min="MIN_SIM_QUANTITY"
                  step="1"
                  @blur="normalizeQuantity"
                  aria-label="SIM quantity"
                />
                <button type="button" @click="increaseQuantity">
                  <i class="fas fa-plus"></i>
                </button>
              </div>

              <div class="qty-note">
                <i class="fas fa-info-circle"></i>
                Current subscription: {{ currentSimQuantityLabel }}
              </div>
            </div>

            <!-- Calculation breakdown -->
            <div class="calc-rows">
              <div class="calc-row">
                <span>Price per SIM</span>
                <strong>{{ selectedPlan ? formatCurrency(selectedPlan.price_per_sim) + ' / mo' : '–' }}</strong>
              </div>
              <div v-if="selectedPlan?.billing_type === 'yearly'" class="calc-row calc-row--accent">
                <span>× Months</span>
                <strong>12</strong>
              </div>
              <div class="calc-row">
                <span>SIM Quantity</span>
                <strong>{{ quotedQuantity }}</strong>
              </div>
              <div class="calc-row">
                <span>Billing Cycle</span>
                <strong>{{ selectedPlan ? cycleLabel(selectedPlan.billing_type) : '–' }}</strong>
              </div>
              <div class="calc-row">
                <span>Subtotal</span>
                <strong>{{ selectedPlan ? formatCurrency(subtotal) : '–' }}</strong>
              </div>
            </div>

            <!-- Total -->
            <div class="total-row">
              <span>Total Due</span>
              <strong>{{ selectedPlan ? formatCurrency(total) : '–' }}</strong>
            </div>

            <!-- Payment note -->
            <p v-if="paymentMessage" class="pay-note" :class="paymentMessageType">{{ paymentMessage }}</p>
            <p v-else class="pay-note">One-time Razorpay payment — you control each renewal manually.</p>

            <!-- Pay button -->
            <button type="button" class="pay-btn" :disabled="!canRenew" @click="renewPlan">
              <span v-if="paymentLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              <i v-else class="fas fa-lock"></i>
              <span v-if="paymentLoading">Opening Checkout…</span>
              <span v-else>Pay Once</span>
            </button>
          </div>
        </aside>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import { showError, showSuccess } from '@/services/toast'

const MIN_SIM_QUANTITY = 5

const router = useRouter()
const loading = ref(false)
const error = ref('')
const plans = ref([])
const subscription = ref(null)
const stats = ref({})
const selectedPlanId = ref(null)
const simQuantity = ref(MIN_SIM_QUANTITY)
const activeBillingCycle = ref('monthly')
const paymentLoading = ref(false)
const paymentMessage = ref('')
const paymentMessageType = ref('')
const billingTabs = [
  { value: 'monthly', label: 'Monthly' },
  { value: 'yearly', label: 'Yearly' }
]

const featureNames = {
  call_tracking: 'Call Tracking',
  reports: 'Reports',
  ai: 'AI',
  api: 'API'
}

const fetchRenewalData = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await api.get('/subscription/renewal-data')
    const data = response.data?.data || {}

    plans.value = Array.isArray(data.plans) ? data.plans : []
    subscription.value = data.subscription || null
    stats.value = data.stats || {}

    // Guard: mirrors canRequestRenewal in Subscription.vue exactly.
    // Free trial users can upgrade at any time.
    // Expired (status or negative days) → always allow.
    // Active with no end date → block. Active with days > threshold → block.
    const s = stats.value
    const isCurrentlyTrial = s.plan_slug === 'free_trial'
    const isExpiredOrNegative =
      s.subscription_status === 'expired' ||
      (s.days_until_expiry !== null && s.days_until_expiry < 0)

    if (!isCurrentlyTrial && !isExpiredOrNegative) {
      const days = s.days_until_expiry
      const threshold = s.renewal_days_before ?? 2
      if (days === null) {
        showError('Renewal is not available for this plan type.')
        router.replace('/subscription')
        return
      }
      if (days > threshold) {
        showError(`Renewal opens ${threshold} day${threshold === 1 ? '' : 's'} before expiry.`)
        router.replace('/subscription')
        return
      }
    }

    const currentQuantity = Number(subscription.value?.sim_limit ?? stats.value?.sim_limit ?? MIN_SIM_QUANTITY)
    simQuantity.value = Math.max(MIN_SIM_QUANTITY, currentQuantity || MIN_SIM_QUANTITY)

    const currentPlanId = subscription.value?.plan_id
    const currentPlan = plans.value.find((plan) => plan.id === currentPlanId && isBillingPlan(plan))
    const firstPaidPlan = billingPlans.value.find((plan) => Number(plan.price_per_sim || 0) > 0)
    const fallbackPlan = currentPlan || firstPaidPlan || billingPlans.value[0] || null

    activeBillingCycle.value = billingTabs.some((tab) => tab.value === fallbackPlan?.billing_type)
      ? fallbackPlan.billing_type
      : 'monthly'
    selectedPlanId.value = fallbackPlan?.id || null
  } catch (err) {
    error.value = err.response?.data?.message || 'Unable to load renewal options. Please try again.'
  } finally {
    loading.value = false
  }
}

const isBillingPlan = (plan) => ['monthly', 'yearly'].includes(plan?.billing_type)
const billingPlans = computed(() => plans.value.filter(isBillingPlan))
const filteredPlans = computed(() => billingPlans.value.filter((plan) => plan.billing_type === activeBillingCycle.value))
const selectedPlan = computed(() => plans.value.find((plan) => plan.id === selectedPlanId.value) || null)
const currentPlanName = computed(() => subscription.value?.plan?.display_name || stats.value?.plan_name || '')
const currentSimQuantityLabel = computed(() => {
  const quantity = Number(subscription.value?.sim_limit ?? stats.value?.sim_limit ?? 0)
  return quantity > 0 ? `${quantity} SIMs` : 'No previous quantity'
})
const quotedQuantity = computed(() => {
  const quantity = Number.parseInt(simQuantity.value, 10)
  return Number.isFinite(quantity) ? Math.max(MIN_SIM_QUANTITY, quantity) : MIN_SIM_QUANTITY
})
const billingMonths = computed(() => selectedPlan.value?.billing_type === 'yearly' ? 12 : 1)
const subtotal = computed(() => Number(selectedPlan.value?.price_per_sim || 0) * quotedQuantity.value * billingMonths.value)
const total = computed(() => subtotal.value)
const canRenew = computed(() => Boolean(selectedPlan.value) && !loading.value && !paymentLoading.value)
const paymentStatusLabel = computed(() => {
  if (paymentLoading.value) return 'Processing'
  if (paymentMessageType.value === 'success') return 'Success'
  if (paymentMessageType.value === 'danger') return 'Failed'
  return 'Razorpay Test'
})

const planIcon = (plan) => {
  if (plan.name === 'advance') return 'fas fa-bolt'
  if (plan.name === 'basic') return 'fas fa-layer-group'
  return 'fas fa-cube'
}

const selectPlan = (planId) => {
  selectedPlanId.value = planId
}

const selectBillingCycle = (cycle) => {
  activeBillingCycle.value = cycle

  if (!filteredPlans.value.some((plan) => plan.id === selectedPlanId.value)) {
    selectedPlanId.value = filteredPlans.value[0]?.id || null
  }
}

const planCountByCycle = (cycle) => billingPlans.value.filter((plan) => plan.billing_type === cycle).length

const normalizeQuantity = () => {
  const normalized = Number.parseInt(simQuantity.value, 10)
  simQuantity.value = Number.isFinite(normalized) ? Math.max(MIN_SIM_QUANTITY, normalized) : MIN_SIM_QUANTITY
}

const decreaseQuantity = () => {
  simQuantity.value = Math.max(MIN_SIM_QUANTITY, Number(simQuantity.value || MIN_SIM_QUANTITY) - 1)
}

const increaseQuantity = () => {
  simQuantity.value = Number(simQuantity.value || MIN_SIM_QUANTITY) + 1
}

const loadRazorpayCheckout = () => {
  return new Promise((resolve, reject) => {
    if (window.Razorpay) {
      resolve()
      return
    }

    const script = document.createElement('script')
    script.src = 'https://checkout.razorpay.com/v1/checkout.js'
    script.async = true
    script.onload = resolve
    script.onerror = () => reject(new Error('Unable to load Razorpay checkout.'))
    document.head.appendChild(script)
  })
}

const renewPlan = async () => {
  if (!selectedPlan.value) return
  normalizeQuantity()
  await startOneTimeCheckout()
}

// ---------------------------------------------------------------------------
// One-time checkout via Razorpay Orders API
// ---------------------------------------------------------------------------

const startOneTimeCheckout = async () => {
  paymentLoading.value = true
  paymentMessage.value = ''
  paymentMessageType.value = ''

  try {
    await loadRazorpayCheckout()

    const response = await api.post('/subscription/renew/order', {
      subscription_plan_id: selectedPlan.value.id,
      sim_quantity: quotedQuantity.value
    })

    const data  = response.data?.data || {}
    const order = data.order          || {}
    const quote = data.quote          || {}

    if (!order.id || !data.key) {
      throw new Error('Razorpay order response is incomplete.')
    }

    const user         = JSON.parse(localStorage.getItem('user') || '{}')
    let paymentHandled = false

    const checkout = new window.Razorpay({
      key:         data.key,
      amount:      order.amount,
      currency:    order.currency || quote.currency || 'INR',
      name:        'Callytics',
      description: `${quote.plan_name || selectedPlan.value.display_name} subscription renewal`,
      order_id:    order.id,
      prefill: {
        name:    user.name   || '',
        email:   user.email  || '',
        contact: user.mobile || user.phone || ''
      },
      notes: {
        subscription_plan_id: String(selectedPlan.value.id),
        sim_quantity:         String(quotedQuantity.value)
      },
      method: { upi: true, card: true, netbanking: true, wallet: true },
      theme:  { color: '#f97316' },
      handler: async (payment) => {
        paymentHandled = true
        await verifyOneTimePayment(payment)
      },
      modal: {
        ondismiss: () => {
          if (paymentHandled) return
          paymentLoading.value = false
          paymentMessage.value = 'Checkout closed before payment completion.'
          paymentMessageType.value = 'danger'
        }
      }
    })

    checkout.on('payment.failed', (failure) => {
      paymentHandled = true
      paymentLoading.value = false
      paymentMessage.value = failure.error?.description || 'Payment failed. Please try again.'
      paymentMessageType.value = 'danger'
      showError(paymentMessage.value)
    })

    checkout.open()
  } catch (err) {
    paymentLoading.value = false
    paymentMessage.value = err.response?.data?.message || err.message || 'Unable to start renewal payment.'
    paymentMessageType.value = 'danger'
    showError(paymentMessage.value)
  }
}

const verifyOneTimePayment = async (payment) => {
  try {
    const response = await api.post('/subscription/renew/verify', {
      razorpay_order_id:   payment.razorpay_order_id,
      razorpay_payment_id: payment.razorpay_payment_id,
      razorpay_signature:  payment.razorpay_signature
    })

    const message = response.data?.message || 'Payment verified and subscription renewed.'
    showSuccess(message)
    router.replace('/subscription')
  } catch (err) {
    paymentMessage.value = err.response?.data?.message || 'Payment verification failed. Please contact support.'
    paymentMessageType.value = 'danger'
    showError(paymentMessage.value)
  } finally {
    paymentLoading.value = false
  }
}

const normalizeFeatures = (features) => {
  return Array.isArray(features) ? features : []
}

const featureLabel = (feature) => {
  return featureNames[feature] || String(feature).replace(/[_-]/g, ' ')
}

const cycleLabel = (cycle) => {
  if (!cycle) return 'Billing Cycle'
  return String(cycle)
    .replace(/[_-]/g, ' ')
    .replace(/\w\S*/g, (word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
}

const planDescription = (plan) => {
  if (plan.name === 'free_trial') return `Trial access for ${plan.trial_days || 14} days before moving to a paid subscription.`
  if (plan.name === 'advance') return 'Advanced plan for larger teams that need API access and richer reporting.'
  if (plan.name === 'basic') return 'Essential call tracking and reporting for growing teams.'
  return 'Flexible per-SIM pricing for your organization.'
}

const formatCurrency = (value) => {
  const numeric = Number(value || 0)
  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    maximumFractionDigits: numeric % 1 === 0 ? 0 : 2
  }).format(numeric)
}

onMounted(fetchRenewalData)
</script>

<style scoped>
/* ── Design tokens ── */
.rn-page {
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

/* ── Card base ── */
.rn-card {
  border: 1px solid var(--line);
  border-radius: 20px;
  background: #fff;
  box-shadow: 0 2px 12px rgba(16, 32, 51, 0.06);
}

/* ── Shared kicker ── */
.kicker {
  display: block;
  font-size: 0.68rem;
  font-weight: 800;
  letter-spacing: 0.13em;
  text-transform: uppercase;
  color: var(--muted);
}

/* ── State boxes ── */
.rn-state {
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

.rn-state--err {
  color: #b42318;
  border-color: #fecdca;
  background: #fffbfa;
}

/* ── Page Header ── */
.rn-hdr {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  padding: 22px 26px;
  background: #fff;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--muted);
  text-decoration: none;
  margin-bottom: 12px;
  transition: color 0.15s;
}

.back-link:hover { color: var(--ink); }

.rn-hdr-title {
  margin: 0 0 6px;
  font-size: clamp(1.2rem, 1.8vw, 1.45rem);
  font-weight: 900;
  color: var(--ink);
  letter-spacing: -0.03em;
}

.rn-hdr-sub {
  margin: 0;
  font-size: 0.83rem;
  color: var(--muted);
  line-height: 1.55;
  max-width: 480px;
}

.current-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 800;
  color: var(--orange);
  background: rgba(249, 115, 22, 0.1);
  border: 1px solid rgba(249, 115, 22, 0.25);
  white-space: nowrap;
  flex-shrink: 0;
  margin-top: 4px;
}

/* ── 2-column layout ── */
.rn-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 340px;
  gap: 16px;
  align-items: start;
}

/* ── Plans Panel ── */
.plans-panel {
  padding: 22px;
}

/* ── Billing Tabs ── */
.billing-tabs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 6px;
  padding: 6px;
  background: #f1f5f9;
  border: 1px solid var(--line);
  border-radius: 18px;
  margin-bottom: 22px;
}

.billing-tab {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 12px 14px;
  border: 0;
  border-radius: 13px;
  background: transparent;
  color: var(--muted);
  cursor: pointer;
  transition: background 0.18s, color 0.18s, box-shadow 0.18s;
  text-align: left;
}

.billing-tab.active {
  background: #fff;
  color: var(--ink);
  box-shadow: 0 2px 16px rgba(15, 23, 42, 0.10);
  border: 1.5px solid rgba(249, 115, 22, 0.3);
}

/* Icon square */
.bt-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  flex-shrink: 0;
  background: rgba(0, 0, 0, 0.06);
  color: var(--muted);
  transition: background 0.18s, color 0.18s;
}

.billing-tab.active .bt-icon {
  background: linear-gradient(135deg, var(--orange), #ffb454);
  color: #fff;
}

.bt-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.bt-text {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.bt-label {
  font-size: 0.88rem;
  font-weight: 800;
  color: var(--ink);
  letter-spacing: -0.01em;
  line-height: 1;
}

.billing-tab:not(.active) .bt-label {
  color: var(--muted);
}

.bt-sub {
  font-size: 0.67rem;
  font-weight: 600;
  color: var(--muted);
  line-height: 1;
}

.billing-tab.active .bt-sub {
  color: var(--muted);
}

/* Check indicator */
.bt-check {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--orange), #ffb454);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 0.55rem;
  color: #fff;
}

/* ── Plan List — horizontal row of vertical cards ── */
.plan-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 14px;
}

/* ── Plan card (vertical) ── */
.plan-item {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 0;
  padding: 18px 16px 16px;
  text-align: left;
  border: 1.5px solid var(--line);
  border-radius: 18px;
  background: #fff;
  cursor: pointer;
  overflow: hidden;
  transition: border-color 0.15s, box-shadow 0.15s, background 0.15s, transform 0.15s;
  box-shadow: 0 1px 6px rgba(15,23,42,0.05);
}

.plan-item:hover {
  border-color: rgba(249, 115, 22, 0.35);
  box-shadow: 0 6px 20px rgba(249, 115, 22, 0.1);
  transform: translateY(-2px);
}

.plan-item.selected {
  border-color: var(--orange);
  background: linear-gradient(160deg, #fffbf7 0%, #fff7ed 100%);
  box-shadow: 0 6px 22px rgba(249, 115, 22, 0.16);
}

.plan-item--popular {
  border-color: #fed7aa;
}

.plan-item--popular.selected {
  border-color: var(--orange);
}

/* Popular badge — pinned to top-center */
.popular-badge {
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 12px;
  border-radius: 0 0 10px 10px;
  font-size: 0.63rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: #fff;
  background: linear-gradient(90deg, var(--orange), #ffb454);
  white-space: nowrap;
}

/* ── Card top row: avatar + radio ── */
.plan-item-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 14px;
  margin-top: 8px;
}

/* Plan avatar */
.plan-avatar {
  width: 44px;
  height: 44px;
  border-radius: 13px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  color: #fff;
  flex-shrink: 0;
  background: linear-gradient(135deg, #94a3b8, #64748b);
}

.plan-avatar--advance    { background: linear-gradient(135deg, var(--orange), #ffb454); }
.plan-avatar--basic      { background: linear-gradient(135deg, #fb923c, #fbbf24); }
.plan-avatar--free_trial { background: linear-gradient(135deg, #7c3aed, #a78bfa); }

/* ── Selection dot ── */
.plan-sel-dot {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  border: 2px solid #d0d8e4;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 0.58rem;
  flex-shrink: 0;
  transition: background 0.15s, border-color 0.15s;
}

.plan-sel-dot.checked {
  border-color: var(--orange);
  background: var(--orange);
}

/* ── Name + badge ── */
.plan-name-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 6px;
  margin-bottom: 6px;
}

.plan-name-row h5 {
  margin: 0;
  font-size: 0.96rem;
  font-weight: 900;
  color: var(--ink);
}

.cycle-badge {
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 0.65rem;
  font-weight: 800;
  color: #475467;
  background: #f2f4f7;
}

.plan-item.selected .cycle-badge {
  color: #c2410c;
  background: #ffedd5;
}

/* ── Description ── */
.plan-desc-text {
  margin: 0 0 10px;
  font-size: 0.77rem;
  color: var(--muted);
  line-height: 1.5;
  flex-grow: 1;
}

/* ── Feature pills ── */
.feat-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  margin-bottom: 14px;
}

.feat-pills span {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 9px;
  border-radius: 999px;
  color: #92400e;
  background: #fef3c7;
  border: 1px solid #fde68a;
  font-size: 0.67rem;
  font-weight: 700;
}

.feat-pills span i { font-size: 0.55rem; }

.plan-item.selected .feat-pills span {
  color: #c2410c;
  background: #ffedd5;
  border-color: #fed7aa;
}

/* ── Plan price — pinned to bottom ── */
.plan-price {
  padding-top: 12px;
  border-top: 1px solid var(--line);
  margin-top: auto;
}

.plan-item.selected .plan-price { border-top-color: #fed7aa; }

.plan-price strong {
  display: block;
  font-size: 1.3rem;
  font-weight: 900;
  color: var(--ink);
  letter-spacing: -0.04em;
  line-height: 1;
}

.plan-price span {
  display: block;
  font-size: 0.68rem;
  color: var(--muted);
  font-weight: 700;
  margin-top: 3px;
}

/* ── Empty plans ── */
.empty-plans {
  padding: 18px;
  border: 1px dashed #cbd5e1;
  border-radius: 14px;
  color: var(--muted);
  background: #f8fafc;
  font-size: 0.88rem;
  margin: 0;
}

/* ── Quote column ── */
.quote-col {
  position: sticky;
  top: 86px;
}

/* ── Summary Card ── */
.summary-card {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 0;
}

.sum-hdr {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 16px;
}

.sum-title {
  margin: 4px 0 0;
  font-size: 0.98rem;
  font-weight: 800;
  color: var(--ink);
}

.sum-badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 800;
  white-space: nowrap;
  flex-shrink: 0;
}

.sum-badge--default { color: #b54708; background: #fffaeb; }
.sum-badge--success { color: #067647; background: #dcfae6; }
.sum-badge--danger  { color: #b42318; background: #fee4e2; }

/* ── Selected plan row ── */
.sel-plan {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 13px 14px;
  border-radius: 14px;
  background: linear-gradient(135deg, #fff8f2, #fff3e6);
  border: 1px solid rgba(249, 115, 22, 0.18);
  margin-bottom: 14px;
}

.sel-plan-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  color: #fff;
  background: linear-gradient(135deg, var(--orange), #ffb454);
  flex-shrink: 0;
  box-shadow: 0 3px 10px rgba(249, 115, 22, 0.3);
}

.sel-plan strong {
  display: block;
  font-size: 0.92rem;
  font-weight: 800;
  color: var(--ink);
  margin-top: 3px;
}

/* ── Quantity block ── */
.qty-block {
  padding: 14px;
  border: 1px solid rgba(249, 115, 22, 0.18);
  border-radius: 14px;
  background: linear-gradient(160deg, #fff8f2 0%, #fff3e6 100%);
  margin-bottom: 14px;
}

.qty-block-hdr {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 12px;
}

.qty-value {
  display: block;
  margin-top: 3px;
  font-size: 1.15rem;
  font-weight: 900;
  color: var(--ink);
  letter-spacing: -0.03em;
}

.min-tag {
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 0.7rem;
  font-weight: 800;
  color: #c2410c;
  background: rgba(249, 115, 22, 0.1);
  white-space: nowrap;
  flex-shrink: 0;
}

/* ── Quantity control ── */
.qty-ctrl {
  display: grid;
  grid-template-columns: 44px 1fr 44px;
  gap: 8px;
  align-items: center;
}

.qty-ctrl button,
.qty-ctrl input {
  height: 44px;
  border: 1px solid rgba(249, 115, 22, 0.25);
  border-radius: 12px;
}

.qty-ctrl button {
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(249, 115, 22, 0.08);
  color: #c2410c;
  font-weight: 900;
  cursor: pointer;
  transition: background 0.15s, border-color 0.15s;
}

.qty-ctrl button:hover:not(:disabled) {
  background: rgba(249, 115, 22, 0.16);
  border-color: rgba(249, 115, 22, 0.45);
}

.qty-ctrl button:disabled { opacity: 0.45; cursor: not-allowed; }

.qty-ctrl input {
  width: 100%;
  text-align: center;
  font-size: 1.05rem;
  font-weight: 900;
  color: var(--ink);
  background: #fff;
  outline: none;
}

.qty-ctrl input:focus {
  border-color: var(--orange);
  box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
}

/* ── Quantity note ── */
.qty-note {
  display: flex;
  align-items: center;
  gap: 7px;
  margin-top: 10px;
  padding: 9px 12px;
  border-radius: 10px;
  font-size: 0.78rem;
  font-weight: 700;
  color: #c2410c;
  background: rgba(249, 115, 22, 0.08);
  border: 1px solid rgba(249, 115, 22, 0.15);
}

/* ── Calculation rows ── */
.calc-rows {
  display: flex;
  flex-direction: column;
  margin-bottom: 2px;
}

.calc-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 9px 0;
  border-bottom: 1px solid var(--line);
}

.calc-row:last-child { border-bottom: 0; }

.calc-row span {
  font-size: 0.81rem;
  color: var(--muted);
  font-weight: 600;
}

.calc-row strong {
  font-size: 0.86rem;
  font-weight: 800;
  color: var(--ink);
}

.calc-row--accent {
  background: rgba(249, 115, 22, 0.06);
  border-radius: 8px;
  padding: 9px 8px;
  margin: 0 -8px;
  border-bottom: none;
}

.calc-row--accent span { color: #c2410c; font-weight: 700; }
.calc-row--accent strong { color: var(--orange); font-size: 0.9rem; }

/* ── Total row ── */
.total-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin: 12px 0 0;
  padding: 16px 18px;
  border-radius: 16px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
}

.total-row span {
  color: var(--muted);
  font-size: 0.82rem;
  font-weight: 700;
}

.total-row strong {
  color: var(--ink);
  font-size: 1.6rem;
  font-weight: 900;
  letter-spacing: -0.04em;
}

/* ── Payment note ── */
.pay-note {
  margin: 12px 0 0;
  font-size: 0.78rem;
  color: var(--muted);
  line-height: 1.5;
}

.pay-note.success { color: #067647; font-weight: 700; }
.pay-note.danger  { color: #b42318; font-weight: 700; }

/* ── Pay button ── */
.pay-btn {
  margin-top: 16px;
  width: 100%;
  padding: 15px 16px;
  border: 0;
  border-radius: 14px;
  color: #fff;
  font-size: 0.94rem;
  font-weight: 900;
  letter-spacing: -0.01em;
  background: linear-gradient(135deg, var(--orange), #ffb454);
  box-shadow: 0 6px 22px rgba(249, 115, 22, 0.32);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  transition: box-shadow 0.15s, transform 0.15s, opacity 0.15s;
}

.pay-btn:hover:not(:disabled) {
  box-shadow: 0 8px 28px rgba(249, 115, 22, 0.42);
  transform: translateY(-1px);
}

.pay-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  box-shadow: none;
}

/* ── Responsive ── */
@media (max-width: 1100px) {
  .rn-layout {
    grid-template-columns: 1fr;
  }

  .quote-col {
    position: static;
  }

  .summary-card {
    max-width: 540px;
  }
}

@media (max-width: 767px) {
  .rn-hdr {
    flex-direction: column;
    gap: 12px;
  }

  .current-chip {
    margin-top: 0;
  }

  .plan-list {
    grid-template-columns: 1fr;
  }

  .plan-item {
    flex-direction: row;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    padding: 14px;
  }

  .plan-item-top {
    margin-bottom: 0;
    margin-top: 0;
  }

  .plan-price {
    margin-top: 0;
    padding-top: 0;
    border-top: none;
    text-align: right;
    margin-left: auto;
  }

  .qty-ctrl {
    grid-template-columns: 38px 1fr 38px;
  }

  .summary-card {
    max-width: 100%;
  }
}
</style>
