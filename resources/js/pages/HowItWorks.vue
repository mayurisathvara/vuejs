<template>
  <div>
    <!-- Hero Section -->
    <section class="relative overflow-hidden" role="banner" aria-label="How it works hero">
      <div class="absolute inset-0" style="background: linear-gradient(135deg, #F9FAFB 0%, #EEF2F7 50%, #F3F4F6 100%);" aria-hidden="true"></div>
      <div class="absolute top-0 right-0 w-[500px] h-[500px] opacity-60" style="background: radial-gradient(circle, rgba(255, 115, 50, 0.18) 0%, transparent 70%);" aria-hidden="true"></div>
      <div class="absolute bottom-0 left-0 w-[400px] h-[400px] opacity-40" style="background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 70%);" aria-hidden="true"></div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20 lg:py-24">
        <div class="text-center max-w-3xl mx-auto">
          <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-orange-100 mb-5">
            <svg class="w-4 h-4 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Step-by-Step Guide</span>
          </div>

          <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-5 leading-tight">
            How
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-orange-400"> Callytics </span>
            Works
          </h1>

          <p class="text-lg md:text-xl text-gray-600 leading-relaxed mb-8 max-w-2xl mx-auto">
            From sign-up to actionable insights — get your business call tracking running in minutes, not days.
          </p>

          <div class="flex flex-wrap justify-center gap-6 text-sm">
            <div class="flex items-center gap-2 text-gray-600">
              <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
              </svg>
              <span class="font-medium">Takes less than 5 minutes</span>
            </div>
            <div class="flex items-center gap-2 text-gray-600">
              <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
              </svg>
              <span class="font-medium">No technical skills needed</span>
            </div>
            <div class="flex items-center gap-2 text-gray-600">
              <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
              </svg>
              <span class="font-medium">Start tracking today</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Steps Section -->
    <section class="py-20 bg-white" role="region" aria-label="Setup steps">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative">
          <!-- Vertical timeline connector (desktop) -->
          <div
            class="absolute hidden md:block"
            style="left: 2.5rem; top: 5rem; bottom: 5rem; width: 2px; background: linear-gradient(to bottom, #ff6b00 0%, #ff8c33 40%, #ffcc99 80%, transparent 100%);"
            aria-hidden="true"
          ></div>

          <div class="space-y-8 md:space-y-10">
            <article
              v-for="(step, index) in steps"
              :key="index"
              class="relative flex gap-6 md:gap-10 step-row"
              :aria-label="'Step ' + step.num + ': ' + step.title"
            >
              <!-- Step Number Block -->
              <div class="flex-shrink-0 relative z-10">
                <div
                  class="w-20 h-20 rounded-2xl flex flex-col items-center justify-center shadow-lg step-num-block"
                  style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);"
                >
                  <span class="text-[10px] font-bold text-orange-100 uppercase tracking-widest leading-none mb-0.5">STEP</span>
                  <span class="text-2xl font-black text-white leading-none">{{ step.num }}</span>
                </div>
              </div>

              <!-- Content Card -->
              <div class="flex-1 step-card rounded-2xl border border-gray-100 bg-white p-6 md:p-8 shadow-sm transition-all duration-300">
                <div class="flex items-start justify-between gap-4 mb-4">
                  <h2 class="text-xl md:text-2xl font-bold text-gray-900 leading-snug">{{ step.title }}</h2>
                  <div
                    class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 step-icon-wrap"
                    style="background-color: #fff4ec;"
                  >
                    <span v-html="step.icon" class="text-orange-500"></span>
                  </div>
                </div>

                <p class="text-gray-600 leading-relaxed mb-6 text-base md:text-lg">{{ step.description }}</p>

                <!-- Success badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-green-50 border border-green-100">
                  <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                  </svg>
                  <span class="text-sm font-semibold text-green-700">{{ step.success }}</span>
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <!-- What Happens Next -->
    <section class="py-16 bg-gray-50 border-t border-gray-100" role="region" aria-label="Benefits after setup">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
          <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-orange-100 mb-4">
            <svg class="w-4 h-4 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <span class="text-sm font-semibold text-orange-600 uppercase tracking-wide">After Setup</span>
          </div>
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">Everything at Your Fingertips</h2>
          <p class="text-gray-600 text-lg max-w-2xl mx-auto">Once configured, Callytics runs automatically — giving you full visibility into every call your team makes and receives.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="(benefit, i) in benefits" :key="i" class="benefit-card rounded-xl p-6 bg-white border border-gray-100 shadow-sm transition-all duration-300">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-4" style="background-color: #fff4ec;">
              <span v-html="benefit.icon" class="text-orange-500"></span>
            </div>
            <h3 class="font-bold text-gray-900 mb-1">{{ benefit.title }}</h3>
            <p class="text-sm text-gray-500 leading-relaxed">{{ benefit.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 text-white" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);" role="region" aria-label="Call to action">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Ready to Get Started?</h2>
        <p class="text-lg text-orange-100 mb-8 max-w-2xl mx-auto">
          Join hundreds of businesses already tracking and optimising their calls with Callytics. Setup takes minutes.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link
            to="/contact"
            class="px-8 py-4 bg-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105"
            style="color: #ff6b00;"
          >
            Start Free Trial
          </router-link>
          <router-link
            to="/pricing"
            class="px-8 py-4 bg-white/10 border-2 border-white/30 text-white font-bold rounded-xl hover:bg-white/20 transition-all duration-300"
          >
            View Pricing
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
const steps = [
  {
    num: '01',
    title: 'Register & Setup',
    description: 'Create your Callytics account using your mobile number or email and verify with OTP. It takes under a minute and no credit card is required.',
    success: 'Account created successfully',
    icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>',
  },
  {
    num: '02',
    title: 'Install App & Grant Permissions',
    description: 'Download the Callytics mobile app from Google Play, sign in with your credentials, and allow the required permissions for call tracking to begin.',
    success: 'App connected and permissions configured',
    icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>',
  },
  {
    num: '03',
    title: 'Automatic Call Tracking & Sync',
    description: 'Callytics automatically tracks all incoming, outgoing, and missed calls and securely syncs the data to the cloud in real time — no manual input needed.',
    success: 'Live tracking and cloud sync active',
    icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>',
  },
  {
    num: '04',
    title: 'Analyse Performance',
    description: 'Access real-time dashboards, detailed call reports, agent performance summaries, and actionable business insights — from desktop or mobile, anytime.',
    success: 'Insights ready to use',
    icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>',
  },
];

const benefits = [
  {
    title: 'Live Call Dashboard',
    desc: 'Monitor every call as it happens with real-time stats and agent activity.',
    icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>',
  },
  {
    title: 'Missed Call Alerts',
    desc: 'Get instant notifications for every missed call so no lead slips through.',
    icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>',
  },
  {
    title: 'Agent Performance',
    desc: 'Compare agent call volumes, durations, and answer rates at a glance.',
    icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>',
  },
  {
    title: 'Export Reports',
    desc: 'Download detailed call logs and summaries to Excel in a single click.',
    icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
  },
];
</script>

<style scoped>
/* Step row hover interaction */
.step-row {
  transition: all 0.3s ease;
}

.step-card {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04), 0 4px 16px rgba(0, 0, 0, 0.04);
}

.step-card:hover {
  border-color: #fed7aa;
  box-shadow: 0 8px 24px rgba(255, 107, 0, 0.1), 0 4px 12px rgba(0, 0, 0, 0.06);
  transform: translateX(4px);
}

/* Step number block pulse ring on hover */
.step-row:hover .step-num-block {
  box-shadow: 0 0 0 6px rgba(255, 107, 0, 0.12), 0 8px 20px rgba(255, 107, 0, 0.25);
}

/* Icon wrapper */
.step-icon-wrap {
  transition: background-color 0.3s ease;
}

.step-card:hover .step-icon-wrap {
  background-color: #ffedd5;
}

/* Benefit cards */
.benefit-card {
  transition: all 0.3s ease;
}

.benefit-card:hover {
  border-color: #fed7aa;
  box-shadow: 0 6px 20px rgba(255, 107, 0, 0.09), 0 3px 8px rgba(0, 0, 0, 0.05);
  transform: translateY(-3px);
}
</style>
