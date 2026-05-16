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
              <span>{{ tab.label }}</span>
              <small>{{ planCountByCycle(tab.value) }} plan{{ planCountByCycle(tab.value) === 1 ? '' : 's' }}</small>
            </button>
          </div>

          <!-- Plan list -->
          <div v-if="filteredPlans.length" class="plan-list">
            <button
              v-for="plan in filteredPlans"
              :key="plan.id"
              type="button"
              class="plan-item"
              :class="{ selected: selectedPlanId === plan.id }"
              @click="selectPlan(plan.id)"
            >
              <div class="plan-sel-dot">
                <i v-if="selectedPlanId === plan.id" class="fas fa-check"></i>
              </div>

              <div class="plan-body">
                <div class="plan-name-row">
                  <h5>{{ plan.display_name }}</h5>
                  <span class="cycle-badge">{{ cycleLabel(plan.billing_type) }}</span>
                </div>
                <p>{{ planDescription(plan) }}</p>
                <div class="feat-pills" v-if="normalizeFeatures(plan.features).length">
                  <span v-for="feat in normalizeFeatures(plan.features)" :key="`${plan.id}-${feat}`">
                    {{ featureLabel(feat) }}
                  </span>
                </div>
              </div>

              <div class="plan-price">
                <strong>{{ formatCurrency(plan.price_per_sim) }}</strong>
                <span>per SIM</span>
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
                <strong>{{ selectedPlan ? formatCurrency(selectedPlan.price_per_sim) : '–' }}</strong>
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
            <p v-else class="pay-note">
              You will be redirected to Razorpay TEST checkout for secure payment verification.
            </p>

            <!-- Pay button -->
            <button type="button" class="pay-btn" :disabled="!canRenew" @click="renewPlan">
              <span v-if="paymentLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              <i v-else class="fas fa-lock"></i>
              {{ paymentLoading ? 'Opening Checkout…' : 'Renew Plan' }}
            </button>
          </div>
        </aside>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '@/services/api'
import { showError, showSuccess } from '@/services/toast'

const MIN_SIM_QUANTITY = 5

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
const subtotal = computed(() => Number(selectedPlan.value?.price_per_sim || 0) * quotedQuantity.value)
const total = computed(() => subtotal.value)
const canRenew = computed(() => Boolean(selectedPlan.value) && !loading.value && !paymentLoading.value)
const paymentStatusLabel = computed(() => {
  if (paymentLoading.value) return 'Processing'
  if (paymentMessageType.value === 'success') return 'Success'
  if (paymentMessageType.value === 'danger') return 'Failed'
  return 'Razorpay Test'
})

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
  paymentLoading.value = true
  paymentMessage.value = ''
  paymentMessageType.value = ''

  try {
    await loadRazorpayCheckout()

    const response = await api.post('/subscription/renew/order', {
      subscription_plan_id: selectedPlan.value.id,
      sim_quantity: quotedQuantity.value
    })

    const data = response.data?.data || {}
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
      description: `${quote.plan_name || selectedPlan.value.display_name} subscription renewal`,
      order_id: order.id,
      prefill: {
        name: user.name || '',
        email: user.email || '',
        contact: user.mobile || user.phone || ''
      },
      notes: {
        subscription_plan_id: String(selectedPlan.value.id),
        sim_quantity: String(quotedQuantity.value)
      },
      method: {
        upi: true,
        card: true,
        netbanking: true,
        wallet: true
      },
      theme: {
        color: '#f97316'
      },
      handler: async (payment) => {
        paymentHandled = true
        await verifyPayment(payment)
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

const verifyPayment = async (payment) => {
  try {
    const response = await api.post('/subscription/renew/verify', {
      razorpay_order_id: payment.razorpay_order_id,
      razorpay_payment_id: payment.razorpay_payment_id,
      razorpay_signature: payment.razorpay_signature
    })

    paymentMessage.value = response.data?.message || 'Payment verified and subscription renewed.'
    paymentMessageType.value = 'success'
    showSuccess(paymentMessage.value)
    await fetchRenewalData()
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
  padding: 20px 24px;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: 0.82rem;
  font-weight: 800;
  color: var(--blue);
  text-decoration: none;
  margin-bottom: 10px;
}

.back-link:hover { color: var(--navy); }

.rn-hdr-title {
  margin: 0 0 4px;
  font-size: 1.1rem;
  font-weight: 900;
  color: var(--ink);
  letter-spacing: -0.02em;
}

.rn-hdr-sub {
  margin: 0;
  font-size: 0.82rem;
  color: var(--muted);
  line-height: 1.5;
  max-width: 480px;
}

.current-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 12px;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 800;
  color: #175cd3;
  background: #eff8ff;
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
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 6px;
  padding: 5px;
  background: #f4f6fa;
  border: 1px solid var(--line);
  border-radius: 14px;
  margin-bottom: 18px;
}

.billing-tab {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 9px 14px;
  border: 0;
  border-radius: 10px;
  background: transparent;
  color: var(--muted);
  cursor: pointer;
  font-weight: 700;
  transition: background 0.15s, color 0.15s, box-shadow 0.15s;
}

.billing-tab span { font-size: 0.88rem; }

.billing-tab small {
  font-size: 0.7rem;
  font-weight: 800;
  opacity: 0.72;
  color: inherit;
}

.billing-tab.active {
  background: #fff;
  color: #175cd3;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.08);
}

/* ── Plan List ── */
.plan-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.plan-item {
  width: 100%;
  display: grid;
  grid-template-columns: 22px minmax(0, 1fr) auto;
  gap: 14px;
  align-items: flex-start;
  padding: 16px 18px;
  text-align: left;
  border: 1.5px solid var(--line);
  border-radius: 16px;
  background: #fff;
  cursor: pointer;
  transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
}

.plan-item:hover {
  border-color: rgba(37, 99, 235, 0.38);
  box-shadow: 0 4px 18px rgba(37, 99, 235, 0.08);
}

.plan-item.selected {
  border-color: var(--teal);
  background: linear-gradient(160deg, #f8fffd 0%, #f0fef9 100%);
  box-shadow: 0 4px 16px rgba(18, 184, 166, 0.12);
}

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
  margin-top: 1px;
  transition: background 0.15s, border-color 0.15s;
}

.plan-item.selected .plan-sel-dot {
  border-color: var(--teal);
  background: var(--teal);
}

/* ── Plan body ── */
.plan-name-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 4px;
}

.plan-name-row h5 {
  margin: 0;
  font-size: 0.98rem;
  font-weight: 900;
  color: var(--ink);
}

.cycle-badge {
  padding: 3px 8px;
  border-radius: 999px;
  font-size: 0.68rem;
  font-weight: 800;
  color: #475467;
  background: #f2f4f7;
}

.plan-item.selected .cycle-badge {
  color: #0a7060;
  background: #d1faf4;
}

.plan-body p {
  margin: 0 0 8px;
  font-size: 0.8rem;
  color: var(--muted);
  line-height: 1.5;
}

.feat-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
}

