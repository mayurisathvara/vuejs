<template>
  <div>
    <!-- Hero Section -->
    <section id="services-hero" class="relative overflow-hidden" role="banner" aria-labelledby="services-title">
      <div class="absolute inset-0" style="background: linear-gradient(135deg, #F9FAFB 0%, #EEF2F7 50%, #F3F4F6 100%);" aria-hidden="true"></div>
      <div class="absolute top-0 right-0 w-[500px] h-[500px] opacity-60" style="background: radial-gradient(circle, rgba(255, 115, 50, 0.18) 0%, transparent 70%);" aria-hidden="true"></div>
      <div class="absolute bottom-0 left-0 w-[400px] h-[400px] opacity-40" style="background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 70%);" aria-hidden="true"></div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20 lg:py-24">
        <div class="text-center max-w-3xl mx-auto">
          <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-orange-100 mb-5">
            <svg class="w-4 h-4 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            <span class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Our Services</span>
          </div>

          <h1 id="services-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-5 leading-tight">
            Call Tracking & Analytics Services
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-orange-400"> Built for Indian Businesses</span>
          </h1>

          <p class="text-lg md:text-xl text-gray-600 leading-relaxed mb-4 max-w-2xl mx-auto">
            Track, analyse, and optimise every business call with real-time insights, source attribution, and performance tracking — all from one powerful platform.
          </p>
          <p class="text-base text-gray-500 max-w-xl mx-auto">
            Trusted by 2,000+ businesses across India from Real Estate, Healthcare, E-commerce & more.
          </p>
        </div>
      </div>
    </section>

    <!-- Trust Badges -->
    <section id="trust-badges" class="py-12 bg-white border-b border-gray-100" aria-label="Trust indicators">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
          <div class="fade-up" style="animation-delay: 0s;">
            <div class="text-4xl font-bold text-orange-600 mb-2">10K+</div>
            <div class="text-sm text-gray-600 font-semibold">Businesses Served</div>
          </div>
          <div class="fade-up" style="animation-delay: 0.1s;">
            <div class="text-4xl font-bold text-orange-600 mb-2">50M+</div>
            <div class="text-sm text-gray-600 font-semibold">Calls Tracked</div>
          </div>
          <div class="fade-up" style="animation-delay: 0.2s;">
            <div class="text-4xl font-bold text-orange-600 mb-2">99.9%</div>
            <div class="text-sm text-gray-600 font-semibold">Platform Uptime</div>
          </div>
          <div class="fade-up" style="animation-delay: 0.3s;">
            <div class="text-4xl font-bold text-orange-600 mb-2">24/7</div>
            <div class="text-sm text-gray-600 font-semibold">Support Available</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Services -->
    <section id="main-services" class="py-16 bg-white" aria-labelledby="main-services-title">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
          <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-orange-100 mb-4">
            <span class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Core Services</span>
          </div>
          <h2 id="main-services-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Everything You Need to Track & Grow</h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">Powerful services designed to give your business complete call intelligence — from attribution to analytics.</p>
        </div>
        <div class="space-y-16">
          <div v-for="(service, index) in mainServices" :key="index" class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center" :class="{ 'lg:flex-row-reverse': index % 2 !== 0 }">
            <div :class="{ 'lg:order-2': index % 2 !== 0 }">
              <div class="inline-block px-4 py-1 rounded-full text-sm font-semibold mb-4" style="background-color: #ffe6cc; color: #ff6b00;">
                {{ service.category }}
              </div>
              <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">{{ service.title }}</h2>
              <p class="text-lg text-gray-700 mb-4 leading-relaxed">{{ service.description }}</p>
              
              <!-- Benefit-focused expansion -->
              <div class="bg-orange-50 border-l-4 border-orange-500 p-4 mb-6 rounded-r-lg">
                <p class="text-gray-700 text-base">
                  <strong>{{ service.benefitTitle }}</strong> {{ service.benefitText }}
                </p>
              </div>
              
              <ul class="space-y-3 mb-8">
                <li v-for="(feature, idx) in service.features" :key="idx" class="flex items-start">
                  <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span class="text-gray-700">{{ feature }}</span>
                </li>
              </ul>
              
              <!-- Mini CTAs -->
              <div class="flex flex-col sm:flex-row gap-3">
                <router-link
                  to="/pricing"
                  class="inline-flex items-center justify-center px-6 py-3 font-bold rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:scale-105"
                  style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%); color: white;"
                >
                  Explore Pricing
                  <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </router-link>
                <router-link
                  to="/contact"
                  class="inline-flex items-center justify-center px-6 py-3 bg-white text-orange-600 font-bold rounded-lg border-2 border-orange-600 hover:bg-orange-50 transition-all duration-300 hover:scale-105"
                >
                  Request a Demo
                  <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </router-link>
              </div>
            </div>
            <div :class="{ 'lg:order-1': index % 2 !== 0 }">
              <div class="relative h-80 rounded-2xl shadow-2xl flex items-center justify-center hover:shadow-3xl hover:scale-105 transition-all duration-300" style="background: linear-gradient(135deg, #ff8c33 0%, #ff6b00 100%);">
                <div v-html="service.icon" class="text-white opacity-90"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Additional Features -->
    <section id="additional-features" class="py-24 bg-gray-50" aria-labelledby="additional-features-title">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-orange-100 mb-4">
            <span class="text-sm font-semibold text-orange-600 uppercase tracking-wide">More Capabilities</span>
          </div>
          <h2 id="additional-features-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Additional Features
          </h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Everything you need to manage, track, and grow your business calls.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="(feature, index) in additionalFeatures" :key="index" class="bg-white p-8 rounded-xl shadow-md hover:shadow-2xl hover:scale-105 transition-all duration-300">
            <div class="w-14 h-14 rounded-lg flex items-center justify-center mb-4 hover:scale-110 transition-transform duration-300" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);">
              <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.iconPath" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ feature.title }}</h3>
            <p class="text-gray-600">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Why Choose Callytics for Call Tracking Services -->
    <section id="why-choose" class="py-24 bg-white" aria-labelledby="why-choose-title">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-orange-100 mb-4">
            <span class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Why Callytics</span>
          </div>
          <h2 id="why-choose-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Why Choose Callytics for Call Tracking Services
          </h2>
          <p class="text-lg text-gray-600 max-w-3xl mx-auto">
            Trusted by Indian businesses for reliability, accuracy, and results that impact the bottom line.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div class="text-center p-8 rounded-xl bg-gradient-to-br from-gray-50 to-white hover:shadow-xl hover:scale-105 transition-all duration-300">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">99.9% Uptime</h3>
            <p class="text-gray-600">Built on enterprise-grade infrastructure to ensure your call tracking never goes offline — even during peak business hours.</p>
          </div>

          <div class="text-center p-8 rounded-xl bg-gradient-to-br from-gray-50 to-white hover:shadow-xl hover:scale-105 transition-all duration-300">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">Secure Cloud Infrastructure</h3>
            <p class="text-gray-600">All call data, recordings, and reports are encrypted and stored securely. Your business data stays completely private.</p>
          </div>

          <div class="text-center p-8 rounded-xl bg-gradient-to-br from-gray-50 to-white hover:shadow-xl hover:scale-105 transition-all duration-300">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">Suitable for All Businesses</h3>
            <p class="text-gray-600">Whether you are a 2-person startup or a 200-person enterprise, Callytics scales with your growth.</p>
          </div>

          <div class="text-center p-8 rounded-xl bg-gradient-to-br from-gray-50 to-white hover:shadow-xl hover:scale-105 transition-all duration-300">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">Dedicated Support Team</h3>
            <p class="text-gray-600">Our India-based support team is available Monday to Saturday to help you get the most out of Callytics.</p>
          </div>
        </div>

        <!-- Internal Links -->
        <div class="mt-12 text-center">
          <p class="text-gray-700 text-lg mb-6">
            For more information about how Callytics can transform your business, visit our detailed resources centre.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <router-link
              to="/features"
              class="inline-flex items-center justify-center px-6 py-3 bg-white text-orange-600 font-bold rounded-lg border-2 border-orange-600 hover:bg-orange-50 transition-all duration-300 hover:scale-105"
            >
              View All Features
              <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </router-link>
            <router-link
              to="/contact"
              class="inline-flex items-center justify-center px-6 py-3 bg-white text-orange-600 font-bold rounded-lg border-2 border-orange-600 hover:bg-orange-50 transition-all duration-300 hover:scale-105"
            >
              Start a Free Trial
              <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Industries We Serve -->
    <section id="industries" class="py-24 bg-gray-50" aria-labelledby="industries-title">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-orange-100 mb-4">
            <span class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Sectors We Power</span>
          </div>
          <h2 id="industries-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Industries We Serve
          </h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Trusted by businesses across diverse sectors all over India.
          </p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <div v-for="(industry, index) in industries" :key="index" class="bg-white p-6 rounded-lg text-center transition-all duration-300 hover:bg-orange-50 hover:shadow-lg hover:scale-105">
            <div class="text-4xl mb-3">{{ industry.icon }}</div>
            <h3 class="font-semibold text-gray-900">{{ industry.name }}</h3>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonial Section -->
    <section id="testimonial" class="py-24 bg-white" aria-labelledby="testimonial-title">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl p-8 md:p-12 shadow-xl">
          <div class="flex justify-center mb-6">
            <svg class="w-12 h-12 text-orange-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
          </div>
          <blockquote class="text-center">
            <p class="text-xl md:text-2xl text-gray-800 font-medium italic mb-6 leading-relaxed">
              "Callytics transformed how we track and optimise our marketing campaigns. Within 2 months, we increased our call conversion rate by 41% and reduced wasted ad spend by ₹80,000. The ROI from call tracking alone has paid for the platform 10x over."
            </p>
            <footer class="text-gray-700">
              <p class="font-bold text-lg">Abhishek Rodrigues</p>
              <p class="text-sm">Director of Marketing, LeadGen India — Bengaluru</p>
            </footer>
          </blockquote>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section id="final-cta" class="relative py-12 text-white overflow-hidden" aria-labelledby="cta-title">
      <div class="absolute inset-0 bg-gradient-to-br from-orange-600 via-orange-500 to-orange-400"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(255,255,255,0.1)_0%,_transparent_50%)]"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,_rgba(0,0,0,0.1)_0%,_transparent_50%)]"></div>
      <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 id="cta-title" class="text-3xl md:text-4xl font-bold mb-6">
          Ready to Get Started?
        </h2>
        <p class="text-xl text-orange-50 mb-8">
          Join thousands of Indian businesses using Callytics to track calls and grow revenue.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-6">
          <router-link
            to="/contact"
            class="px-8 py-4 bg-white font-bold rounded-lg shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105"
            style="color: #ff6b00;"
          >
            Start Free Trial
          </router-link>
          <router-link
            to="/pricing"
            class="px-8 py-4 bg-white/10 backdrop-blur-sm border-2 border-white/30 text-white font-bold rounded-lg hover:bg-white/20 transition-all duration-300"
          >
            View Pricing
          </router-link>
        </div>
        <p class="text-sm text-orange-100">No credit card required · Cancel anytime · Dedicated support</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';

