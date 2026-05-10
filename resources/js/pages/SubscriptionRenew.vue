<template>
  <div class="renew-page">
    <div v-if="loading" class="renew-loading">
      <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
      <span>Loading renewal options...</span>
    </div>

    <div v-else-if="error" class="alert alert-danger renew-alert">
      <i class="fas fa-exclamation-circle me-2"></i>
      {{ error }}
    </div>

    <div v-else class="renew-layout">
      <section class="plans-panel">
        <div class="section-heading">
          <div>
            <router-link class="back-link" to="/subscription">
              <i class="fas fa-arrow-left me-2"></i>
              Back to Subscription
            </router-link>
            <span class="eyebrow dark">Available Plans</span>
            <h4>Select a plan</h4>
          </div>
          <span v-if="currentPlanName" class="current-plan-chip">
            Current: {{ currentPlanName }}
          </span>
        </div>

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
            <small>{{ planCountByCycle(tab.value) }} plans</small>
          </button>
        </div>

        <div v-if="filteredPlans.length" class="plan-list">
          <button
            v-for="plan in filteredPlans"
            :key="plan.id"
            type="button"
            class="renew-plan-card"
            :class="{ selected: selectedPlanId === plan.id }"
            @click="selectPlan(plan.id)"
          >
            <div class="plan-select-dot">
              <i v-if="selectedPlanId === plan.id" class="fas fa-check"></i>
            </div>
            <div class="plan-main">
              <div class="plan-title-row">
                <h5>{{ plan.display_name }}</h5>
                <span>{{ cycleLabel(plan.billing_type) }}</span>
              </div>
              <p>{{ planDescription(plan) }}</p>
              <div class="feature-pills">
                <span v-for="feature in normalizeFeatures(plan.features)" :key="`${plan.id}-${feature}`">
                  {{ featureLabel(feature) }}
                </span>
              </div>
            </div>
            <div class="plan-price">
              <strong>{{ formatCurrency(plan.price_per_sim) }}</strong>
              <span>per SIM</span>
            </div>
          </button>
        </div>

        <div v-else class="empty-plans">
          No {{ cycleLabel(activeBillingCycle).toLowerCase() }} plans are available right now.
        </div>
      </section>

      <aside class="quote-panel">
        <div class="summary-card">
          <div class="summary-header">
            <div>
              <span class="eyebrow dark">Calculation</span>
              <h4>Renewal Summary</h4>
            </div>
            <span class="summary-badge">{{ paymentStatusLabel }}</span>
          </div>

          <div class="summary-selected">
            <div class="summary-plan-icon">
              <i class="fas fa-layer-group"></i>
            </div>
            <div>
              <span>Selected Plan</span>
              <strong>{{ selectedPlan?.display_name || 'Select a plan' }}</strong>
            </div>
          </div>

          <div class="summary-quantity">
            <div class="summary-quantity-header">
              <div>
                <span>SIM Quantity</span>
                <strong>{{ quotedQuantity }} SIMs</strong>
              </div>
              <small>Minimum {{ MIN_SIM_QUANTITY }} SIMs</small>
            </div>

            <div class="quantity-control">
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

            <div class="quantity-note">
              <i class="fas fa-info-circle me-2"></i>
              Current subscription quantity: {{ currentSimQuantityLabel }}
            </div>
          </div>

          <div class="calculation-lines">
            <div>
              <span>Price per SIM</span>
              <strong>{{ selectedPlan ? formatCurrency(selectedPlan.price_per_sim) : '-' }}</strong>
            </div>
            <div>
              <span>SIM Quantity</span>
              <strong>{{ quotedQuantity }}</strong>
            </div>
            <div>
              <span>Billing Cycle</span>
              <strong>{{ selectedPlan ? cycleLabel(selectedPlan.billing_type) : '-' }}</strong>
            </div>
            <div>
              <span>Subtotal</span>
              <strong>{{ selectedPlan ? formatCurrency(subtotal) : '-' }}</strong>
            </div>
          </div>

          <div class="total-row">
            <span>Total</span>
            <strong>{{ selectedPlan ? formatCurrency(total) : '-' }}</strong>
          </div>

          <p v-if="paymentMessage" class="payment-note" :class="paymentMessageType">
            {{ paymentMessage }}
          </p>
          <p v-else class="payment-note">
            You will be redirected to Razorpay TEST checkout for secure payment verification.
          </p>

          <button type="button" class="btn renew-final-button" :disabled="!canRenew" @click="renewPlan">
            <span v-if="paymentLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            <i v-else class="fas fa-lock me-2"></i>
            {{ paymentLoading ? 'Opening Checkout...' : 'Renew Plan' }}
          </button>
        </div>
      </aside>
    </div>
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
.renew-page {
  --renew-ink: #101828;
  --renew-muted: #667085;
  --renew-line: #e5ebf3;
  --renew-blue: #2563eb;
  --renew-teal: #0fbaa3;
  --renew-orange: #f97316;
  --renew-navy: #10233f;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.back-link {
  display: flex;
  align-items: center;
  width: max-content;
  margin-bottom: 18px;
  color: var(--renew-blue);
  font-weight: 800;
  text-decoration: none;
  line-height: 1;
}