.feat-pills span {
  padding: 3px 8px;
  border-radius: 999px;
  color: #175cd3;
  background: #eff8ff;
  font-size: 0.7rem;
  font-weight: 800;
  text-transform: capitalize;
}

/* ── Plan price ── */
.plan-price {
  text-align: right;
  flex-shrink: 0;
}

.plan-price strong {
  display: block;
  font-size: 1.15rem;
  font-weight: 900;
  color: var(--ink);
  letter-spacing: -0.04em;
  line-height: 1;
}

.plan-price span {
  display: block;
  font-size: 0.7rem;
  color: var(--muted);
  font-weight: 700;
  margin-top: 2px;
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
  gap: 10px;
  padding: 12px;
  border-radius: 14px;
  background: #f8fafc;
  border: 1px solid var(--line);
  margin-bottom: 14px;
}

.sel-plan-icon {
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 11px;
  color: #fff;
  background: linear-gradient(135deg, var(--navy), var(--blue));
  flex-shrink: 0;
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
  border: 1px solid var(--line);
  border-radius: 14px;
  background: linear-gradient(180deg, #fbfdff 0%, #f5f9ff 100%);
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
  color: #175cd3;
  background: #eff8ff;
  white-space: nowrap;
  flex-shrink: 0;
}

/* ── Quantity control ── */
.qty-ctrl {
  display: grid;
  grid-template-columns: 40px 1fr 40px;
  gap: 8px;
  align-items: center;
}

.qty-ctrl button,
.qty-ctrl input {
  height: 40px;
  border: 1px solid var(--line);
  border-radius: 11px;
}

.qty-ctrl button {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
  color: var(--navy);
  font-weight: 900;
  cursor: pointer;
  transition: background 0.15s, border-color 0.15s;
}

.qty-ctrl button:hover:not(:disabled) {
  background: #f0f4fa;
  border-color: #c9d3e0;
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
  border-color: var(--blue);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
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
  color: #175cd3;
  background: #eff8ff;
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

/* ── Total row ── */
.total-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin: 12px 0 0;
  padding: 14px 16px;
  border-radius: 14px;
  background:
    radial-gradient(circle at 0 0, rgba(249, 115, 22, 0.28), transparent 40%),
    linear-gradient(135deg, var(--navy), #173e62);
}

.total-row span {
  color: rgba(255, 255, 255, 0.72);
  font-size: 0.82rem;
  font-weight: 700;
}

.total-row strong {
  color: #fff;
  font-size: 1.5rem;
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
  margin-top: 14px;
  width: 100%;
  padding: 12px 16px;
  border: 0;
  border-radius: 13px;
  color: #fff;
  font-size: 0.88rem;
  font-weight: 800;
  background: linear-gradient(135deg, var(--orange), #ffb454);
  box-shadow: 0 6px 20px rgba(249, 115, 22, 0.28);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: box-shadow 0.15s, opacity 0.15s;
}

.pay-btn:hover:not(:disabled) {
  box-shadow: 0 8px 24px rgba(249, 115, 22, 0.38);
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

  .billing-tabs {
    grid-template-columns: 1fr;
  }

  .plan-item {
    grid-template-columns: 22px minmax(0, 1fr);
    grid-template-rows: auto auto;
  }

  .plan-price {
    grid-column: 2;
    text-align: left;
  }

  .qty-ctrl {
    grid-template-columns: 38px 1fr 38px;
  }

  .summary-card {
    max-width: 100%;
  }
}
</style>