const mainServices = [
  {
    category: 'Core Service',
    title: 'Call Tracking & Attribution',
    description: 'Track every inbound call back to its exact marketing source — Google Ads, Meta, organic search, or offline campaigns. Understand which channels are driving real conversations and stop wasting ad spend on sources that don\'t convert.',
    benefitTitle: 'Businesses using Callytics call attribution report',
    benefitText: 'up to 35% reduction in wasted ad spend within the first 90 days.',
    features: [
      'Determine which marketing source drives each call',
      'Multi-channel attribution tracking',
      'Campaign-level call analytics',
      'Real-time spend optimisation'
    ],
    icon: '<svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>'
  },
  {
    category: 'Core Service',
    title: 'Call Recording & Analysis',
    description: 'Record, store, and analyse every business call automatically. Replay conversations for quality assurance, agent training, and compliance. Never miss a critical detail from any customer interaction again.',
    benefitTitle: 'All recordings are securely stored on cloud infrastructure',
    benefitText: 'and accessible anytime from your Callytics dashboard.',
    features: [
      'Automatic call recording',
      'Secure cloud storage',
      'Keyword search in calls',
      'Quality review workflows'
    ],
    icon: '<svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>'
  },
  {
    category: 'Core Service',
    title: 'Advanced Analytics & Reporting',
    description: 'Turn your call data into powerful business intelligence. Get detailed reports on call volume, agent performance, peak call hours, missed call trends, and conversion rates — all exportable and shareable with your team.',
    benefitTitle: 'Suit for data-driven decision makers.',
    benefitText: 'Our easy-to-read dashboards give managers and business owners full visibility without needing a technical background.',
    features: [
      'Custom report builder',
      'Agent performance scorecards',
      'Conversion tracking',
      'White-label reports',
      'Exportable data and reports'
    ],
    icon: '<svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>'
  }
];