.back-link:hover {
  color: var(--renew-navy);
}

.eyebrow {
  display: inline-flex;
  font-size: 0.72rem;
  font-weight: 900;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #7c8aa0;
}

.eyebrow.dark {
  color: #7c8aa0;
}

.renew-loading,
.renew-alert,
.plans-panel,
.summary-card {
  border: 1px solid var(--renew-line);
  border-radius: 26px;
  background: #fff;
  box-shadow: 0 18px 40px rgba(16, 32, 51, 0.06);
}

.renew-loading {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 28px;
  color: var(--renew-muted);
  font-weight: 800;
}

.renew-alert {
  padding: 18px 20px;
}

.renew-layout {
  display: grid;
  grid-template-columns: minmax(0, 1.25fr) minmax(340px, 0.75fr);
  gap: 22px;
  align-items: start;
}

.plans-panel,
.summary-card {
  padding: 30px;
}

.section-heading,
.summary-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 18px;
  margin-bottom: 26px;
}

.section-heading h4,
.summary-card h4 {
  margin: 10px 0 0;
  color: var(--renew-ink);
  font-size: 1.35rem;
  font-weight: 900;
  letter-spacing: -0.03em;
}

.current-plan-chip,
.summary-badge {
  display: inline-flex;
  align-items: center;
  padding: 8px 12px;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 900;
  white-space: nowrap;
}

.current-plan-chip {
  color: #175cd3;
  background: #eff8ff;
  margin-top: 32px;
}

.summary-badge {
  color: #b54708;
  background: #fffaeb;
}

.billing-tabs {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
  padding: 6px;
  margin-bottom: 22px;
  border: 1px solid var(--renew-line);
  border-radius: 18px;
  background: #f8fafc;
}

.billing-tab {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  min-height: 50px;
  padding: 10px 14px;
  border: 0;
  border-radius: 14px;
  color: #64748b;
  background: transparent;
  font-weight: 900;
  transition: background 0.18s ease, color 0.18s ease, box-shadow 0.18s ease;
}

.billing-tab span {
  font-size: 0.96rem;
}

.billing-tab small {
  color: inherit;
  opacity: 0.72;
  font-size: 0.74rem;
  font-weight: 900;
}

.billing-tab.active {
  color: #175cd3;
  background: #fff;
  box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
}

.plan-list {
  display: grid;
  gap: 16px;
}

.renew-plan-card {
  width: 100%;
  display: grid;
  grid-template-columns: auto minmax(0, 1fr) auto;
  gap: 16px;
  align-items: flex-start;
  padding: 22px;
  text-align: left;
  border: 1px solid var(--renew-line);
  border-radius: 22px;
  background:
    radial-gradient(circle at 0 0, rgba(37, 99, 235, 0.06), transparent 26%),
    #fff;
  transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
}

.renew-plan-card:hover,
.renew-plan-card.selected {
  transform: translateY(-2px);
  border-color: rgba(37, 99, 235, 0.5);
  box-shadow: 0 18px 38px rgba(37, 99, 235, 0.12);
}

.renew-plan-card.selected {
  background:
    radial-gradient(circle at 0 0, rgba(15, 186, 163, 0.16), transparent 30%),
    linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
}

.plan-select-dot {
  width: 34px;
  height: 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #cbd5e1;
  border-radius: 999px;
  color: #fff;
  background: #fff;
}

.renew-plan-card.selected .plan-select-dot {
  border-color: var(--renew-teal);
  background: var(--renew-teal);
}

.plan-title-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.plan-title-row h5 {
  margin: 0;
  color: var(--renew-ink);
  font-size: 1.18rem;
  font-weight: 900;
}

.plan-title-row span {
  padding: 4px 9px;
  border-radius: 999px;
  color: #475467;
  background: #f2f4f7;
  font-size: 0.72rem;
  font-weight: 900;
}

.plan-main p,
.payment-note {
  color: var(--renew-muted);
  line-height: 1.58;
}

.plan-main p {
  margin: 7px 0 12px;
}

.feature-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.feature-pills span {
  padding: 5px 9px;
  border-radius: 999px;
  color: #175cd3;
  background: #eff8ff;
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: capitalize;
}

.plan-price {
  text-align: right;
}

.plan-price strong {
  display: block;
  color: var(--renew-ink);
  font-size: 1.4rem;
  font-weight: 900;
  letter-spacing: -0.05em;
}

.plan-price span {
  color: var(--renew-muted);
  font-size: 0.78rem;
  font-weight: 800;
}

