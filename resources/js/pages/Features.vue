<template>
  <div>
    <!-- Hero Section - Premium Modern Design -->
    <section class="relative text-gray-900 overflow-hidden hero-section" role="banner" aria-label="Hero section">
      <!-- Soft Gradient Background with improved depth -->
      <div class="absolute inset-0 hero-gradient" aria-hidden="true"></div>
      
      <!-- Subtle Pattern Overlay -->
      <div class="absolute inset-0 hero-pattern" aria-hidden="true"></div>
      
      <!-- Floating Geometric Shapes for Depth -->
      <div class="absolute top-20 left-10 w-72 h-72 bg-orange-400/10 rounded-full blur-3xl animate-float" aria-hidden="true"></div>
      <div class="absolute bottom-20 right-10 w-96 h-96 bg-orange-300/10 rounded-full blur-3xl animate-float-delayed" aria-hidden="true"></div>
      <div class="absolute top-1/2 left-1/3 w-64 h-64 bg-orange-500/5 rounded-full blur-2xl" aria-hidden="true"></div>
      
      <!-- Content Container with shadow -->
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">
        <div class="text-center">
          <!-- Badge/Label -->
          <div class="inline-flex items-center px-4 py-2 rounded-full bg-white/90 backdrop-blur-sm shadow-lg mb-8 animate-fade-in">
            <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 animate-pulse" aria-hidden="true"></span>
            <span class="text-sm font-semibold text-orange-600">Trusted by 2,000+ businesses worldwide</span>
          </div>
          
          <h1 class="hero-heading mb-6 animate-fade-in">
            <span class="text-gray-900">Track, Analyze & Optimize<br/>Every Business Call</span>
          </h1>
          
          <p class="text-xl md:text-2xl text-gray-600 mb-10 max-w-3xl mx-auto leading-relaxed">
            Transform your phone conversations into actionable insights. Monitor calls in real-time, record conversations, and boost your marketing ROI with powerful analytics.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
            <router-link
              to="/contact"
              class="cta-button-primary group"
              aria-label="Start your free trial"
            >
              Start Your Free Trial
              <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
              </svg>
            </router-link>
            <router-link
              to="/pricing"
              class="cta-button-secondary"
              aria-label="View pricing plans"
            >
              View Pricing
            </router-link>
          </div>
          
          <!-- Trust Indicators - Enhanced -->
          <div class="flex flex-wrap justify-center items-center gap-6 text-sm text-gray-600 font-medium">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span>No credit card required</span>
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span>14-day free trial</span>
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span>GDPR Compliant</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Smooth Wave Transition with Shadow -->
      <div class="absolute bottom-0 left-0 right-0 wave-container" aria-hidden="true">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="w-full h-24">
          <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="white"/>
        </svg>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-24 bg-white" id="features" role="region" aria-label="Features">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Powerful Features for Your Business
          </h2>
          <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            Everything you need to track, analyze, and optimize your phone calls
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <article v-for="(feature, index) in features" :key="index" class="feature-card" :class="'fade-in-up delay-' + (index * 100)">
            <div class="feature-icon" v-html="feature.icon" aria-hidden="true"></div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ feature.title }}</h3>
            <p class="text-gray-600">{{ feature.description }}</p>
          </article>
        </div>
      </div>
    </section>

    <!-- Powerful Admin Features Section -->
    <section class="py-24 bg-gray-50" id="admin-features" role="region" aria-label="Admin Features">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Powerful Admin Features Built for Performance
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Everything you need to monitor, manage, and optimize business calls from one centralized platform.
          </p>
        </div>

        <!-- Feature 1: Dashboard Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-24">
          <div class="order-2 lg:order-1">
            <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
              Real-Time Call Analytics Dashboard
            </h3>
            <p class="text-lg text-gray-600 mb-6 leading-relaxed">
              Monitor call performance instantly with live metrics, inbound and outbound tracking, missed call analysis, answer rate insights, and peak activity tracking.
            </p>
            <ul class="space-y-3">
              <li class="flex items-start">
                <svg class="w-6 h-6 text-orange-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-700">Total Calls Overview</span>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 text-orange-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-700">Answer Rate & Average Duration</span>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 text-orange-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-700">Inbound & Outbound Breakdown</span>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 text-orange-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-700">Missed Call Monitoring</span>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 text-orange-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-700">Daily Call Volume Charts</span>
              </li>
            </ul>
          </div>
          <div class="order-1 lg:order-2">
            <div class="rounded-2xl overflow-hidden shadow-2xl border border-gray-200 hover:shadow-3xl transition-shadow duration-300">
              <img 
                :src="dashboardImg" 
                alt="Real-Time Call Analytics Dashboard showing total calls, answer rates, inbound and outbound tracking, and daily call volume charts"
                class="w-full h-auto"
                loading="lazy"
              />
            </div>
          </div>
        </div>

        <!-- Feature 2: Advanced Call Reports -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-24">
          <div>
            <div class="rounded-2xl overflow-hidden shadow-2xl border border-gray-200 hover:shadow-3xl transition-shadow duration-300">
              <img 
                :src="callReportImg" 
                alt="Detailed Call Reports with powerful filters including date, department, call status, agent mapping, and Excel export"
                class="w-full h-auto"
                loading="lazy"
              />
            </div>
          </div>
          <div>
            <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
              Detailed Call Reports & Smart Filtering
            </h3>
            <p class="text-lg text-gray-600 mb-6 leading-relaxed">
              Access complete call logs with powerful filters. Track agent activity, call status, SIM usage, and export reports for deeper analysis.
            </p>
            <ul class="space-y-3">
              <li class="flex items-start">
                <svg class="w-6 h-6 text-orange-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-700">Date & Department Filters</span>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 text-orange-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-700">Call Status Tracking</span>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 text-orange-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-700">Agent & SIM Mapping</span>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 text-orange-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-700">Customer Number Tracking</span>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 text-orange-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-700">Export to Excel</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Feature 3: SIM-wise Summary Report -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div class="order-2 lg:order-1">
            <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
              SIM & Agent Performance Insights
            </h3>
            <p class="text-lg text-gray-600 mb-6 leading-relaxed">
              Analyze performance at the SIM and agent level with detailed duration metrics, outbound tracking, and missed call summaries.
            </p>
            <ul class="space-y-3">
              <li class="flex items-start">
                <svg class="w-6 h-6 text-orange-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-700">Agent-wise Summary</span>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 text-orange-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-700">Answered Duration Metrics</span>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 text-orange-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-700">Missed Call Analysis</span>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 text-orange-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-700">Outbound Performance Data</span>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 text-orange-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-700">Structured Tabular View</span>
              </li>
            </ul>
          </div>
          <div class="order-1 lg:order-2">
            <div class="rounded-2xl overflow-hidden shadow-2xl border border-gray-200 hover:shadow-3xl transition-shadow duration-300">
              <img 
                :src="summaryReportImg" 
                alt="SIM-wise Summary Report showing agent performance, answered duration metrics, missed calls, and outbound data"
                class="w-full h-auto"
                loading="lazy"
              />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Mobile App Section -->
    <section class="py-24 relative overflow-hidden" id="mobile-app" role="region" aria-label="Mobile app" style="background: linear-gradient(180deg, #ffffff 0%, #fff8f3 30%, #fff1e6 60%, #fff8f3 90%, #ffffff 100%);">
      <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute top-20 left-10 w-72 h-72 rounded-full opacity-20 blur-3xl" style="background: #ff6b00;"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 rounded-full opacity-10 blur-3xl" style="background: #ff8c33;"></div>
      </div>
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Manage Calls On the Go with Our Mobile App
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Access real-time call data, track performance, and stay updated from anywhere using the Callytics mobile app.
          </p>
        </div>

        <!-- Mobile App Showcase -->
        <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-8 mb-10">
          
          <!-- Left Features -->
          <div class="flex flex-col gap-8 flex-1 order-2 lg:order-1">
            <div class="flex items-start gap-4 text-right lg:flex-row flex-row-reverse lg:flex-row-reverse">
              <div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Real-time Notifications</h3>
                <p class="text-sm text-gray-500">Get instant alerts for incoming calls and important events</p>
              </div>
              <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 app-feature-icon">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
              </div>
            </div>
            <div class="flex items-start gap-4 text-right lg:flex-row flex-row-reverse lg:flex-row-reverse">
              <div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Live Dashboard</h3>
                <p class="text-sm text-gray-500">Monitor key metrics and analytics from anywhere</p>
              </div>
              <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 app-feature-icon">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
              </div>
            </div>
          </div>

          <!-- Center Phone Screenshots -->
          <div class="flex items-end justify-center gap-2 sm:gap-3 md:gap-4 flex-shrink-0 order-1 lg:order-2">
            <!-- Call Logs -->
            <div class="flex flex-col items-center group app-screen-card">
              <div class="relative w-36 sm:w-40 md:w-44">
                <img 
                  :src="'/mobile_app/call_logs.png'" 
                  alt="Callytics mobile app call logs screen"
                  class="w-full h-auto app-phone-img transition-all duration-500 group-hover:-translate-y-2 group-hover:app-phone-img-hover"
                  loading="lazy"
                />
              </div>
              <span class="mt-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Call Logs</span>
            </div>

            <!-- Dashboard (Center - Larger) -->
            <div class="flex flex-col items-center group app-screen-card -mt-4">
              <div class="relative w-44 sm:w-48 md:w-56 app-center-phone">
                <div class="absolute -inset-4 rounded-[2.5rem] opacity-20 group-hover:opacity-40 transition-opacity duration-500 blur-2xl" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);"></div>
                <img 
                  :src="'/mobile_app/dashboard.png'" 
                  alt="Callytics mobile app dashboard"
                  class="w-full h-auto relative z-10 app-phone-img transition-all duration-500 group-hover:-translate-y-3 group-hover:app-phone-img-hover"
                  loading="lazy"
                />
              </div>
              <span class="mt-3 text-xs font-semibold text-orange-600 uppercase tracking-wider">Dashboard</span>
            </div>

            <!-- Analytics -->
            <div class="flex flex-col items-center group app-screen-card">
              <div class="relative w-36 sm:w-40 md:w-44">
                <img 
                  :src="'/mobile_app/analytics.png'" 
                  alt="Callytics mobile app analytics screen"
                  class="w-full h-auto app-phone-img transition-all duration-500 group-hover:-translate-y-2 group-hover:app-phone-img-hover"
                  loading="lazy"
                />
              </div>
              <span class="mt-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Analytics</span>
            </div>
          </div>

          <!-- Right Features -->
          <div class="flex flex-col gap-8 flex-1 order-3">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 app-feature-icon">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
              </div>
              <div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Instant Reports</h3>
                <p class="text-sm text-gray-500">Access detailed call reports and summaries instantly</p>
              </div>
            </div>
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 app-feature-icon">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
              </div>
              <div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Secure Access</h3>
                <p class="text-sm text-gray-500">Protected login with secure authentication</p>
              </div>
            </div>
          </div>

        </div>

        <!-- Download Button -->
        <div class="text-center">
          <p class="text-lg text-gray-600 mb-6 font-medium">Download the app now</p>
          <a 
            href="#" 
            class="inline-flex items-center px-8 py-4 bg-black text-white rounded-xl hover:bg-gray-900 transition-all duration-300 hover:shadow-xl hover:scale-105"
            aria-label="Download Callytics on Google Play Store"
          >
            <svg class="w-9 h-9 mr-4" viewBox="0 0 512 512" fill="none" aria-hidden="true">
              <path d="M325.3 234.3L104.6 13l280.8 161.2-60.1 60.1zM47 0C34 6.8 25.3 19.2 25.3 35.3v441.3c0 16.1 8.7 28.5 21.7 35.3l256.6-256L47 0zm425.2 225.6l-58.9-34.1-65.7 64.5 65.7 64.5 60.1-34.1c18-14.3 18-46.5-1.2-60.8zM104.6 499l280.8-161.2-60.1-60.1L104.6 499z" fill="currentColor"/>
            </svg>
            <div class="text-left">
              <div class="text-xs opacity-80">GET IT ON</div>
              <div class="text-xl font-semibold -mt-1">Google Play</div>
            </div>
          </a>
        </div>
      </div>
    </section>

    <!-- How Callytics Works Section -->
    <section class="hiw-section" id="how-it-works" role="region" aria-label="How Callytics Works">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16">
          <span class="hiw-badge">Simple Process</span>
          <h2 class="hiw-title">How Callytics Works</h2>
          <p class="hiw-subtitle">
            Get started in minutes and transform your call tracking with four simple steps
          </p>
        </div>

        <!-- Steps -->
        <div class="hiw-steps">
          <!-- Connector line (Desktop) -->
          <div class="hiw-connector-line" aria-hidden="true"></div>

          <!-- Step 1 -->
          <div class="hiw-card-enter hiw-delay-1">
            <div class="group hiw-card">
              <div class="hiw-card-top-border" aria-hidden="true"></div>
              <div class="hiw-num"><span>1</span></div>
              <div class="hiw-icon">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
              </div>
              <h3 class="hiw-card-title">Register</h3>
              <p class="hiw-card-desc">Create your Callytics account in seconds. No credit card required to get started.</p>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="hiw-card-enter hiw-delay-2">
            <div class="group hiw-card">
              <div class="hiw-card-top-border" aria-hidden="true"></div>
              <div class="hiw-num"><span>2</span></div>
              <div class="hiw-icon">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11l0-4m0 0l-2 2m2-2l2 2" />
                </svg>
              </div>
              <h3 class="hiw-card-title">Install App</h3>
              <p class="hiw-card-desc">Download and install the Callytics app on your team's devices in just a few taps.</p>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="hiw-card-enter hiw-delay-3">
            <div class="group hiw-card">
              <div class="hiw-card-top-border" aria-hidden="true"></div>
              <div class="hiw-num"><span>3</span></div>
              <div class="hiw-icon">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 3h6m0 0v6m0-6l-7 7" />
                </svg>
              </div>
              <h3 class="hiw-card-title">Automatically Track Calls</h3>
              <p class="hiw-card-desc">Every inbound and outbound call is captured automatically — no manual logging needed.</p>
            </div>
          </div>

          <!-- Step 4 -->
          <div class="hiw-card-enter hiw-delay-4">
            <div class="group hiw-card">
              <div class="hiw-card-top-border" aria-hidden="true"></div>
              <div class="hiw-num"><span>4</span></div>
              <div class="hiw-icon">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <h3 class="hiw-card-title">Call Insights Dashboard</h3>
              <p class="hiw-card-desc">View real-time analytics, performance reports, and actionable insights from your dashboard.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Final CTA Section -->
    <section class="py-20 text-white" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-6">
          Ready to Transform Your Call Tracking?
        </h2>
        <p class="text-xl text-orange-50 mb-8">
          Join thousands of businesses already using Callytics to optimize their marketing
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link
            to="/contact"
            class="px-8 py-4 bg-white font-bold rounded-lg shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105"
            style="color: #ff6b00;"
          >
            Start Your Free Trial
          </router-link>
          <router-link
            to="/pricing"
            class="px-8 py-4 bg-white/10 backdrop-blur-sm border-2 border-white/30 text-white font-bold rounded-lg hover:bg-white/20 transition-all duration-300"
          >
            Compare Plans
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import dashboardImg from '@/assets/images/dashboard.png';
import callReportImg from '@/assets/images/call_report.png';
import summaryReportImg from '@/assets/images/summary_report.png';

