<template>
  <div>
    <!-- Hero Section -->
    <section id="pricing-hero" class="relative overflow-hidden" role="banner" aria-labelledby="pricing-title">
      <div class="absolute inset-0" style="background: linear-gradient(135deg, #F9FAFB 0%, #EEF2F7 50%, #F3F4F6 100%);" aria-hidden="true"></div>
      <div class="absolute top-0 right-0 w-[500px] h-[500px] opacity-60" style="background: radial-gradient(circle, rgba(255, 115, 50, 0.18) 0%, transparent 70%);" aria-hidden="true"></div>
      <div class="absolute bottom-0 left-0 w-[400px] h-[400px] opacity-40" style="background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 70%);" aria-hidden="true"></div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20 lg:py-24">
        <div class="text-center max-w-3xl mx-auto">
          <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-orange-100 mb-5">
            <svg class="w-4 h-4 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2z" />
            </svg>
            <span class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Simple & Transparent Pricing</span>
          </div>

          <h1 id="pricing-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-5 leading-tight">
            Simple Call Tracking Plans for Every
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-orange-400"> Business</span>
          </h1>

          <p class="text-lg md:text-xl text-gray-600 leading-relaxed mb-8 max-w-2xl mx-auto">
            Transparent pricing with no hidden fees. Scale as you grow with full flexibility and zero lock-ins.
          </p>

          <!-- Billing Toggle -->
          <div class="inline-flex items-center bg-white rounded-lg p-1 shadow-md border border-orange-100 mb-6">
            <button
              @click="billingType = 'monthly'"
              class="px-6 py-2 rounded-md font-semibold transition-all duration-300"
              :class="billingType === 'monthly' ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md' : 'text-gray-700 hover:text-orange-600'"
              aria-label="Select monthly billing"
            >
              Monthly
            </button>
            <button
              @click="billingType = 'annual'"
              class="px-6 py-2 rounded-md font-semibold transition-all duration-300 relative"
              :class="billingType === 'annual' ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md' : 'text-gray-700 hover:text-orange-600'"
              aria-label="Select annual billing and save 20%"
            >
              Annual
              <span class="absolute -top-2 -right-2 bg-green-500 text-white text-xs px-2 py-0.5 rounded-full shadow-md">Save 20%</span>
            </button>
          </div>

          <!-- Trust Indicators -->
          <div class="flex flex-wrap justify-center gap-6 text-sm">
            <div class="flex items-center gap-2 text-gray-600">
              <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
              </svg>
              <span class="font-medium">14-Day Free Trial</span>
            </div>
            <div class="flex items-center gap-2 text-gray-600">
              <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
              </svg>
              <span class="font-medium">No Credit Card Required</span>
            </div>
            <div class="flex items-center gap-2 text-gray-600">
              <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
              </svg>
              <span class="font-medium">Cancel Anytime</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Cards -->
    <section id="pricing-plans" class="py-10 bg-gray-50" aria-labelledby="plans-title">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 id="plans-title" class="sr-only">Pricing Plans</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 max-w-6xl mx-auto">
          <article v-for="(plan, index) in pricingPlans" :key="index" 
               class="pricing-card"
               :class="{ 'featured': plan.featured }"
               :aria-label="plan.name + ' pricing plan'">
            <div v-if="plan.featured" class="featured-badge">
              ⭐ Most Popular
            </div>
            
            <div class="p-5">
              <h3 class="text-lg font-bold text-gray-900 mb-1">{{ plan.name }}</h3>
              <p class="text-gray-600 text-sm mb-3">{{ plan.description }}</p>

              <div class="mb-3">
                <span class="text-3xl font-bold text-gray-900">
                  <template v-if="plan.customPrice">Custom</template>
                  <template v-else>₹{{ billingType === 'monthly' ? plan.monthlyPrice : plan.annualPrice }}</template>
                </span>
                <span v-if="!plan.customPrice" class="text-gray-600">/month</span>
                <div v-if="billingType === 'annual' && !plan.customPrice" class="text-sm text-green-600 font-semibold mt-1">
                  Save ₹{{ (plan.monthlyPrice - plan.annualPrice) * 12 }}/year
                </div>
              </div>

              <router-link
                to="/contact"
                class="block w-full py-2 px-5 text-center font-semibold rounded-lg transition-all duration-300 mb-2"
                :class="plan.featured ? 'text-white hover:shadow-xl hover:scale-105' : 'bg-gray-100 text-gray-900 hover:bg-gray-200'"
                :style="plan.featured ? 'background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);' : ''"
              >
                {{ plan.cta }}
              </router-link>
              
              <!-- Urgency Text -->
              <p class="text-xs text-gray-500 text-center mb-3 italic">
                Start your free trial today — upgrade anytime.
              </p>

              <div class="space-y-4">
                <div class="border-t border-gray-200 pt-4">
                  <p class="font-semibold text-gray-900 mb-3">Features:</p>
                  <ul class="space-y-2">
                    <li v-for="(feature, idx) in plan.features" :key="idx" class="flex items-start text-sm text-gray-700">
                      <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      <span>{{ feature }}</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </article>
        </div>
      </div>
    </section>

    <!-- Final CTA -->
    <section id="final-cta" class="relative py-12 text-white overflow-hidden" aria-labelledby="cta-title">
      <!-- Enhanced Gradient Background -->
      <div class="absolute inset-0 bg-gradient-to-br from-orange-600 via-orange-500 to-orange-400"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(255,255,255,0.1)_0%,_transparent_50%)]"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,_rgba(0,0,0,0.1)_0%,_transparent_50%)]"></div>
      
      <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 id="cta-title" class="text-3xl md:text-4xl font-bold mb-6">
          Ready to Start Tracking Calls?
        </h2>
        <p class="text-xl text-orange-50 mb-8">
          Join 1,000+ businesses growing smarter with Callytics
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-6">
          <router-link
            to="/contact"
            class="inline-block px-8 py-4 bg-white font-bold rounded-lg shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105"
            style="color: #ff6b00;"
          >
            Start Free Trial
          </router-link>
          <router-link
            to="/features"
            class="inline-block px-8 py-4 bg-white/10 backdrop-blur-sm border-2 border-white/30 text-white font-bold rounded-lg hover:bg-white/20 transition-all duration-300"
          >
            Talk to Sales
          </router-link>
        </div>
        <p class="text-sm text-orange-100">No credit card required · Cancel anytime · Dedicated customer support</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const billingType = ref('monthly');

