<template>
  <div>
    <!-- Hero Section - Enhanced Two Column Layout -->
    <section class="relative text-gray-900 overflow-hidden hero-section" role="banner" aria-label="Hero section">
      <!-- Premium SaaS Gradient Background - Base Layer -->
      <div class="absolute inset-0" style="background: linear-gradient(135deg, #F9FAFB 0%, #EEF2F7 50%, #F3F4F6 100%);" aria-hidden="true"></div>
      
      <!-- Soft Orange Glow (right-center, behind mobile images) -->
      <div class="absolute top-1/2 right-0 w-[600px] h-[600px] -translate-y-1/2 opacity-100" 
           style="background: radial-gradient(circle, rgba(255, 115, 50, 0.25) 0%, transparent 70%);" 
           aria-hidden="true"></div>
      
      <!-- Subtle Purple Glow (bottom-right) -->
      <div class="absolute bottom-0 right-0 w-[800px] h-[800px] translate-y-1/4 translate-x-1/4 opacity-100" 
           style="background: radial-gradient(circle, rgba(168, 85, 247, 0.15) 0%, transparent 65%);" 
           aria-hidden="true"></div>
      
      <!-- Very Light Blue Tint (top-left) -->
      <div class="absolute top-0 left-0 w-[500px] h-[500px] -translate-y-1/4 -translate-x-1/4 opacity-100" 
           style="background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 70%);" 
           aria-hidden="true"></div>
      
      <!-- Content Container -->
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
          
          <!-- Left Column - Content -->
          <div class="order-2 lg:order-1">
            <!-- Badge/Label -->
            <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-orange-100 mb-6 animate-fade-in">
              <svg class="w-4 h-4 text-orange-600 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z" />
              </svg>
              <span class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Powerful Features</span>
            </div>
            
            <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6 leading-tight">
              Everything You Need<br/>
              to Manage &<br/>
              <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-orange-500">Optimize Your Calls</span>
            </h1>
            
            <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed max-w-xl">
              From call tracking to AI-powered insights, Callytics gives you complete control over your business communication.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 mb-8">
              <router-link
                to="/contact"
                class="inline-flex items-center justify-center px-8 py-4 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105"
                style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);"
                aria-label="Start your free trial"
              >
                Start Free Trial
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
              </router-link>
              <button
                @click="showDemoModal = true"
                class="inline-flex items-center justify-center px-8 py-4 bg-white text-gray-900 font-bold rounded-xl border-2 border-gray-300 hover:border-orange-500 hover:text-orange-600 transition-all duration-300"
                aria-label="View demo"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                View Demo
              </button>
            </div>
            
            <!-- Trust Indicators -->
            <div class="flex flex-wrap items-center gap-6 text-sm">
              <div class="flex items-center gap-2 text-gray-700">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="font-medium">No credit card required</span>
              </div>
              <div class="flex items-center gap-2 text-gray-700">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="font-medium">14-day free trial</span>
              </div>
              <div class="flex items-center gap-2 text-gray-700">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span class="font-medium">GDPR & TRAI Compliant</span>
              </div>
            </div>
          </div>
          
          <!-- Right Column - Mobile Mockups -->
          <div class="order-1 lg:order-2 relative">
            <div class="relative flex justify-center lg:justify-end items-center">
              <!-- Main Hero Image with Phones -->
              <div class="relative w-full max-w-2xl">
                <img 
                  :src="'/mobile_app/hero_image.png'" 
                  alt="Callytics mobile app showing call logs and dashboard analytics"
                  class="w-full h-auto drop-shadow-2xl"
                  loading="eager"
                />
                
                <!-- Floating Stats Card - Growth -->
                <div class="absolute top-8 -right-4 lg:right-0 bg-white rounded-xl shadow-xl px-4 py-2 animate-float hidden sm:block min-w-[160px]">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                      <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                      </svg>
                    </div>
                    <div class="flex-1">
                      <div class="text-lg font-bold text-gray-900 leading-none mb-0.5">+18%</div>
                      <div class="text-[10px] font-medium text-gray-600 whitespace-nowrap leading-tight">Call Growth</div>
                    </div>
                  </div>
                </div>
                
                <!-- Floating Stats Card - Duration -->
                <div class="absolute bottom-20 left-2 lg:left-14 bg-white rounded-xl shadow-xl px-4 py-2 animate-float-delayed hidden sm:block min-w-[160px]">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                      <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div class="flex-1">
                      <div class="text-lg font-bold text-gray-900 leading-none mb-0.5">4m 32s</div>
                      <div class="text-[10px] font-medium text-gray-600 whitespace-nowrap leading-tight">Avg Call Duration</div>
                    </div>
                  </div>
                </div>
                
                <!-- Floating Stats Card - Total Calls -->
                <div class="absolute top-1/3 left-2 lg:left-14 bg-white rounded-xl shadow-xl px-4 py-2 animate-float hidden sm:block min-w-[160px]">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                      <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                      </svg>
                    </div>
                    <div class="flex-1">
                      <div class="text-lg font-bold text-gray-900 leading-none mb-0.5">1,247</div>
                      <div class="text-[10px] font-medium text-gray-600 whitespace-nowrap leading-tight">Total Calls Today</div>
                    </div>
                  </div>
                </div>
                
                <!-- Floating Stats Card - Answer Rate -->
                <div class="absolute bottom-8 -right-8 lg:-right-4 bg-white rounded-xl shadow-xl px-4 py-2 animate-float-delayed hidden sm:block min-w-[160px]">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                      <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div class="flex-1">
                      <div class="text-lg font-bold text-gray-900 leading-none mb-0.5">79%</div>
                      <div class="text-[10px] font-medium text-gray-600 whitespace-nowrap leading-tight">Answer Rate</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
        </div>
      </div>
    </section>

    <!-- Features Section - Premium SaaS Style -->
    <section class="py-20 lg:py-28 bg-white relative overflow-hidden" id="features" role="region" aria-label="Features">
      <!-- Subtle background gradient -->
      <div class="absolute inset-0 bg-gradient-to-b from-gray-50 via-transparent to-gray-50 pointer-events-none" aria-hidden="true"></div>
      
      <!-- Soft gradient accents -->
      <div class="absolute top-20 right-0 w-96 h-96 rounded-full opacity-5 blur-3xl" style="background: linear-gradient(135deg, #ff6b00, #ff8c33);" aria-hidden="true"></div>
      <div class="absolute bottom-20 left-0 w-80 h-80 rounded-full opacity-4 blur-3xl" style="background: linear-gradient(135deg, #ff6b00, #ff8c33);" aria-hidden="true"></div>
      
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Section Header -->
        <div class="text-center mb-16 lg:mb-20">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6 leading-tight max-w-3xl mx-auto">
            Powerful Call Tracking for Modern Businesses
          </h2>
          <p class="text-lg md:text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">
            Real-time insights, complete control, and seamless integration—everything you need to maximize call value.
          </p>
        </div>

        <!-- Premium Feature Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
          <article 
            v-for="(feature, index) in features" 
            :key="index" 
            class="premium-feature-card fade-in-up" 
            :class="'delay-' + (index * 100)"
            role="article"
          >
            <!-- Icon -->
            <div class="premium-feature-icon" v-html="feature.icon" aria-hidden="true"></div>
            
            <!-- Content -->
            <h3 class="text-xl font-bold text-gray-900 mb-3 leading-tight">{{ feature.title }}</h3>
            <p class="text-gray-600 text-sm leading-relaxed mb-5">{{ feature.description }}</p>
            
            <!-- Micro-bullet points -->
            <ul class="space-y-2 pt-5 border-t border-gray-100">
              <li v-for="(bullet, i) in feature.bullets" :key="i" class="flex items-start gap-2.5 text-xs text-gray-600">
                <svg class="w-4 h-4 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ bullet }}</span>
              </li>
            </ul>
          </article>
        </div>
      </div>
    </section>

    <!-- Powerful Admin Features Section -->
    <section class="py-24 bg-gray-50" id="admin-features" role="region" aria-label="Admin Features">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Powerful Admin Controls Built for Performance
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Monitor, manage, and optimise your business calls from one centralised platform — designed for teams of all sizes.
          </p>
        </div>

        <!-- Feature 1: Dashboard Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-24">
          <div class="order-2 lg:order-1">
            <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
              Real-Time Call Analytics Dashboard
            </h3>
            <p class="text-lg text-gray-600 mb-6 leading-relaxed">
              Get a complete, real-time view of your call operations. Callytics' live dashboard tracks total calls, answer rates, average call duration, inbound vs outbound split, missed call alerts, and daily call volume charts — all updated instantly.
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
                <span class="text-gray-700">Answer Rate & Avg Duration</span>
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
              Access complete call logs with powerful filters. Analyse agent activity, track call status, monitor SIM usage, and export detailed reports by date, department, or agent — for deeper, more accurate business analysis.
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
              Evaluate performance at both the SIM and agent level. Identify your top performers, track unanswered call patterns, analyse missed call trends, and review outbound activity — all in a structured, tabular view.
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
                <span class="text-gray-700">Unanswered Duration Stamps</span>
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
    <section class="py-24 relative overflow-hidden" id="mobile-app" role="region" aria-label="Mobile app" style="background: linear-gradient(180deg, #ffffff 0%, #fff8f3 26%, #fff1e6 58%, #fff8f3 88%, #ffffff 100%);">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-14 max-w-3xl mx-auto">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 leading-tight">
            Manage Your Business Calls On the Go
          </h2>
          <p class="text-lg md:text-xl text-gray-600 leading-relaxed mx-auto">
            Stay connected to your call data from anywhere. The Callytics mobile app gives your team real-time visibility, instant notifications, and full dashboard access — right from your smartphone.
          </p>
        </div>

        <!-- Mobile App Showcase -->
        <div class="app-showcase-grid mb-14">
          
          <!-- Left Features -->
          <div class="app-feature-stack app-features-left">
            <div class="app-feature-card">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 app-feature-icon">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
              </div>
              <div>
                <h3 class="text-lg font-bold text-gray-900 mb-3">Real-Time Notifications</h3>
                <ul class="app-feature-list">
                  <li>Instant alerts for missed & answered calls</li>
                  <li>Live agent activity tracking</li>
                  <li>Never miss a business-critical moment</li>
                </ul>
              </div>
            </div>
            <div class="app-feature-card">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 app-feature-icon">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
              </div>
              <div>
                <h3 class="text-lg font-bold text-gray-900 mb-3">Live Dashboard</h3>
                <ul class="app-feature-list">
                  <li>View live call stats & team performance</li>
                  <li>Key metrics at a glance</li>
                  <li>Available anytime, anywhere from mobile</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Center Phone Screenshots -->
          <div class="app-phone-row">
            <!-- Call Logs -->
            <div class="flex flex-col items-center group app-screen-card">
              <div class="relative app-phone-side">
                <img 
                  :src="'/mobile_app/call_logs.png'" 
                  alt="Callytics mobile app call logs screen"
                  class="w-full h-auto app-phone-img transition-all duration-500 group-hover:-translate-y-2 group-hover:app-phone-img-hover"
                  loading="lazy"
                />
              </div>
              <span class="app-phone-label text-gray-500">CALL LOGS</span>
            </div>

            <!-- Dashboard (Center - Larger) -->
            <div class="flex flex-col items-center group app-screen-card">
              <div class="relative app-phone-hero app-center-phone">
                <div class="absolute -inset-4 rounded-[2.5rem] opacity-20 group-hover:opacity-40 transition-opacity duration-500 blur-2xl" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);"></div>
                <img 
                  :src="'/mobile_app/dashboard.png'" 
                  alt="Callytics mobile app dashboard"
                  class="w-full h-auto relative z-10 app-phone-img transition-all duration-500 group-hover:-translate-y-3 group-hover:app-phone-img-hover"
                  loading="lazy"
                />
              </div>
              <span class="app-phone-label text-orange-600">DASHBOARD</span>
            </div>

            <!-- Analytics -->
            <div class="flex flex-col items-center group app-screen-card">
              <div class="relative app-phone-side">
                <img 
                  :src="'/mobile_app/analytics.png'" 
                  alt="Callytics mobile app analytics screen"
                  class="w-full h-auto app-phone-img transition-all duration-500 group-hover:-translate-y-2 group-hover:app-phone-img-hover"
                  loading="lazy"
                />
              </div>
              <span class="app-phone-label text-gray-500">ANALYTICS</span>
            </div>
          </div>

          <!-- Right Features -->
          <div class="app-feature-stack app-features-right">
            <div class="app-feature-card">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 app-feature-icon">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
              </div>
              <div>
                <h3 class="text-lg font-bold text-gray-900 mb-3">Instant Reports</h3>
                <ul class="app-feature-list">
                  <li>Access detailed call reports instantly</li>
                  <li>Export data with a single tap</li>
                  <li>Easily share insights with your team</li>
                </ul>
              </div>
            </div>
            <div class="app-feature-card">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 app-feature-icon">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
              </div>
              <div>
                <h3 class="text-lg font-bold text-gray-900 mb-3">Secure Access</h3>
                <ul class="app-feature-list">
                  <li>Role-based access control</li>
                  <li>Multi-factor authentication built-in</li>
                  <li>Your call data stays private & protected</li>
                </ul>
              </div>
            </div>
          </div>

        </div>

        <!-- Download Button -->
        <div class="text-center pt-10 border-t border-orange-200/70">
          <p class="text-lg md:text-xl text-gray-800 mb-6 font-bold max-w-3xl mx-auto">Download the Callytics App — Available on Google Play. Start tracking your business calls from your mobile today.</p>
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

    <!-- Final CTA Section -->
    <section class="py-20 text-white" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-6">
          Ready to Take Control of Your Business Calls?
        </h2>
        <p class="text-xl text-orange-50 mb-8">
          Join thousands of growing businesses across India already using Callytics to track calls, improve team performance, and maximise marketing ROI.
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

    <!-- Demo Modal -->
    <transition name="modal-fade">
      <div v-if="showDemoModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
          <!-- Background overlay -->
          <div 
            class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" 
            aria-hidden="true"
            @click="showDemoModal = false"
          ></div>

          <!-- Modal panel -->
          <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <!-- Close button -->
            <button
              @click="showDemoModal = false"
              class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors z-10"
            >
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>

            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="text-center">
                <h3 class="text-2xl font-bold text-gray-900 mb-4" id="modal-title">
                  Watch Callytics in Action
                </h3>
                <p class="text-gray-600 mb-6">
                  See how Callytics helps businesses track and optimize their call operations
                </p>
                
                <!-- Video placeholder - replace with actual video embed -->
                <div class="relative bg-gray-900 rounded-xl overflow-hidden" style="padding-bottom: 56.25%;">
                  <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center">
                      <svg class="w-20 h-20 text-white mx-auto mb-4 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" />
                      </svg>
                      <p class="text-white text-lg">Demo Video Coming Soon</p>
                      <p class="text-gray-400 text-sm mt-2">Contact us for a personalized demo</p>
                    </div>
                  </div>
                </div>

                <div class="mt-6">
                  <router-link
                    to="/contact"
                    @click="showDemoModal = false"
                    class="inline-block px-8 py-3 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300"
                    style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);"
                  >
                    Schedule a Live Demo
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import dashboardImg from '@/assets/images/dashboard.png';
import callReportImg from '@/assets/images/call_report.png';
import summaryReportImg from '@/assets/images/summary_report.png';