const additionalFeatures = [
  {
    title: 'Call Routing',
    description: 'Route incoming calls to the right agent or department based on time, location, or IVR input.',
    iconPath: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'
  },
  {
    title: 'IVR System',
    description: 'Set up a professional multi-level IVR phone menu to manage and direct incoming calls efficiently.',
    iconPath: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'
  },
  {
    title: 'Call Whisper',
    description: 'Brief your agents with caller context before they pick up — improve first-call resolution rates.',
    iconPath: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'
  },
  {
    title: 'SMS Alerts',
    description: 'Get instant SMS notifications for missed calls, new leads, and other important call events.',
    iconPath: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'
  },
  {
    title: 'Multi User Access',
    description: 'Add your entire team with role-based access controls and customised permission levels.',
    iconPath: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'
  },
  {
    title: 'White Label',
    description: 'Rebrand the Callytics platform with your own company identity — ideal for agencies and resellers.',
    iconPath: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'
  }
];

const industries = [
  { name: 'Real Estate', icon: '🏠' },
  { name: 'Auto Dealers', icon: '🚘' },
  { name: 'Legal Services', icon: '⚖️' },
  { name: 'Home Services', icon: '🔧' },
  { name: 'E-commerce', icon: '🛒' },
  { name: 'Healthcare', icon: '🏥' },
  { name: 'Automotive', icon: '🚗' },
  { name: 'Education', icon: '🎓' },
  { name: 'Finance', icon: '💰' },
  { name: 'Hospitality', icon: '🏨' },
  { name: 'Media & Advertising', icon: '📊' },
  { name: 'Technology', icon: '💻' }
];