const features = [
  {
    title: 'Real-Time Tracking',
    description: 'Monitor all incoming calls in real-time with detailed analytics and insights.',
    icon: '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
  },
  {
    title: 'Call Recording',
    description: 'Record and review calls for quality assurance and training purposes.',
    icon: '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>'
  },
  {
    title: 'Advanced Analytics',
    description: 'Get detailed reports on call volume, duration, sources, and conversion rates.',
    icon: '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>'
  },
  {
    title: 'Smart Integration',
    description: 'Seamlessly integrate with your marketing tools and analytics platforms for unified data.',
    icon: '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>'
  }
];
</script>

<style scoped>
/* Animation keyframes */
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) scale(1);
  }
  50% {
    transform: translateY(-20px) scale(1.05);
  }
}

@keyframes float-delayed {
  0%, 100% {
    transform: translateY(0px) scale(1);
  }
  50% {
    transform: translateY(20px) scale(1.05);
  }
}

.animate-float {
  animation: float 8s ease-in-out infinite;
}

.animate-float-delayed {
  animation: float-delayed 10s ease-in-out infinite;
  animation-delay: 1s;
}

.animate-fade-in {
  animation: fade-in 1s ease-out;
}

/* Scroll-triggered fade-in animations */
.fade-in-up {
  opacity: 0;
  transform: translateY(30px);
  transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.fade-in-up.delay-0 { transition-delay: 0s; }
.fade-in-up.delay-100 { transition-delay: 0.1s; }
.fade-in-up.delay-150 { transition-delay: 0.15s; }
.fade-in-up.delay-200 { transition-delay: 0.2s; }
.fade-in-up.delay-300 { transition-delay: 0.3s; }

/* Premium Hero Section Styles */
.hero-section {
  min-height: 75vh;
  display: flex;
  align-items: center;
  background: linear-gradient(180deg, #fff8f3 0%, #ffffff 50%, #fafafa 100%);
}

/* Mobile App Screenshot Cards */
.app-screen-card {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Phone image premium shadow */
.app-phone-img {
  filter: drop-shadow(0 8px 20px rgba(0, 0, 0, 0.12)) drop-shadow(0 4px 8px rgba(0, 0, 0, 0.08));
}

.group:hover .app-phone-img {
  filter: drop-shadow(0 12px 28px rgba(0, 0, 0, 0.16)) drop-shadow(0 6px 12px rgba(0, 0, 0, 0.1));
}

/* Center phone prominence */
.app-center-phone {
  transform: scale(1.05);
  transition: transform 0.4s ease;
}

.group:hover .app-center-phone {
  transform: scale(1.07);
}

/* Feature icon containers */
.app-feature-icon {
  background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);
  border-radius: 0.75rem;
  box-shadow: 0 2px 8px rgba(255, 107, 0, 0.2), 0 1px 3px rgba(0, 0, 0, 0.06);
  transition: box-shadow 0.3s ease;
}

.app-feature-icon:hover {
  box-shadow: 0 4px 14px rgba(255, 107, 0, 0.3), 0 2px 4px rgba(0, 0, 0, 0.08);
}

.hero-gradient {
  background: radial-gradient(ellipse at top, rgba(255, 140, 51, 0.12) 0%, transparent 60%),
              radial-gradient(ellipse at bottom right, rgba(255, 107, 0, 0.08) 0%, transparent 50%);
}

.hero-pattern {
  background-image: 
    linear-gradient(rgba(255, 140, 51, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 140, 51, 0.03) 1px, transparent 1px);
  background-size: 50px 50px;
  opacity: 0.5;
}

.wave-container {
  filter: drop-shadow(0 -4px 8px rgba(0, 0, 0, 0.03));
}

/* CTA Buttons */
.cta-button-primary {
  @apply px-8 py-4 text-white font-bold rounded-xl shadow-xl transition-all duration-300 flex items-center gap-2;
  background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);
}

.cta-button-primary:hover {
  @apply shadow-2xl scale-105 -translate-y-1;
}

.cta-button-secondary {
  @apply px-8 py-4 bg-white border-2 border-gray-200 text-gray-700 font-bold rounded-xl transition-all duration-300;
}

.cta-button-secondary:hover {
  @apply border-orange-300 shadow-lg scale-105;
}

/* Feature Cards */
.feature-card {
  @apply p-8 bg-white rounded-xl shadow-md transition-all duration-300 border border-gray-100;
}

.feature-card:hover {
  @apply shadow-xl -translate-y-2;
  border-color: #ffcc99;
}

.feature-icon {
  @apply w-16 h-16 rounded-lg flex items-center justify-center text-white mb-4 transition-transform duration-300;
  background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);
}

