<template>
  <div class="reg-page">

    <!-- ── LEFT PANEL ─────────────────────────────────────────── -->
    <div class="reg-left">

      <!-- Animated background blobs -->
      <div class="blob blob-1"></div>
      <div class="blob blob-2"></div>
      <div class="blob blob-3"></div>

      <!-- Animated grid overlay -->
      <div class="grid-overlay"></div>

      <!-- Floating ring decorations -->
      <div class="ring ring-1"></div>
      <div class="ring ring-2"></div>
      <div class="ring ring-3"></div>

      <!-- Content -->
      <div class="left-content">
        <!-- Brand -->
        <div class="left-brand">
          <div class="brand-logo-wrap">
            <img :src="'/logo/login_icon.png'" alt="Callytics" class="brand-logo" />
          </div>
          <span class="brand-name">Callytics</span>
        </div>

        <!-- Headline -->
        <div class="left-headline">
          <h1>Track every call.<br><span class="headline-accent">Grow faster.</span></h1>
          <p class="left-sub">Turn every call into actionable insights. Monitor, analyze, and optimize your team's call performance in real time.</p>
          <div class="trust-line">
            <i class="fas fa-shield-alt"></i>
            Trusted by growing teams to track 10,000+ calls daily
          </div>
        </div>

        <!-- Feature list -->
        <ul class="feature-list">
          <li v-for="feat in features" :key="feat.text" class="feature-item">
            <div class="feat-icon">
              <i :class="feat.icon"></i>
            </div>
            <div class="feat-body">
              <span class="feat-title">{{ feat.title }}</span>
              <span class="feat-desc">{{ feat.desc }}</span>
            </div>
          </li>
        </ul>

        <!-- Stats row -->
        <div class="stats-row">
          <div v-for="stat in stats" :key="stat.label" class="stat-chip">
            <span class="stat-value">{{ stat.value }}</span>
            <span class="stat-label">{{ stat.label }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ── RIGHT PANEL ────────────────────────────────────────── -->
    <div class="reg-right">
      <div class="reg-form-wrap">

        <!-- Header -->
        <div class="form-header">
          <h2 class="form-title">Create your account</h2>
          <p class="form-sub">No credit card required &bull; Setup in 2 minutes &bull; SIM limit: 10</p>
        </div>

        <!-- The register form (router-view) -->
        <router-view />

        <!-- Sign-in link -->
        <p class="signin-switch">
          Already have an account?
          <router-link to="/login" class="signin-link">Sign in</router-link>
        </p>
      </div>
    </div>

  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'

// ── JSON-LD Structured Data ──────────────────────────────────────
// Helps search engines understand this is a SaaS sign-up page.
const JSON_LD_ID = 'register-jsonld'

onMounted(() => {
  if (document.getElementById(JSON_LD_ID)) return

  const schema = {
    '@context': 'https://schema.org',
    '@graph': [
      {
        '@type': 'WebPage',
        '@id': window.location.href,
        name: 'Create Your Free Callytics Account',
        description: 'Sign up for Callytics and start your 14-day free trial. Track call logs, manage SIMs, and grow your business — no credit card required.',
        url: window.location.href,
        inLanguage: 'en',
        isPartOf: { '@id': window.location.origin + '/#website' },
      },
      {
        '@type': 'WebSite',
        '@id': window.location.origin + '/#website',
        url: window.location.origin,
        name: 'Callytics',
        description: 'Call analytics and SIM management platform for growing teams.',
        publisher: { '@id': window.location.origin + '/#organization' },
      },
      {
        '@type': 'Organization',
        '@id': window.location.origin + '/#organization',
        name: 'Callytics',
        url: window.location.origin,
        logo: {
          '@type': 'ImageObject',
          url: window.location.origin + '/logo/logo.png',
        },
        sameAs: [],
      },
      {
        '@type': 'SoftwareApplication',
        name: 'Callytics',
        applicationCategory: 'BusinessApplication',
        operatingSystem: 'Web',
        offers: {
          '@type': 'Offer',
          price: '0',
          priceCurrency: 'INR',
          description: '14-day free trial — no credit card required',
        },
      },
    ],
  }

  const script = document.createElement('script')
  script.id        = JSON_LD_ID
  script.type      = 'application/ld+json'
  script.innerHTML = JSON.stringify(schema)
  document.head.appendChild(script)
})

onUnmounted(() => {
  document.getElementById(JSON_LD_ID)?.remove()
})

// ── Left-panel content ───────────────────────────────────────────
const features = [
  {
    icon: 'fas fa-chart-line',
    title: 'Real-time Call Analytics',
    desc: 'Monitor every inbound and outbound call with live insights.',
  },
  {
    icon: 'fas fa-users',
    title: 'Team Management',
    desc: 'Manage teams and SIMs effortlessly from one centralized dashboard.',
  },
  {
    icon: 'fas fa-sliders-h',
    title: 'Smart SIM Control',
    desc: 'Control SIM usage, limits, and access in one place.',
  },
  {
    icon: 'fas fa-chart-bar',
    title: 'Powerful Reports',
    desc: 'Generate detailed reports to improve performance and conversions.',
  },
]

const stats = [
  { value: '10K+', label: 'Calls tracked daily' },
  { value: '99.9%', label: 'Uptime SLA' },
  { value: '14 days', label: 'Free trial' },
]
</script>

<style scoped>
/* ── Base ──────────────────────────────────────────────────────── */
.reg-page {
  display: flex;
  min-height: 100vh;
}

/* ── LEFT PANEL ────────────────────────────────────────────────── */
.reg-left {
  position: relative;
  width: 46%;
  flex-shrink: 0;
  overflow: hidden;
  background: linear-gradient(145deg, #0f172a 0%, #1e1b4b 40%, #312e81 75%, #1e1b4b 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Animated gradient blobs */
.blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.55;
  animation: blobFloat 12s ease-in-out infinite alternate;
}

.blob-1 {
  width: 520px;
  height: 520px;
  background: radial-gradient(circle, #7c3aed 0%, #4f46e5 60%, transparent 100%);
  top: -120px;
  left: -100px;
  animation-duration: 14s;
}

.blob-2 {
  width: 380px;
  height: 380px;
  background: radial-gradient(circle, #f97316 0%, #ec4899 60%, transparent 100%);
  bottom: -60px;
  right: -80px;
  animation-duration: 18s;
  animation-delay: -6s;
}

.blob-3 {
  width: 260px;
  height: 260px;
  background: radial-gradient(circle, #06b6d4 0%, #3b82f6 60%, transparent 100%);
  top: 50%;
  left: 55%;
  transform: translate(-50%, -50%);
  animation-duration: 10s;
  animation-delay: -3s;
}

@keyframes blobFloat {
  0%   { transform: translateY(0) scale(1); }
  50%  { transform: translateY(-30px) scale(1.06); }
  100% { transform: translateY(15px) scale(0.96); }
}

/* Subtle dot grid */
.grid-overlay {
  position: absolute;
  inset: 0;
  background-image:
    radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px);
  background-size: 28px 28px;
  pointer-events: none;
}

/* Decorative rings */
.ring {
  position: absolute;
  border-radius: 50%;
  border: 1px solid rgba(255,255,255,0.07);
  animation: ringPulse 8s ease-in-out infinite;
}

.ring-1 {
  width: 320px;
  height: 320px;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  animation-delay: 0s;
}

.ring-2 {
  width: 500px;
  height: 500px;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  animation-delay: -2s;
}

.ring-3 {
  width: 700px;
  height: 700px;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  animation-delay: -4s;
  border-color: rgba(255,255,255,0.04);
}

@keyframes ringPulse {
  0%, 100% { opacity: 0.5; transform: translate(-50%, -50%) scale(1); }
  50%       { opacity: 1;   transform: translate(-50%, -50%) scale(1.04); }
}

/* Left content */
.left-content {
  position: relative;
  z-index: 2;
  padding: 3rem 3.5rem;
  color: #fff;
  max-width: 500px;
  width: 100%;
}

/* Brand */
.left-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 3rem;
}

.brand-logo-wrap {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(0,0,0,0.4);
  flex-shrink: 0;
}

.brand-logo {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.brand-name {
  font-size: 1.35rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #fff;
}

/* Headline */
.left-headline {
  margin-bottom: 2.5rem;
}

.left-headline h1 {
  font-size: 2.6rem;
  font-weight: 800;
  line-height: 1.18;
  letter-spacing: -0.03em;
  color: #fff;
  margin-bottom: 0.9rem;
}

.headline-accent {
  background: linear-gradient(90deg, #f97316, #fb923c, #fbbf24);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.left-sub {
  color: rgba(255,255,255,0.62);
  font-size: 1.05rem;
  line-height: 1.6;
  margin: 0 0 1rem;
}

.trust-line {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: 0.8rem;
  font-weight: 600;
  color: rgba(255,255,255,0.5);
  background: rgba(255,255,255,0.07);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 20px;
  padding: 0.3rem 0.85rem;
}

.trust-line i {
  color: #34d399;
  font-size: 0.75rem;
}

/* Feature list */
.feature-list {
  list-style: none;
  padding: 0;
  margin: 0 0 2.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
}

.feature-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  animation: fadeSlideUp 0.5s ease both;
}

.feature-item:nth-child(1) { animation-delay: 0.1s; }
.feature-item:nth-child(2) { animation-delay: 0.2s; }
.feature-item:nth-child(3) { animation-delay: 0.3s; }
.feature-item:nth-child(4) { animation-delay: 0.4s; }

@keyframes fadeSlideUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

.feat-icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: #fbbf24;
  font-size: 0.95rem;
  border: 1px solid rgba(255,255,255,0.12);
}

.feat-body {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.feat-title {
  font-weight: 700;
  font-size: 0.95rem;
  color: #fff;
  line-height: 1.3;
}

.feat-desc {
  font-size: 0.83rem;
  color: rgba(255,255,255,0.52);
  line-height: 1.4;
}

/* Stats row */
.stats-row {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255,255,255,0.1);
}

.stat-chip {
  display: flex;
  flex-direction: column;
  gap: 2px;
  background: rgba(255,255,255,0.07);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px;
  padding: 0.6rem 1rem;
  backdrop-filter: blur(8px);
}

.stat-value {
  font-size: 1.15rem;
  font-weight: 800;
  color: #fbbf24;
  line-height: 1;
}

.stat-label {
  font-size: 0.75rem;
  color: rgba(255,255,255,0.5);
  white-space: nowrap;
}

/* ── RIGHT PANEL ───────────────────────────────────────────────── */
.reg-right {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f9fafb;
  overflow-y: auto;
  padding: 2.5rem 1.5rem;
}

.reg-form-wrap {
  width: 100%;
  max-width: 680px;
}

/* Form header */
.form-header {
  margin-bottom: 2.2rem;
}

.form-title {
  font-size: 1.85rem;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.025em;
  margin: 0 0 0.4rem;
}

.form-sub {
  color: #6b7280;
  font-size: 0.97rem;
  margin: 0;
}

.form-sub strong {
  color: #111827;
}

/* Form fields inherit the app-wide styles through :deep in App.vue.
   We override the specifics needed for this light background. */
:deep(.form-label) {
  color: #374151;
  font-weight: 600;
  margin-bottom: 0.35rem;
  font-size: 0.83rem;
  letter-spacing: 0.01em;
  text-transform: none;
}

:deep(.form-control) {
  background: #fff;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  height: 48px;
  color: #111827;
  font-size: 0.97rem;
  transition: border-color 0.18s, box-shadow 0.18s;
}

:deep(.form-control:focus) {
  border-color: #f97316;
  box-shadow: 0 0 0 3px rgba(249,115,22,0.15);
  background: #fff;
}

:deep(.form-control::placeholder) {
  color: #9ca3af;
}

:deep(.btn.btn-primary) {
  border: none !important;
  border-radius: 10px !important;
  height: 50px;
  width: 100%;
  background: linear-gradient(90deg, #ea580c 0%, #f97316 50%, #fb923c 100%) !important;
  box-shadow: 0 4px 14px rgba(249,115,22,0.30) !important;
  font-weight: 700 !important;
  font-size: 1rem !important;
  color: #fff !important;
  letter-spacing: 0.01em !important;
  transition: all 0.2s !important;
}

:deep(.btn.btn-primary:hover) {
  background: linear-gradient(90deg, #c2410c 0%, #ea580c 50%, #f97316 100%) !important;
  box-shadow: 0 8px 22px rgba(234,88,12,0.38) !important;
  transform: translateY(-1px);
}

:deep(.btn.btn-primary:active) {
  transform: translateY(0) !important;
  box-shadow: 0 4px 12px rgba(249,115,22,0.28) !important;
}

:deep(.btn-lg) {
  min-height: 50px;
  padding: 0.7rem 1rem;
}

:deep(.form-check-input:checked) {
  background-color: #f97316;
  border-color: #f97316;
}

:deep(.form-check-label) {
  color: #6b7280;
  font-size: 0.9rem;
}

/* Sign-in switch */
.signin-switch {
  text-align: center;
  margin-top: 1.5rem;
  color: #9ca3af;
  font-size: 0.9rem;
}

.signin-link {
  color: #f97316;
  font-weight: 700;
  text-decoration: none;
  margin-left: 0.2rem;
}

.signin-link:hover {
  color: #ea580c;
  text-decoration: underline;
}

/* ── Responsive ────────────────────────────────────────────────── */
@media (max-width: 900px) {
  .reg-left {
    display: none;
  }

  .reg-right {
    background: linear-gradient(145deg, #0f172a 0%, #1e1b4b 40%, #312e81 75%, #1e1b4b 100%);
    padding: 2rem 1rem;
  }

  .reg-form-wrap {
    background: #fff;
    border-radius: 20px;
    padding: 2rem 1.8rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.25);
    max-width: 640px;
  }
}

@media (max-width: 480px) {
  .reg-form-wrap {
    padding: 1.6rem 1.2rem;
  }

  .form-title {
    font-size: 1.55rem;
  }
}
</style>
