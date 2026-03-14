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
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
        <!-- Badge -->
        <div class="inline-flex items-center px-4 py-2 rounded-full bg-white/90 backdrop-blur-sm shadow-lg mb-8">
          <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 animate-pulse" aria-hidden="true"></span>
          <span class="text-sm font-semibold text-orange-600">Simple & Transparent Pricing</span>
        </div>

        <h1 id="pricing-title" class="hero-heading mb-6">
          <span class="text-gray-900">Call Tracking Pricing for </span>
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-orange-500">Every Business</span>
        </h1>
        
        <!-- Subheading -->
        <p class="text-xl text-gray-700 max-w-4xl mx-auto mb-4 leading-relaxed">
          Choose the plan that fits your team. Scale as you grow with <strong>flexible pricing</strong> and no hidden fees.
        </p>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto mb-10">
          14-day free trial. No credit card required.
        </p>
        
        <!-- Billing Toggle -->
        <div class="inline-flex items-center bg-white/90 backdrop-blur-sm rounded-lg p-1 shadow-lg border border-orange-100 mb-6">
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
    <section id="pricing-plans" class="py-24 bg-gray-50" aria-labelledby="plans-title">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 id="plans-title" class="sr-only">Pricing Plans</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
          <article v-for="(plan, index) in pricingPlans" :key="index" 
               class="pricing-card"
               :class="{ 'featured': plan.featured }"
               :aria-label="plan.name + ' pricing plan'">
            <div v-if="plan.featured" class="featured-badge">
              ⭐ Most Popular
            </div>
            
            <div class="p-8">
              <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ plan.name }}</h3>
              <p class="text-gray-600 mb-6">{{ plan.description }}</p>
              
              <div class="mb-6">
                <span class="text-5xl font-bold text-gray-900">
                  ${{ billingType === 'monthly' ? plan.monthlyPrice : plan.annualPrice }}
                </span>
                <span class="text-gray-600">/month</span>
                <div v-if="billingType === 'annual'" class="text-sm text-green-600 font-semibold mt-1">
                  Save ${{ (plan.monthlyPrice - plan.annualPrice) * 12 }}/year
                </div>
              </div>

              <router-link
                to="/contact"
                class="block w-full py-3 px-6 text-center font-semibold rounded-lg transition-all duration-300 mb-4"
                :class="plan.featured ? 'text-white hover:shadow-xl hover:scale-105' : 'bg-gray-100 text-gray-900 hover:bg-gray-200'"
                :style="plan.featured ? 'background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);' : ''"
              >
                {{ plan.cta }}
              </router-link>
              
              <!-- Urgency Text -->
              <p class="text-xs text-gray-500 text-center mb-6 italic">
                Start your free trial today — upgrade anytime.
              </p>

              <div class="space-y-4">
                <div class="border-t border-gray-200 pt-4">
                  <p class="font-semibold text-gray-900 mb-3">Features:</p>
                  <ul class="space-y-3">
                    <li v-for="(feature, idx) in plan.features" :key="idx" class="flex items-start text-gray-700">
                      <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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

    <!-- Feature Comparison -->
    <section id="feature-comparison" class="py-24 bg-white" aria-labelledby="comparison-title">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 id="comparison-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Feature Comparison
          </h2>
          <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            See exactly what's included in each plan
          </p>
        </div>

        <div class="overflow-x-auto shadow-lg rounded-lg">
          <table class="w-full bg-white">
            <thead>
              <tr class="border-b-2 border-gray-200">
                <th class="text-left py-4 px-6 font-semibold text-gray-900">Feature</th>
                <th class="text-center py-4 px-6 font-semibold text-gray-900">Starter</th>
                <th class="text-center py-4 px-6 font-semibold text-gray-900" style="background-color: #fff5e6;">Professional</th>
                <th class="text-center py-4 px-6 font-semibold text-gray-900">Enterprise</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in comparisonTable" :key="index" class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                <td class="py-4 px-6 font-medium text-gray-900">{{ row.feature }}</td>
                <td class="text-center py-4 px-6">
                  <span v-if="typeof row.starter === 'boolean'">
                    <svg v-if="row.starter" class="w-6 h-6 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-label="Included">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg v-else class="w-6 h-6 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-label="Not included">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </span>
                  <span v-else class="text-gray-700">{{ row.starter }}</span>
                </td>
                <td class="text-center py-4 px-6" style="background-color: #fff5e6;">
                  <span v-if="typeof row.professional === 'boolean'">
                    <svg v-if="row.professional" class="w-6 h-6 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-label="Included">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg v-else class="w-6 h-6 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-label="Not included">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </span>
                  <span v-else class="text-gray-700 font-medium">{{ row.professional }}</span>
                </td>
                <td class="text-center py-4 px-6">
                  <span v-if="typeof row.enterprise === 'boolean'">
                    <svg v-if="row.enterprise" class="w-6 h-6 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-label="Included">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg v-else class="w-6 h-6 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-label="Not included">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </span>
                  <span v-else class="text-gray-700">{{ row.enterprise }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Mini CTA Below Comparison -->
        <div class="mt-16 text-center">
          <p class="text-gray-700 mb-6 text-lg">Ready to choose your plan?</p>
          <router-link to="/contact" 
            class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-orange-600 to-orange-500 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
            Start Your Free Trial
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </router-link>
          <p class="text-sm text-gray-500 mt-4">No credit card required • Cancel anytime</p>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section id="pricing-faq" class="py-24 bg-gray-50" aria-labelledby="faq-title">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 id="faq-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Pricing FAQs
          </h2>
          <p class="text-lg text-gray-600">Common questions about our pricing plans</p>
        </div>

        <div class="space-y-4">
          <article v-for="(faq, index) in faqs" :key="index" class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
            <button
              @click="toggleFaq(index)"
              class="w-full flex justify-between items-center p-6 text-left focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 rounded-lg"
              :aria-expanded="openFaqIndex === index"
            >
              <span class="text-lg font-semibold text-gray-900 pr-8">{{ faq.question }}</span>
              <svg
                class="w-6 h-6 transition-transform duration-300 flex-shrink-0"
                :class="{ 'rotate-180': openFaqIndex === index }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                style="color: #ff6b00;"
                aria-hidden="true"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <transition
              name="faq-slide"
              @enter="startTransition"
              @after-enter="endTransition"
              @before-leave="startTransition"
              @after-leave="endTransition"
            >
              <div v-if="openFaqIndex === index" class="faq-content">
                <div class="px-6 pb-6">
                  <p class="text-gray-700 leading-relaxed">{{ faq.answer }}</p>
                </div>
              </div>
            </transition>
          </article>
        </div>
      </div>
    </section>

    <!-- Testimonial Section -->
    <section id="testimonial" class="py-20 bg-white" aria-label="Customer testimonial">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl p-8 md:p-12 shadow-xl">
          <div class="flex flex-col items-center text-center">
            <div class="flex mb-4">
              <svg v-for="i in 5" :key="i" class="w-6 h-6 text-orange-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </div>
            <blockquote class="text-xl md:text-2xl text-gray-900 font-medium mb-6 italic max-w-3xl">
              "Callytics pricing is incredibly transparent. We started with the Professional plan and saw ROI within the first month. The flexibility to scale up has been perfect for our growing business."
            </blockquote>
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-bold text-xl mb-3">
              JM
            </div>
            <div class="font-bold text-gray-900 text-lg">Jennifer Martinez</div>
            <div class="text-gray-600">Director of Marketing, TechGrowth Solutions</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Final CTA -->
    <section id="final-cta" class="relative py-24 text-white overflow-hidden" aria-labelledby="cta-title">
      <!-- Enhanced Gradient Background -->
      <div class="absolute inset-0 bg-gradient-to-br from-orange-600 via-orange-500 to-orange-400"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(255,255,255,0.1)_0%,_transparent_50%)]"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,_rgba(0,0,0,0.1)_0%,_transparent_50%)]"></div>
      
      <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 id="cta-title" class="text-3xl md:text-4xl font-bold mb-6">
          Still Have Questions?
        </h2>
        <p class="text-xl text-orange-50 mb-8">
          Our team is here to help you find the perfect plan for your business
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-6">
          <router-link
            to="/contact"
            class="inline-block px-8 py-4 bg-white font-bold rounded-lg shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105"
            style="color: #ff6b00;"
          >
            Contact Sales
          </router-link>
          <router-link
            to="/features"
            class="inline-block px-8 py-4 bg-white/10 backdrop-blur-sm border-2 border-white/30 text-white font-bold rounded-lg hover:bg-white/20 transition-all duration-300"
          >
            View All Features
          </router-link>
        </div>
        <p class="text-sm text-orange-100">Join thousands of businesses tracking calls with Callytics</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const billingType = ref('monthly');
