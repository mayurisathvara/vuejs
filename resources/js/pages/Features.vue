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
          
          <h1 class="text-5xl md:text-7xl font-extrabold mb-6 animate-fade-in leading-tight">
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
    <section class="py-24 bg-gradient-to-br from-gray-50 to-white" id="mobile-app" role="region" aria-label="Mobile app">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Manage Calls On the Go with Our Mobile App
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Access real-time call data, track performance, and stay updated from anywhere using the Callytics mobile app.
          </p>
        </div>

        <!-- Mobile App Showcase: Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center mb-16">
          <!-- Left Column: Mobile Screenshots -->
          <div class="relative flex justify-center lg:justify-start">
            <div class="relative w-full max-w-md mx-auto">
              <!-- Dashboard Screenshot (Center) -->
              <div class="relative z-20 mobile-float" style="animation-delay: 0s;">
                <img 
                  src="../assets/images/mobile_dashboard.png" 
                  alt="Callytics Mobile Dashboard"
                  class="w-64 mx-auto rounded-3xl shadow-2xl border-4 border-white"
                />
              </div>
              
              <!-- Call Logs Screenshot (Left, Behind) -->
              <div class="absolute top-8 -left-4 sm:left-4 lg:-left-8 z-10 mobile-float" style="animation-delay: 0.5s;">
                <img 
                  src="../assets/images/mobile_call_logs.png" 
                  alt="Call Logs View"
                  class="w-56 rounded-3xl shadow-xl border-4 border-white opacity-90"
                />
              </div>
              
              <!-- Analytics Screenshot (Right, Behind) -->
              <div class="absolute top-16 -right-4 sm:right-4 lg:-right-8 z-10 mobile-float" style="animation-delay: 1s;">
                <img 
                  src="../assets/images/mobile_analytics.png" 
                  alt="Analytics Dashboard"
                  class="w-56 rounded-3xl shadow-xl border-4 border-white opacity-90"
                />
              </div>
            </div>
          </div>

          <!-- Right Column: Feature Content -->
          <div class="space-y-8">
            <div>
              <h3 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Powerful Call Analytics in Your Pocket
              </h3>
              <p class="text-lg text-gray-600">
                Stay connected to your business performance with our feature-rich mobile application, designed for professionals on the move.
              </p>
            </div>

            <!-- Feature Points -->
            <div class="space-y-5">
              <!-- Feature 1 -->
              <div class="flex items-start gap-4 group">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                </div>
                <div>
                  <h4 class="text-lg font-bold text-gray-900 mb-1">Real-time Call Tracking</h4>
                  <p class="text-gray-600">Monitor incoming calls and track performance metrics as they happen</p>
                </div>
              </div>

              <!-- Feature 2 -->
              <div class="flex items-start gap-4 group">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <div>
                  <h4 class="text-lg font-bold text-gray-900 mb-1">Detailed Call Logs & Insights</h4>
                  <p class="text-gray-600">Access comprehensive call history with detailed analytics and insights</p>
                </div>
              </div>

              <!-- Feature 3 -->
              <div class="flex items-start gap-4 group">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </div>
                <div>
                  <h4 class="text-lg font-bold text-gray-900 mb-1">Performance Analytics Dashboard</h4>
                  <p class="text-gray-600">View key metrics and trends with beautiful, intuitive visualizations</p>
                </div>
              </div>

              <!-- Feature 4 -->
              <div class="flex items-start gap-4 group">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                </div>
                <div>
                  <h4 class="text-lg font-bold text-gray-900 mb-1">Instant Notifications</h4>
                  <p class="text-gray-600">Get real-time alerts for important calls and events that matter</p>
                </div>
              </div>

              <!-- Feature 5 -->
              <div class="flex items-start gap-4 group">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <div>
                  <h4 class="text-lg font-bold text-gray-900 mb-1">Secure User Access</h4>
                  <p class="text-gray-600">Enterprise-grade security with protected authentication and data encryption</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Download Buttons -->
        <div class="text-center">
          <p class="text-lg text-gray-600 mb-6 font-medium">Download the app now</p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <!-- Google Play Button -->
            <a 
              href="#" 
              class="inline-flex items-center px-8 py-4 bg-black text-white rounded-xl hover:bg-gray-900 transition-all duration-300 hover:shadow-xl hover:scale-105"
              aria-label="Download Callytics on Google Play Store"
            >
              <svg class="w-8 h-8 mr-3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z" />
              </svg>
              <div class="text-left">
                <div class="text-xs">GET IT ON</div>
                <div class="text-lg font-semibold -mt-1">Google Play</div>
              </div>
            </a>

            <!-- App Store Button (Coming Soon) -->
            <a 
              href="#" 
              class="inline-flex items-center px-8 py-4 bg-black text-white rounded-xl opacity-60 cursor-not-allowed"
              aria-label="App Store - Coming Soon"
            >
              <svg class="w-8 h-8 mr-3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M18.71,19.5C17.88,20.74 17,21.95 15.66,21.97C14.32,22 13.89,21.18 12.37,21.18C10.84,21.18 10.37,21.95 9.1,22C7.79,22.05 6.8,20.68 5.96,19.47C4.25,17 2.94,12.45 4.7,9.39C5.57,7.87 7.13,6.91 8.82,6.88C10.1,6.86 11.32,7.75 12.11,7.75C12.89,7.75 14.37,6.68 15.92,6.84C16.57,6.87 18.39,7.1 19.56,8.82C19.47,8.88 17.39,10.1 17.41,12.63C17.44,15.65 20.06,16.66 20.09,16.67C20.06,16.74 19.67,18.11 18.71,19.5M13,3.5C13.73,2.67 14.94,2.04 15.94,2C16.07,3.17 15.6,4.35 14.9,5.19C14.21,6.04 13.07,6.7 11.95,6.61C11.8,5.46 12.36,4.26 13,3.5Z" />
              </svg>
              <div class="text-left">
                <div class="text-xs">Coming Soon</div>
                <div class="text-lg font-semibold -mt-1">App Store</div>
              </div>
            </a>
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

/* Mobile App Showcase: Floating Animation */
@keyframes mobile-float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
}

.mobile-float {
  animation: mobile-float 3s ease-in-out infinite;
}

/* Ensure enough height for stacked mobile screens */
.mobile-float:first-child {
  min-height: 500px;
}

/* Add intersection observer for scroll animations */
@media (prefers-reduced-motion: no-preference) {
  .fade-in-up {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Disable floating animation on mobile for performance */
@media (max-width: 768px) {
  .mobile-float {
    animation: none;
  }
}
</style>