// Add structured data for SEO
onMounted(() => {
  // Organization Schema
  const organizationSchema = {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "Callytics",
    "url": "https://callytics.com",
    "logo": "https://callytics.com/logo/logo.png",
    "description": "Professional call tracking and analytics software for businesses",
    "contactPoint": {
      "@type": "ContactPoint",
      "contactType": "Customer Service",
      "telephone": "+1-800-123-4567",
      "email": "hello@callytics.com",
      "availableLanguage": "English"
    },
    "sameAs": [
      "https://www.facebook.com/callytics",
      "https://www.twitter.com/callytics",
      "https://www.linkedin.com/company/callytics"
    ]
  };

  // Service Schema for Call Tracking
  const serviceSchema = {
    "@context": "https://schema.org",
    "@type": "Service",
    "serviceType": "Call Tracking & Analytics Services",
    "provider": {
      "@type": "Organization",
      "name": "Callytics",
      "url": "https://callytics.com"
    },
    "areaServed": "Worldwide",
    "hasOfferCatalog": {
      "@type": "OfferCatalog",
      "name": "Call Tracking Services",
      "itemListElement": [
        {
          "@type": "Offer",
          "itemOffered": {
            "@type": "Service",
            "name": "Call Tracking & Attribution",
            "description": "Track every phone call back to its marketing source with precision and multi-channel attribution."
          }
        },
        {
          "@type": "Offer",
          "itemOffered": {
            "@type": "Service",
            "name": "Call Recording & Analysis",
            "description": "Automatic call recording with AI-powered transcription and sentiment analysis."
          }
        },
        {
          "@type": "Offer",
          "itemOffered": {
            "@type": "Service",
            "name": "Advanced Analytics & Reporting",
            "description": "Real-time dashboards and custom reports for data-driven decision making."
          }
        }
      ]
    },
    "offers": {
      "@type": "Offer",
      "availability": "https://schema.org/InStock",
      "priceSpecification": {
        "@type": "UnitPriceSpecification",
        "priceCurrency": "USD"
      }
    }
  };

  // Insert schemas into document head
  const orgScript = document.createElement('script');
  orgScript.type = 'application/ld+json';
  orgScript.text = JSON.stringify(organizationSchema);
  document.head.appendChild(orgScript);

  const serviceScript = document.createElement('script');
  serviceScript.type = 'application/ld+json';
  serviceScript.text = JSON.stringify(serviceSchema);
  document.head.appendChild(serviceScript);
});
</script>

<style scoped>
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

.fade-up {
  animation: fadeUp 0.6s ease-out forwards;
  opacity: 0;
}

a, button {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
