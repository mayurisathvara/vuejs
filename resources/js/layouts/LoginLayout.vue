<template>
  <div class="login-page">

    <!-- ── LEFT PANEL ─────────────────────────────────────────── -->
    <div class="login-left">

      <!-- Animated background blobs -->
      <div class="blob blob-1"></div>
      <div class="blob blob-2"></div>
      <div class="blob blob-3"></div>

      <!-- Dot grid -->
      <div class="grid-overlay"></div>

      <!-- Decorative rings -->
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
          <h1>Welcome back.<br><span class="headline-accent">Your calls await.</span></h1>
          <p class="left-sub">Everything you need to manage calls, teams, and SIMs — right where you left off.</p>
        </div>

        <!-- Highlights -->
        <ul class="highlight-list">
          <li v-for="item in highlights" :key="item.text" class="highlight-item">
            <div class="hi-icon">
              <i :class="item.icon"></i>
            </div>
            <div class="hi-body">
              <span class="hi-title">{{ item.title }}</span>
              <span class="hi-desc">{{ item.desc }}</span>
            </div>
          </li>
        </ul>

        <!-- Testimonial -->
        <div class="testimonial-card">
          <p class="testimonial-text">
            "Callytics transformed how we track and manage our field team's calls. Instant visibility, zero hassle."
          </p>
          <div class="testimonial-author">
            <div class="author-avatar">R</div>
            <div>
              <span class="author-name">Rahul Mehta</span>
              <span class="author-role">Operations Head, TechGroup</span>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- ── RIGHT PANEL ────────────────────────────────────────── -->
    <div class="login-right">
      <div class="login-form-wrap">

        <!-- Header -->
        <div class="form-header">
          <h2 class="form-title">Sign in to your account</h2>
          <p class="form-sub">Don't have an account?
            <router-link to="/register" class="form-link">Start free trial</router-link>
          </p>
        </div>

        <!-- The login form (router-view) -->
        <router-view />

      </div>
    </div>

  </div>
</template>

<script setup>
const highlights = [
  {
    icon: 'fas fa-tachometer-alt',
    title: 'Live Dashboard',
    desc: 'View real-time call activity across all your teams and SIMs.',
  },
  {
    icon: 'fas fa-mobile-alt',
    title: 'SIM Management',
    desc: 'Activate, swap, and monitor all your SIMs from one place.',
  },
  {
    icon: 'fas fa-chart-bar',
    title: 'Detailed Reports',
    desc: 'Export call summaries and analytics in just a few clicks.',
  },
]
</script>

<style scoped>
/* ── Base ──────────────────────────────────────────────────────── */
.login-page {
  display: flex;
  min-height: 100vh;
}

/* ── LEFT PANEL ────────────────────────────────────────────────── */
.login-left {
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
  opacity: 0.5;
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
  50%  { transform: translateY(-28px) scale(1.07); }
  100% { transform: translateY(14px) scale(0.95); }
}

/* Dot grid */
.grid-overlay {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);
  background-size: 28px 28px;
  pointer-events: none;
}

/* Rings */
.ring {
  position: absolute;
  border-radius: 50%;
  border: 1px solid rgba(255,255,255,0.06);
  animation: ringPulse 9s ease-in-out infinite;
}

.ring-1 {
  width: 300px; height: 300px;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
}
.ring-2 {
  width: 480px; height: 480px;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  animation-delay: -2s;
}
.ring-3 {
  width: 680px; height: 680px;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  animation-delay: -4s;
  border-color: rgba(255,255,255,0.03);
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
  background: linear-gradient(90deg, #38bdf8, #818cf8, #34d399);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.left-sub {
  color: rgba(255,255,255,0.58);
  font-size: 1.02rem;
  line-height: 1.65;
  margin: 0;
}

/* Highlights */
.highlight-list {
  list-style: none;
  padding: 0;
  margin: 0 0 2.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
}

.highlight-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  animation: fadeSlideUp 0.5s ease both;
}

.highlight-item:nth-child(1) { animation-delay: 0.1s; }
.highlight-item:nth-child(2) { animation-delay: 0.2s; }
.highlight-item:nth-child(3) { animation-delay: 0.3s; }

@keyframes fadeSlideUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

.hi-icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: rgba(255,255,255,0.09);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.12);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: #38bdf8;
  font-size: 0.95rem;
}

.hi-body {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.hi-title {
  font-weight: 700;
  font-size: 0.95rem;
  color: #fff;
}

.hi-desc {
  font-size: 0.83rem;
  color: rgba(255,255,255,0.5);
  line-height: 1.45;
}

/* Testimonial */
.testimonial-card {
  background: rgba(255,255,255,0.07);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 14px;
  padding: 1.25rem 1.4rem;
  backdrop-filter: blur(10px);
  animation: fadeSlideUp 0.5s ease 0.4s both;
}

.testimonial-text {
  font-size: 0.92rem;
  color: rgba(255,255,255,0.75);
  line-height: 1.65;
  margin: 0 0 1rem;
  font-style: italic;
}

.testimonial-author {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.author-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0ea5e9, #6366f1);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.9rem;
  color: #fff;
  flex-shrink: 0;
}

.author-name {
  display: block;
  font-weight: 700;
  font-size: 0.88rem;
  color: #fff;
}

.author-role {
  display: block;
  font-size: 0.78rem;
  color: rgba(255,255,255,0.45);
}

/* ── RIGHT PANEL ───────────────────────────────────────────────── */
.login-right {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f9fafb;
  overflow-y: auto;
  padding: 2.5rem 1.5rem;
}

.login-form-wrap {
  width: 100%;
  max-width: 420px;
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

.form-link {
  color: #f97316;
  font-weight: 700;
  text-decoration: none;
  margin-left: 0.2rem;
}

.form-link:hover {
  color: #ea580c;
  text-decoration: underline;
}

/* Mirror the same form field overrides as RegisterLayout */
:deep(.form-label) {
  color: #374151;
  font-weight: 600;
  margin-bottom: 0.4rem;
  font-size: 0.92rem;
}

:deep(.form-control) {
  background: #fff;
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  height: 50px;
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
  background: linear-gradient(90deg, #ea580c 0%, #f97316 50%, #fb923c 100%) !important;
  box-shadow: 0 6px 18px rgba(249,115,22,0.32) !important;
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

/* Forgot password link override */
:deep(.forgot-link) {
  color: #f97316;
  font-size: 0.9rem;
}

:deep(.forgot-link:hover) {
  color: #ea580c;
}

/* ── Responsive ────────────────────────────────────────────────── */
@media (max-width: 900px) {
  .login-left {
    display: none;
  }

  .login-right {
    background: linear-gradient(145deg, #0f172a 0%, #1e1b4b 40%, #312e81 75%, #1e1b4b 100%);
    padding: 2rem 1rem;
  }

  .login-form-wrap {
    background: #fff;
    border-radius: 20px;
    padding: 2rem 1.8rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    max-width: 400px;
  }
}

@media (max-width: 480px) {
  .login-form-wrap {
    padding: 1.6rem 1.2rem;
  }

  .form-title {
    font-size: 1.55rem;
  }
}
</style>