.feature-card:hover .feature-icon {
  @apply scale-110;
}

/* Add intersection observer for scroll animations */
@media (prefers-reduced-motion: no-preference) {
  .fade-in-up {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ===== How Callytics Works ===== */
.hiw-section {
  padding: 6rem 0;
  background: linear-gradient(180deg, #ffffff 0%, #f9fafb 50%, #ffffff 100%);
}

/* Badge */
.hiw-badge {
  display: inline-block;
  padding: 0.35rem 1.1rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #ea580c;
  margin-bottom: 1rem;
  background: #fff7ed;
  border: 1px solid #fed7aa;
}

/* Title */
.hiw-title {
  font-size: clamp(1.75rem, 4vw, 2.5rem);
  font-weight: 800;
  color: #111827;
  margin-bottom: 0.75rem;
  letter-spacing: -0.025em;
  line-height: 1.2;
}

/* Subtitle */
.hiw-subtitle {
  font-size: 1.05rem;
  color: #6b7280;
  max-width: 34rem;
  margin: 0 auto;
  line-height: 1.7;
}

/* Steps container — 4-col grid with connector line behind */
.hiw-steps {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
  position: relative;
}

@media (min-width: 768px) {
  .hiw-steps {
    grid-template-columns: repeat(2, 1fr);
    gap: 1.75rem;
  }
}

@media (min-width: 1024px) {
  .hiw-steps {
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
  }
}

/* Horizontal connector line — sits behind cards at step-number height */
.hiw-connector-line {
  display: none;
}

@media (min-width: 1024px) {
  .hiw-connector-line {
    display: block;
    position: absolute;
    top: 3.25rem; /* vertically aligns with center of step number */
    left: calc(12.5% + 1.5rem);
    right: calc(12.5% + 1.5rem);
    height: 2px;
    background: repeating-linear-gradient(
      90deg,
      #e5e7eb 0px,
      #e5e7eb 6px,
      transparent 6px,
      transparent 12px
    );
    z-index: 0;
  }
}

/* Card */
.hiw-card {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 2.5rem 1.5rem 2rem;
  border-radius: 1rem;
  background: #ffffff;
  border: 1px solid #f3f4f6;
  box-shadow: 0 1px 2px rgba(0,0,0,0.03), 0 4px 16px rgba(0,0,0,0.04);
  transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  overflow: hidden;
}

.hiw-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.05), 0 16px 40px rgba(0,0,0,0.07);
  border-color: #e5e7eb;
}

/* Orange top stripe — hidden, slides in on hover */
.hiw-card-top-border {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #ff6b00, #ff8c33);
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.35s ease;
}

.hiw-card:hover .hiw-card-top-border {
  transform: scaleX(1);
}

/* Step number — sits on the connector line */
.hiw-num {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
  background: linear-gradient(135deg, #ff6b00, #ff8c33);
  box-shadow: 0 0 0 4px #fff, 0 0 0 6px rgba(255, 107, 0, 0.12), 0 2px 8px rgba(255, 107, 0, 0.18);
  transition: box-shadow 0.3s ease;
}

.hiw-num span {
  font-size: 1.05rem;
  font-weight: 800;
  color: #fff;
  line-height: 1;
}

.hiw-card:hover .hiw-num {
  box-shadow: 0 0 0 4px #fff, 0 0 0 8px rgba(255, 107, 0, 0.15), 0 4px 14px rgba(255, 107, 0, 0.25);
}

/* Icon — light peach container, orange icon */
.hiw-icon {
  width: 3.5rem;
  height: 3.5rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.25rem;
  background: #FFF4EC;
  color: #ea580c;
  transition: all 0.3s ease;
}

.hiw-card:hover .hiw-icon {
  background: #ffedd5;
  color: #c2410c;
  transform: scale(1.05);
}

/* Card text */
.hiw-card-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.4rem;
}

.hiw-card-desc {
  font-size: 0.85rem;
  line-height: 1.65;
  color: #6b7280;
}

/* Staggered entrance */
@keyframes hiw-slide-up {
  from {
    opacity: 0;
    transform: translateY(24px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.hiw-card-enter {
  opacity: 0;
  animation: hiw-slide-up 0.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

.hiw-delay-1 { animation-delay: 0.1s; }
.hiw-delay-2 { animation-delay: 0.2s; }
.hiw-delay-3 { animation-delay: 0.3s; }
.hiw-delay-4 { animation-delay: 0.4s; }

@media (prefers-reduced-motion: reduce) {
  .hiw-card-enter {
    opacity: 1;
    animation: none;
  }
}
</style>