const openFaqIndex = ref(null);

const pricingPlans = [
  {
    name: 'Starter',
    description: 'Perfect for small businesses getting started',
    monthlyPrice: 49,
    annualPrice: 39,
    featured: false,
    cta: 'Start Free Trial',
    features: [
      '1 Tracking Number',
      '500 Minutes/month',
      'Basic Analytics Dashboard',
      'Call History & Logs',
      'Email Support',
      'Google Analytics Integration',
      'Mobile App Access'
    ]
  },
  {
    name: 'Professional',
    description: 'Most popular for growing businesses',
    monthlyPrice: 99,
    annualPrice: 79,
    featured: true,
    cta: 'Start Free Trial',
    features: [
      '5 Tracking Numbers',
      '2000 Minutes/month',
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
    description: 'For large teams with custom needs',
    monthlyPrice: 249,
    annualPrice: 199,
    featured: false,
    cta: 'Contact Sales',
    features: [
      'Unlimited Tracking Numbers',
      'Unlimited Minutes',
      'Custom Analytics & Dashboards',
      'AI-Powered Call Analysis',
      'Advanced Call Recording',
      'White Label Option',
      'Custom Integrations',
      'Dedicated Account Manager',
      'SLA Guarantee',
      'Custom Training & Onboarding',
      'Advanced Security Features'
    ]
  }
];

const comparisonTable = [
  { feature: 'Tracking Numbers', starter: '1', professional: '5', enterprise: 'Unlimited' },
  { feature: 'Monthly Minutes', starter: '500', professional: '2000', enterprise: 'Unlimited' },
  { feature: 'Call Recording', starter: false, professional: true, enterprise: true },
  { feature: 'Dynamic Number Insertion', starter: false, professional: true, enterprise: true },
  { feature: 'Call Analytics', starter: 'Basic', professional: 'Advanced', enterprise: 'Custom' },
  { feature: 'IVR System', starter: false, professional: true, enterprise: true },
  { feature: 'Call Routing', starter: false, professional: true, enterprise: true },
  { feature: 'API Access', starter: false, professional: true, enterprise: true },
  { feature: 'White Label', starter: false, professional: false, enterprise: true },
  { feature: 'AI Analysis', starter: false, professional: false, enterprise: true },
  { feature: 'Support', starter: 'Email', professional: 'Priority', enterprise: 'Dedicated' }
];

const faqs = [
  {
    question: 'Can I change plans later?',
    answer: 'Yes! You can upgrade or downgrade your plan at any time. Upgrades take effect immediately, while downgrades apply at the start of your next billing cycle.'
  },
  {
    question: 'What happens if I exceed my minutes?',
    answer: 'We\'ll notify you when you reach 80% of your limit. Additional minutes are charged at $0.10/minute. You can also upgrade to a higher plan at any time to get more included minutes.'
  },
  {
    question: 'Is there a setup fee?',
    answer: 'No setup fees! All plans include free setup and onboarding assistance to help you get started quickly.'
  },
  {
    question: 'Do you offer refunds?',
    answer: 'We offer a 14-day free trial so you can test our service risk-free. If you\'re not satisfied within the first 30 days of paid service, we\'ll provide a full refund.'
  },
  {
    question: 'Can I get a custom plan?',
    answer: 'Yes! If none of our standard plans fit your needs, contact our sales team to discuss a custom enterprise solution tailored to your requirements.'
  }
];

const toggleFaq = (index) => {
  openFaqIndex.value = openFaqIndex.value === index ? null : index;
};

// FAQ accordion transition handlers
const startTransition = (el) => {
  el.style.height = '0';
};

const endTransition = (el) => {
  el.style.height = '';
};

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
        "price": "49",
        "priceCurrency": "USD",
        "priceValidUntil": "2026-12-31",
        "availability": "https://schema.org/InStock",
        "url": "https://callytics.com/pricing",
        "description": "Perfect for small businesses getting started with call tracking"
      },
      {
        "@type": "Offer",
        "name": "Professional Plan",
        "price": "99",
        "priceCurrency": "USD",
        "priceValidUntil": "2026-12-31",
        "availability": "https://schema.org/InStock",
        "url": "https://callytics.com/pricing",
        "description": "Most popular plan for growing businesses"
      },
      {
        "@type": "Offer",
        "name": "Enterprise Plan",
        "price": "249",
        "priceCurrency": "USD",
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

  // FAQPage Schema
  const faqSchema = {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": faqs.map(faq => ({
      "@type": "Question",
      "name": faq.question,
      "acceptedAnswer": {
        "@type": "Answer",
        "text": faq.answer
      }
    }))
  };

  // Insert schemas into document head
  const productScript = document.createElement('script');
  productScript.type = 'application/ld+json';
  productScript.text = JSON.stringify(productsSchema);
  document.head.appendChild(productScript);

  const faqScript = document.createElement('script');
  faqScript.type = 'application/ld+json';
  faqScript.text = JSON.stringify(faqSchema);
  document.head.appendChild(faqScript);
});
</script>