const showDemoModal = ref(false);

const features = [
  {
    title: 'Track Every Call, Instantly',
    description: 'See inbound & outbound calls in real-time with complete activity logs and live insights.',
    bullets: ['Live tracking', 'Call logs', 'Performance insights'],
    icon: '<svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
  },
  {
    title: 'Record & Replay Conversations',
    description: 'Automatically record calls for training, quality checks, and compliance.',
    bullets: ['Cloud storage', 'Quality assurance', 'Compliance ready'],
    icon: '<svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>'
  },
  {
    title: 'Turn Calls into Insights',
    description: 'Analyze call data like duration, sources, and conversions to improve performance.',
    bullets: ['Duration analytics', 'Source tracking', 'Conversion metrics'],
    icon: '<svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>'
  },
  {
    title: 'Seamless CRM Integration',
    description: 'Sync call data with your CRM and marketing tools for a unified customer journey.',
    bullets: ['CRM sync', 'Multi-tool integration', 'Unified data'],
    icon: '<svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>'
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
.hero-heading {
  font-size: clamp(1.75rem, 2vw + 0.875rem, 2.5rem);
}

.hero-section {
  min-height: 75vh;
  display: flex;
  align-items: center;
  background: linear-gradient(180deg, #fff8f3 0%, #ffffff 50%, #fafafa 100%);
}

/* Mobile App Screenshot Cards */
.app-showcase-grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr);
  gap: 2.5rem;
  align-items: center;
}

.app-feature-stack {
  display: grid;
  gap: 1.75rem;
}

.app-feature-card {
  min-height: 0;
  display: flex;
  align-items: flex-start;
  gap: 1.15rem;
  padding: 0.25rem 0;
  text-align: left;
  background: transparent;
  border: 0;
  border-radius: 0;
  box-shadow: none;
  backdrop-filter: none;
}

.app-feature-list {
  display: grid;
  gap: 0.5rem;
  color: #4b5563;
  font-size: 1.02rem;
  line-height: 1.45;
  list-style: disc;
  padding-left: 1.2rem;
}

.app-feature-list li::marker {
  color: #F97316;
}

.app-phone-row {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
}

.app-phone-side {
  width: min(11rem, 72vw);
}

.app-phone-hero {
  width: min(14rem, 78vw);
}

.app-phone-label {
  margin-top: 0.9rem;
  font-size: 0.72rem;
  font-weight: 800;
  line-height: 1;
  letter-spacing: 0.18em;
}

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

@media (min-width: 640px) {
  .app-phone-row {
    flex-direction: row;
    align-items: flex-end;
    gap: 0.75rem;
  }

  .app-phone-side {
    width: 10rem;
  }

  .app-phone-hero {
    width: 12rem;
  }
}

@media (min-width: 768px) {
  .app-showcase-grid {
    grid-template-columns: minmax(15rem, 0.95fr) minmax(24rem, 1.1fr);
    grid-template-areas:
      "left phones"
      "right phones";
    gap: 1.75rem 2.5rem;
  }

  .app-features-left {
    grid-area: left;
    align-self: end;
  }

  .app-features-right {
    grid-area: right;
    align-self: start;
  }

  .app-phone-row {
    grid-area: phones;
    align-self: center;
  }
}

@media (min-width: 1024px) {
  .app-showcase-grid {
    grid-template-columns: minmax(18rem, 1.1fr) minmax(29rem, auto) minmax(18rem, 1.1fr);
    grid-template-areas: "left phones right";
    gap: 2.75rem;
  }

  .app-feature-stack {
    align-self: center;
  }

  .app-phone-side {
    width: 11rem;
  }

  .app-phone-hero {
    width: 14rem;
  }
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

/* Premium Feature Cards - SaaS Style */
.premium-feature-card {
  @apply p-8 lg:p-10 bg-white rounded-2xl transition-all duration-500 border border-gray-100 flex flex-col;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04), 0 4px 16px rgba(0, 0, 0, 0.04);
  background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%);
}

.premium-feature-card:hover {
  @apply border-orange-200;
  box-shadow: 0 8px 24px rgba(255, 107, 0, 0.12), 0 4px 12px rgba(0, 0, 0, 0.06);
  transform: translateY(-12px) scale(1.01);
}

.premium-feature-icon {
  @apply w-14 h-14 rounded-2xl flex items-center justify-center text-white mb-6 transition-all duration-500 flex-shrink-0;
  background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);
  box-shadow: 0 4px 16px rgba(255, 107, 0, 0.25);
}

.premium-feature-card:hover .premium-feature-icon {
  @apply scale-110;
  box-shadow: 0 8px 28px rgba(255, 107, 0, 0.35);
  transform: scale(1.15) rotate(5deg);
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

/* Modal Transitions */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-active .inline-block,
.modal-fade-leave-active .inline-block {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-fade-enter-from .inline-block {
  transform: scale(0.95) translateY(20px);
  opacity: 0;
}

.modal-fade-leave-to .inline-block {
  transform: scale(0.95) translateY(20px);
  opacity: 0;
}
</style>