const pricingPlans = [
  {
    name: 'Starter',
    description: 'Perfect for small businesses & startups',
    monthlyPrice: 2999,
    annualPrice: 2399,
    featured: false,
    cta: 'Start Free Trial',
    features: [
      '2 Tracking Numbers',
      '1,000 Minutes/month',
      'Basic Analytics Dashboard',
      'Call History & Logs',
      'Email Support',
      'Google Analytics Integration',
      'Mobile App Access'
    ]
  },
  {
    name: 'Professional',
    description: 'The go-to plan for growing businesses',
    monthlyPrice: 7999,
    annualPrice: 6399,
    featured: true,
    cta: 'Start Free Trial',
    features: [
      '10 Tracking Numbers',
      '5,000 Minutes/month',
      'Advanced Analytics & Reports',
      'Call Recording (Unlimited)',
      'Dynamic Number Insertion',
      'IVR & Call Routing',
      'Priority Support',
      'Call Whisper & Tags',
      'API Access'
    ]
  },
  {
    name: 'Enterprise',
    description: 'Built for agencies and large enterprises',
    customPrice: true,
    featured: false,
    cta: 'Contact Sales',
    features: [
      'Unlimited Tracking Numbers',
      'Unlimited Minutes',
      'Custom Analytics & Dashboards',
      'AI-Powered Call Insights',
      'Advanced Call Recording',
      'White Label Option',
      'Custom Integrations',
      'Dedicated Account Manager',
      'SLA Guarantee',
      'Custom Training & Onboarding',
      'Compliance & Data Security'
    ]
  }
];