<style scoped>
/* Premium Pricing Hero Styles */
.pricing-hero-section {
  padding-top: 2rem;
  padding-bottom: 2rem;
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
  transform: scale(1.08);
  box-shadow: 0 25px 50px -12px rgba(255, 107, 0, 0.25), 
              0 20px 25px -5px rgba(0, 0, 0, 0.1);
  border: 2px solid #ff6b00;
  z-index: 10;
}

.pricing-card.featured:hover {
  transform: scale(1.10) translateY(-8px);
  box-shadow: 0 30px 60px -15px rgba(255, 107, 0, 0.35), 
              0 25px 30px -5px rgba(0, 0, 0, 0.15);
}

.featured-badge {
  @apply text-white text-center py-3 px-6 font-semibold;
  background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);
}

/* Improved FAQ accordion animations */
.faq-slide-enter-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
}

.faq-slide-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
}

.faq-slide-enter-from,
.faq-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.faq-slide-enter-to,
.faq-slide-leave-from {
  opacity: 1;
  transform: translateY(0);
}

.faq-content {
  animation: slideDown 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
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
    padding-top: 3rem;
    padding-bottom: 3rem;
  }

  #feature-comparison,
  #pricing-faq,
  #testimonial {
    padding-top: 3rem;
    padding-bottom: 3rem;
  }

  .pricing-card.featured {
    transform: scale(1.02);
  }

  .pricing-card.featured:hover {
    transform: scale(1.04) translateY(-4px);
  }

  /* Make table scrollable on mobile */
  .overflow-x-auto {
    -webkit-overflow-scrolling: touch;
  }
}

/* Enhanced responsive spacing for larger screens */
@media (min-width: 768px) {
  section {
    padding-top: 6rem;
    padding-bottom: 6rem;
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