.empty-plans {
  padding: 28px;
  border: 1px dashed #cbd5e1;
  border-radius: 18px;
  color: var(--renew-muted);
  background: #f8fafc;
}

.quote-panel {
  position: sticky;
  top: 86px;
}

.quantity-control {
  display: grid;
  grid-template-columns: 54px minmax(0, 1fr) 54px;
  gap: 10px;
  align-items: center;
}

.quantity-control button,
.quantity-control input {
  height: 54px;
  border: 1px solid var(--renew-line);
  border-radius: 16px;
}

.quantity-control button {
  color: var(--renew-navy);
  background: #f8fafc;
  font-weight: 900;
}

.quantity-control button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.quantity-control input {
  width: 100%;
  text-align: center;
  color: var(--renew-ink);
  font-size: 1.45rem;
  font-weight: 900;
  outline: none;
}

.quantity-control input:focus {
  border-color: var(--renew-blue);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.quantity-note {
  margin-top: 14px;
  padding: 12px;
  border-radius: 14px;
  color: #175cd3;
  background: #eff8ff;
  font-weight: 800;
  font-size: 0.88rem;
}

.summary-quantity {
  margin-top: 14px;
  padding: 16px;
  border: 1px solid var(--renew-line);
  border-radius: 18px;
  background:
    radial-gradient(circle at 0 0, rgba(15, 186, 163, 0.12), transparent 32%),
    #fbfdff;
}

.summary-quantity-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 14px;
}

.summary-quantity-header span {
  display: block;
  color: var(--renew-muted);
  font-size: 0.8rem;
  font-weight: 900;
}

.summary-quantity-header strong {
  display: block;
  color: var(--renew-ink);
  font-size: 1.3rem;
  font-weight: 900;
  letter-spacing: -0.04em;
}

.summary-quantity-header small {
  padding: 6px 10px;
  border-radius: 999px;
  color: #175cd3;
  background: #eff8ff;
  font-weight: 900;
  white-space: nowrap;
}

.summary-selected {
  display: flex;
  gap: 12px;
  align-items: center;
  padding: 16px;
  border-radius: 18px;
  background: #f8fafc;
  border: 1px solid var(--renew-line);
}

.summary-plan-icon {
  width: 56px;
  height: 56px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 auto;
  border-radius: 14px;
  color: #fff;
  background: linear-gradient(135deg, var(--renew-navy), var(--renew-blue));
}

.summary-selected span,
.calculation-lines span,
.total-row span {
  display: block;
  color: var(--renew-muted);
  font-size: 0.8rem;
  font-weight: 900;
}

.summary-selected strong {
  display: block;
  color: var(--renew-ink);
  font-size: 1.04rem;
  font-weight: 900;
}

.calculation-lines {
  display: grid;
  gap: 12px;
  margin-top: 16px;
}

.calculation-lines div {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--renew-line);
}

.calculation-lines strong {
  color: var(--renew-ink);
  font-weight: 900;
  text-align: right;
}

.total-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-top: 18px;
  padding: 18px;
  border-radius: 18px;
  color: #fff;
  background:
    radial-gradient(circle at 0 0, rgba(249, 115, 22, 0.34), transparent 36%),
    linear-gradient(135deg, var(--renew-navy), #173e62);
}

.total-row span {
  color: rgba(255, 255, 255, 0.72);
}

.total-row strong {
  color: #fff;
  font-size: 1.8rem;
  font-weight: 900;
  letter-spacing: -0.05em;
}

.payment-note {
  margin: 14px 0;
  font-size: 0.86rem;
}

.payment-note.success {
  color: #067647;
  font-weight: 800;
}

.payment-note.danger {
  color: #b42318;
  font-weight: 800;
}

.renew-final-button {
  width: 100%;
  border: 0;
  color: #fff;
  font-weight: 900;
  background: linear-gradient(135deg, var(--renew-orange), #ffb454);
  box-shadow: 0 16px 32px rgba(249, 115, 22, 0.24);
}

.renew-final-button:disabled {
  opacity: 0.55;
  box-shadow: none;
}

@media (max-width: 1199.98px) {
  .renew-layout {
    grid-template-columns: 1fr;
  }

  .quote-panel {
    position: static;
  }
}

@media (max-width: 767.98px) {
  .section-heading,
  .summary-header,
  .summary-quantity-header {
    flex-direction: column;
  }

  .current-plan-chip {
    margin-top: 0;
  }

  .renew-plan-card {
    grid-template-columns: auto minmax(0, 1fr);
  }

  .plan-price {
    grid-column: 2;
    text-align: left;
  }

  .quantity-control {
    grid-template-columns: 48px minmax(0, 1fr) 48px;
  }

  .billing-tabs {
    grid-template-columns: 1fr;
  }
}
</style>