// Add structured data for SEO
onMounted(() => {
  // Product/Offer Schema for each pricing plan
  const productsSchema = {
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "Callytics Call Tracking Software",
    "description": "Professional call tracking and analytics software for businesses",
    "brand": {
      "@type": "Brand",
      "name": "Callytics"
    },
    "offers": [
      {
        "@type": "Offer",
        "name": "Starter Plan",
        "price": "2999",
        "priceCurrency": "INR",
        "priceValidUntil": "2026-12-31",
        "availability": "https://schema.org/InStock",
        "url": "https://callytics.com/pricing",
        "description": "Perfect for small businesses getting started with call tracking"
      },
      {
        "@type": "Offer",
        "name": "Professional Plan",
        "price": "7999",
        "priceCurrency": "INR",
        "priceValidUntil": "2026-12-31",
        "availability": "https://schema.org/InStock",
        "url": "https://callytics.com/pricing",
        "description": "Most popular plan for growing businesses"
      },
      {
        "@type": "Offer",
        "name": "Enterprise Plan",
        "price": "0",
        "priceCurrency": "INR",
        "priceValidUntil": "2026-12-31",
        "availability": "https://schema.org/InStock",
        "url": "https://callytics.com/pricing",
        "description": "Custom solution for large teams"
      }
    ],
    "aggregateRating": {
      "@type": "AggregateRating",
      "ratingValue": "4.8",
      "ratingCount": "320",
      "bestRating": "5",
      "worstRating": "1"
    }
  };

  // Insert schema into document head
  const productScript = document.createElement('script');
  productScript.type = 'application/ld+json';
  productScript.text = JSON.stringify(productsSchema);
  document.head.appendChild(productScript);
});
</script>

<style scoped>
/* Enhanced pricing card styles with improved hover */
.pricing-card {
  @apply bg-white rounded-2xl shadow-lg transition-all duration-300 relative overflow-hidden;
  will-change: transform, box-shadow;
  backface-visibility: hidden;
  transform: translateZ(0);
}

.pricing-card:hover {
  @apply shadow-2xl;
  transform: translateY(-8px) scale(1.02);
}

/* Featured plan with enhanced scaling and shadow */
.pricing-card.featured {
  box-shadow: 0 20px 40px -10px rgba(255, 107, 0, 0.2),
              0 10px 20px -5px rgba(0, 0, 0, 0.08);
  border: 2px solid #ff6b00;
  z-index: 10;
}

.pricing-card.featured:hover {
  transform: translateY(-6px);
  box-shadow: 0 25px 50px -12px rgba(255, 107, 0, 0.3),
              0 15px 25px -5px rgba(0, 0, 0, 0.12);
}

.featured-badge {
  @apply text-white text-center py-1.5 px-4 text-sm font-semibold;
  background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);
}

/* Performance optimizations */
/* Use GPU acceleration for transforms */
article,
.pricing-card,
button {
  will-change: transform;
  transform: translateZ(0);
}

/* Optimize mobile view spacing */
@media (max-width: 768px) {
  #pricing-plans {
    padding-top: 2rem;
    padding-bottom: 2rem;
  }

  .pricing-card.featured {
    border: 2px solid #ff6b00;
  }

  .pricing-card.featured:hover {
    transform: translateY(-4px);
  }

  /* Make table scrollable on mobile */
  .overflow-x-auto {
    -webkit-overflow-scrolling: touch;
  }
}

/* Optimize animations - reduce motion for accessibility */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Lazy loading optimization - Note: Add loading="lazy" to images */
/* WebP format recommendation for images */
/* Preload critical fonts: <link rel="preload" href="/fonts/font.woff2" as="font" type="font/woff2" crossorigin> */

/* Enhanced button hover effects */
button:hover,
a:hover {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Improved visual hierarchy */
h1, h2, h3 {
  letter-spacing: -0.025em;
}

/* Better text readability */
p {
  line-height: 1.7;
}
</style>
