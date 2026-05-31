<template>
  <div>
    <!-- Hero Section - Premium Modern Design -->
    <section id="pricing-hero" class="relative text-gray-900 overflow-hidden pricing-hero-section" aria-labelledby="pricing-title">
      <!-- Soft Gradient Background -->
      <div class="absolute inset-0 pricing-hero-gradient"></div>
      
      <!-- Subtle Pattern Overlay -->
      <div class="absolute inset-0 pricing-hero-pattern"></div>
      
      <!-- Floating Geometric Shapes -->
      <div class="absolute top-16 left-10 w-64 h-64 bg-orange-400/10 rounded-full blur-3xl animate-float" aria-hidden="true"></div>
      <div class="absolute bottom-10 right-10 w-72 h-72 bg-orange-300/10 rounded-full blur-3xl animate-float-delayed" aria-hidden="true"></div>
      
      <!-- Content Container -->
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center">
        <!-- Badge -->
        <div class="inline-flex items-center px-4 py-2 rounded-full bg-white/90 backdrop-blur-sm shadow-lg mb-4">
          <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 animate-pulse" aria-hidden="true"></span>
          <span class="text-sm font-semibold text-orange-600">Simple & Transparent Pricing</span>
        </div>

        <h1 id="pricing-title" class="hero-heading mb-3">
          <span class="text-gray-900">Simple Call Tracking Plans for Every </span>
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-orange-500">Business</span>
        </h1>
        
        <!-- Subheading -->
        <p class="text-lg text-gray-700 max-w-3xl mx-auto mb-2 leading-relaxed">
          Transparent pricing with no hidden fees. Scale as you grow with <strong>full flexibility</strong> and zero lock-ins.
        </p>
        <p class="text-base text-gray-600 max-w-2xl mx-auto mb-5">
          14-day free trial. No credit card required.
        </p>
        
        <!-- Billing Toggle -->
        <div class="inline-flex items-center bg-white/90 backdrop-blur-sm rounded-lg p-1 shadow-lg border border-orange-100 mb-4">
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
        <div class="flex flex-wrap justify-center gap-6 text-sm text-gray-600">
          <span class="inline-flex items-center font-medium">
            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            14-Day Free Trial
          </span>
          <span class="inline-flex items-center font-medium">
            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            No Credit Card Required
          </span>
          <span class="inline-flex items-center font-medium">
            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            Cancel Anytime
          </span>
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
.hero-heading {
  font-size: clamp(1.75rem, 2vw + 0.875rem, 2.5rem);
}

/* Premium Pricing Hero Styles */
.pricing-hero-section {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
  background: linear-gradient(180deg, #fff8f3 0%, #ffffff 100%);
}

.pricing-hero-gradient {
  background: radial-gradient(ellipse at top, rgba(255, 140, 51, 0.12) 0%, transparent 60%),
              radial-gradient(ellipse at bottom, rgba(255, 107, 0, 0.08) 0%, transparent 50%);
}

.pricing-hero-pattern {
  background-image: 
    linear-gradient(rgba(255, 140, 51, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 140, 51, 0.03) 1px, transparent 1px);
  background-size: 50px 50px;
  opacity: 0.5;
}

/* Floating animations for hero section */
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
  .pricing-hero-section {
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
  }

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
