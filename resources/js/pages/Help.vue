<template>
  <div>
    <!-- Hero Section - Premium Modern Design -->
    <section id="help-hero" class="relative text-gray-900 overflow-hidden hero-section" aria-labelledby="help-title">
      <!-- Soft Gradient Background -->
      <div class="absolute inset-0 hero-gradient"></div>
      
      <!-- Subtle Pattern Overlay -->
      <div class="absolute inset-0 hero-pattern"></div>
      
      <!-- Floating Geometric Shapes -->
      <div class="absolute top-10 left-10 w-48 h-48 bg-orange-400/10 rounded-full blur-3xl animate-float" aria-hidden="true"></div>
      <div class="absolute bottom-10 right-10 w-56 h-56 bg-orange-300/10 rounded-full blur-3xl animate-float-delayed" aria-hidden="true"></div>
      
      <!-- Content Container -->
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center">
        <!-- Badge -->
        <div class="inline-flex items-center px-4 py-2 rounded-full bg-white/90 backdrop-blur-sm shadow-lg mb-4">
          <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 animate-pulse" aria-hidden="true"></span>
          <span class="text-sm font-semibold text-orange-600">Support Centre</span>
        </div>

        <h1 id="help-title" class="hero-heading mb-3">
          <span class="text-gray-900">Callytics Help & Support — </span>
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-orange-500">Get Expert Assistance</span>
        </h1>
        
        <!-- SEO Introduction Paragraph -->
        <p class="text-lg text-gray-700 max-w-3xl mx-auto mb-2 leading-relaxed">
          Access comprehensive call tracking support and call analytics assistance from our expert team. Get help with setup, troubleshooting, and maximising your business call monitoring platform.
        </p>
        <p class="text-base text-gray-600 max-w-2xl mx-auto">
          We are here Monday to Saturday to ensure you get the most out of Callytics.
        </p>
      </div>
    </section>

    <!-- Email Support Section -->
    <section id="email-support" class="py-16 bg-white border-b border-gray-100" aria-labelledby="email-support-title">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

          <!-- Left: Email Info -->
          <div>
            <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-orange-100 mb-5">
              <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 animate-pulse"></span>
              <span class="text-sm font-semibold text-orange-600">Email Support</span>
            </div>

            <h2 id="email-support-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              Reach Us by Email —
              <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-orange-500">24/7 Available</span>
            </h2>
            <p class="text-gray-600 mb-6 text-lg leading-relaxed">
              Whether you have a technical issue, billing question, or need guidance on getting started — our support team is always here. Drop us an email and we will get back to you promptly.
            </p>

            <!-- Email Address Card -->
            <a href="mailto:support@callytics.com"
               class="flex items-center p-4 bg-orange-50 rounded-xl border border-orange-200 mb-6 hover:border-orange-400 hover:shadow-md transition-all duration-300 group no-text-decoration">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background: #ffe6cc;">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ff6b00;" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-0.5">Email Us At</p>
                <span class="text-lg font-bold group-hover:text-orange-700 transition-colors" style="color: #ff6b00;">
                  support@callytics.com
                </span>
              </div>
              <svg class="w-5 h-5 ml-auto text-orange-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </a>

            <!-- Promises Grid -->
            <div class="grid grid-cols-2 gap-3 mb-8">
              <div v-for="promise in emailPromises" :key="promise" class="flex items-center gap-2">
                <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0" style="background: #ffe6cc;">
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" style="color: #ff6b00;" aria-hidden="true">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </div>
                <span class="text-sm text-gray-700 font-medium">{{ promise }}</span>
              </div>
            </div>

            <!-- CTA Button -->
            <a
              href="mailto:support@callytics.com"
              class="inline-flex items-center gap-2 text-white font-bold px-8 py-3.5 rounded-xl shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300"
              style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              Send Us an Email
            </a>
          </div>

          <!-- Right: What we help with + Response stat -->
          <div class="space-y-5">
            <h3 class="text-xl font-bold text-gray-900 mb-2">What Can We Help With?</h3>
            <div class="grid grid-cols-2 gap-4">
              <div v-for="topic in helpTopics" :key="topic.title"
                   class="p-5 bg-gray-50 rounded-xl border border-gray-200 hover:border-orange-300 hover:bg-orange-50 transition-all duration-300 group">
                <div class="text-2xl mb-2" aria-hidden="true">{{ topic.icon }}</div>
                <h4 class="font-bold text-gray-900 text-sm mb-1 group-hover:text-orange-600 transition-colors">{{ topic.title }}</h4>
                <p class="text-xs text-gray-500">{{ topic.desc }}</p>
              </div>
            </div>

            <!-- Response Time Banner -->
            <div class="relative rounded-xl p-6 text-white overflow-hidden">
              <div class="absolute inset-0 bg-gradient-to-r from-orange-600 to-orange-400"></div>
              <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(255,255,255,0.12)_0%,_transparent_60%)]"></div>
              <div class="relative flex items-center justify-between">
                <div>
                  <p class="text-orange-100 text-sm font-medium mb-1">Average Response Time</p>
                  <p class="text-3xl font-bold">24 Hours</p>
                  <p class="text-orange-100 text-sm mt-1">Mon – Sat, 9 AM – 7 PM IST</p>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                  <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- Documentation & Resources -->
    <section id="resources-section" class="py-24 bg-white" aria-labelledby="resources-title">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 id="resources-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Documentation & Resources</h2>
          <p class="text-xl text-gray-600">Everything you need to master Callytics and maximise your call tracking ROI.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="(resource, index) in resources" :key="index"
               class="resource-card bg-white rounded-xl p-6 hover:border-orange-300 transition-all duration-300 cursor-pointer border-2 border-transparent">
            <div class="text-4xl mb-4" aria-hidden="true">{{ resource.icon }}</div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ resource.title }}</h3>
            <p class="text-sm text-gray-600 mb-4">{{ resource.description }}</p>
            <div class="flex items-center font-semibold text-sm" style="color: #ff6b00;">
              Learn More
              <svg class="w-4 h-4 ml-1 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Internal Linking Section -->
        <div class="mt-16 bg-gray-50 rounded-2xl shadow-lg p-8 border border-gray-100">
          <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">Explore More Callytics Resources</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <router-link to="/features" class="p-4 rounded-xl hover:bg-orange-50 transition-all duration-300 border border-gray-200 hover:border-orange-300 group">
              <div class="flex items-start">
                <svg class="w-6 h-6 text-orange-600 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <div>
                  <h4 class="font-bold text-gray-900 mb-1 group-hover:text-orange-600 transition-colors">Explore Features</h4>
                  <p class="text-sm text-gray-600">Discover powerful call tracking capabilities</p>
                </div>
              </div>
            </router-link>

            <router-link to="/pricing" class="p-4 rounded-xl hover:bg-orange-50 transition-all duration-300 border border-gray-200 hover:border-orange-300 group">
              <div class="flex items-start">
                <svg class="w-6 h-6 text-orange-600 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                  <h4 class="font-bold text-gray-900 mb-1 group-hover:text-orange-600 transition-colors">View Pricing Plans</h4>
                  <p class="text-sm text-gray-600">Find the perfect plan for your business</p>
                </div>
              </div>
            </router-link>

            <router-link to="/contact" class="p-4 rounded-xl hover:bg-orange-50 transition-all duration-300 border border-gray-200 hover:border-orange-300 group">
              <div class="flex items-start">
                <svg class="w-6 h-6 text-orange-600 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <div>
                  <h4 class="font-bold text-gray-900 mb-1 group-hover:text-orange-600 transition-colors">Contact Support</h4>
                  <p class="text-sm text-gray-600">Get connected to an expert from our team</p>
                </div>
              </div>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq-section" class="py-24 bg-gray-50" aria-labelledby="faq-title">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 id="faq-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h2>
          <p class="text-xl text-gray-600">Quick answers to common questions about Callytics</p>
        </div>

        <!-- Search Bar -->
        <div class="mb-10">
          <div class="relative max-w-2xl mx-auto">
            <input
              v-model="faqSearchQuery"
              type="search"
              placeholder="Search help articles..."
              class="w-full px-5 py-4 pl-12 pr-4 text-gray-900 bg-white border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
              aria-label="Search FAQ articles"
            />
            <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>

        <div class="space-y-4">
          <div v-for="(faq, index) in filteredFaqs" :key="index" class="faq-item">
            <button
              @click="toggleFaq(index)"
              class="w-full px-6 py-5 flex justify-between items-center text-left"
              :aria-expanded="openFaqIndex === index"
              :aria-controls="'faq-answer-' + index"
            >
              <span class="font-semibold text-gray-900 text-lg pr-4">{{ faq.question }}</span>
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
            <transition name="faq-content">
              <div v-if="openFaqIndex === index" :id="'faq-answer-' + index" class="px-6 pb-5" role="region">
                <p class="text-gray-700 leading-relaxed">{{ faq.answer }}</p>
              </div>
            </transition>
          </div>

          <!-- No Results Message -->
          <div v-if="filteredFaqs.length === 0" class="text-center py-12 bg-white rounded-xl">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-gray-600 text-lg mb-2">No matching help articles found</p>
            <p class="text-gray-500 text-sm">Try a different search term or <router-link to="/contact" class="text-orange-600 hover:underline font-semibold">contact our support team</router-link></p>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonial Section -->
    <section id="support-testimonial" class="py-20 bg-gradient-to-br from-orange-50 to-gray-50" aria-label="Customer testimonial">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12 relative overflow-hidden">
          <!-- Decorative quote icon -->
          <svg class="absolute top-4 left-4 w-16 h-16 text-orange-100 opacity-50" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
          </svg>
          
          <div class="relative">
            <p class="text-xl md:text-2xl text-gray-800 font-medium mb-6 leading-relaxed italic">
              "The Callytics support team resolved our integration issue within 30 minutes. Their expertise and response time is genuinely unmatched. We have never experienced this level of service from any SaaS platform before."
            </p>
            
            <div class="flex items-center justify-center">
              <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4" style="background: linear-gradient(135deg, #ff6b00, #ff8c33);">
                SP
              </div>
              <div class="text-left">
                <div class="font-bold text-gray-900">Sneha Patel</div>
                <div class="text-sm text-gray-600">Operations Manager, InfoTech Solutions — Ahmedabad</div>
              </div>
            </div>
            
            <!-- Star Rating -->
            <div class="flex items-center justify-center mt-6 gap-1" aria-label="5 out of 5 stars">
              <svg v-for="i in 5" :key="i" class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </div>
          </div>
        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const openFaqIndex = ref(null);
const faqSearchQuery = ref('');

const faqs = [
  {
    question: 'How does call tracking work?',
    answer: 'Callytics assigns unique tracking numbers to each of your marketing channels. When a customer calls, Callytics captures the source, duration, recording, and outcome — giving you complete visibility into which campaigns are generating real business calls.'
  },
  {
    question: 'How do I get started with Callytics?',
    answer: 'Getting started is simple. Register your free account, install the Callytics app on your Android device, and your calls will start tracking automatically within minutes — no technical setup required.'
  },
  {
    question: 'Is Callytics GDPR and TRAI compliant?',
    answer: 'Yes. Callytics is fully compliant with GDPR and TRAI regulations. All call data is encrypted, securely stored, and handled in accordance with Indian and international data protection laws.'
  },
  {
    question: 'How long are call recordings stored?',
    answer: 'Call recordings are stored securely in the cloud for up to 90 days on the Starter plan, 180 days on the Professional plan, and unlimited duration on the Enterprise plan.'
  },
  {
    question: 'Can I upgrade or downgrade my plan anytime?',
    answer: 'Yes. You can change your plan at any time from your Callytics dashboard. Upgrades take effect immediately and billing is adjusted on a pro-rata basis.'
  },
  {
    question: 'Is call recording legal in India?',
    answer: 'Call recording for business purposes is permitted in India provided that at least one party to the call is aware of the recording. Callytics recommends informing callers via an IVR announcement before the call connects.'
  },
  {
    question: 'What happens if I exceed my plan limits?',
    answer: 'Your calls will continue uninterrupted. Additional usage beyond your plan limits is billed at a standard per-minute or per-number rate. You will receive a notification before approaching your limit.'
  },
  {
    question: 'Do you offer training for my team?',
    answer: 'Yes. All Professional and Enterprise plan customers receive onboarding support and access to our full video tutorial library. Enterprise customers also receive dedicated training sessions.'
  },
  {
    question: 'Can I port my existing phone numbers to Callytics?',
    answer: 'Yes. Number porting is supported on Professional and Enterprise plans. Contact our support team and we will guide you through the porting process step by step.'
  },
  {
    question: 'What is your refund policy?',
    answer: 'We offer a 14-day free trial so you can evaluate Callytics risk-free before committing. Refund requests after the trial period are reviewed individually by our billing team.'
  },
  {
    question: 'Does Callytics work across India?',
    answer: 'Yes. Callytics works nationwide across all Indian states and supports all major telecom operators including Jio, Airtel, Vi, and BSNL.'
  }
];

// Filtered FAQs based on search query
const filteredFaqs = computed(() => {
  if (!faqSearchQuery.value.trim()) {
    return faqs;
  }
  
  const query = faqSearchQuery.value.toLowerCase().trim();
  return faqs.filter(faq => 
    faq.question.toLowerCase().includes(query) || 
    faq.answer.toLowerCase().includes(query)
  );
});

const resources = [
  {
    icon: '📘',
    title: 'Getting Started Guide',
    description: 'Step-by-step instructions for new users.'
  },
  {
    icon: '📹',
    title: 'Video Tutorials',
    description: 'Watch and learn with our video library.'
  },
  {
    icon: '🔧',
    title: 'API Documentation',
    description: 'Technical docs for developers and integrations.'
  },
  {
    icon: '💡',
    title: 'Best Practices',
    description: 'Tips and strategies to maximise your ROI.'
  },
  {
    icon: '📊',
    title: 'Use Cases',
    description: 'Real-world examples from successful Indian businesses.'
  },
  {
    icon: '🎓',
    title: 'Webinars',
    description: 'Join our live call tracking sessions.'
  },
  {
    icon: '📱',
    title: 'Mobile App Guide',
    description: 'Complete guide for iOS and Android app.'
  },
  {
    icon: '🔒',
    title: 'Security & Compliance',
    description: 'How we keep your data safe and compliant.'
  }
];

const emailPromises = [
  'Reply within 24 hours',
  'Available Mon – Sat',
  'Expert support team',
  'Secure & confidential',
  'No call waiting needed',
  'Detailed written answers',
];

const helpTopics = [
  { icon: '🔧', title: 'Technical Issues', desc: 'Setup, bugs, and platform errors' },
  { icon: '💳', title: 'Billing & Plans', desc: 'Invoices, upgrades, and payments' },
  { icon: '🚀', title: 'Getting Started', desc: 'Onboarding and first-time setup' },
  { icon: '🔗', title: 'Integrations', desc: 'CRM, API, and third-party tools' },
];

const toggleFaq = (index) => {
  openFaqIndex.value = openFaqIndex.value === index ? null : index;
};

// Add structured data on component mount
onMounted(() => {
  // Remove any existing structured data
  const existingScripts = document.querySelectorAll('script[type="application/ld+json"]');
  existingScripts.forEach(script => script.remove());

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

  // Organization Schema
  const organizationSchema = {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "Callytics",
    "url": "https://callytics.com",
    "logo": "https://callytics.com/logo.png",
    "description": "Advanced call tracking and analytics software for businesses",
    "contactPoint": [
      {
        "@type": "ContactPoint",
        "telephone": "+1-800-123-4567",
        "contactType": "customer support",
        "areaServed": ["US", "CA", "GB", "AU"],
        "availableLanguage": ["English"],
        "hoursAvailable": {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
          "opens": "09:00",
          "closes": "18:00"
        }
      },
      {
        "@type": "ContactPoint",
        "contactType": "technical support",
        "email": "support@callytics.com",
        "areaServed": "Worldwide",
        "availableLanguage": ["English"],
        "hoursAvailable": "Mo-Su 00:00-24:00"
      }
    ],
    "sameAs": [
      "https://www.facebook.com/callytics",
      "https://twitter.com/callytics",
      "https://www.linkedin.com/company/callytics"
    ]
  };

  // Insert schemas into DOM
  const faqScript = document.createElement('script');
  faqScript.type = 'application/ld+json';
  faqScript.text = JSON.stringify(faqSchema);
  document.head.appendChild(faqScript);

  const orgScript = document.createElement('script');
  orgScript.type = 'application/ld+json';
  orgScript.text = JSON.stringify(organizationSchema);
  document.head.appendChild(orgScript);
});
</script>

<style scoped>
.hero-heading {
  font-size: clamp(1.75rem, 2vw + 0.875rem, 2.5rem);
}
/* Premium Hero Section Styles */
.hero-section {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
  background: linear-gradient(180deg, #fff8f3 0%, #ffffff 100%);
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

/* Fade-up animations with GPU acceleration */
@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(30px) translateZ(0);
  }
  to {
    opacity: 1;
    transform: translateY(0) translateZ(0);
  }
}

.fade-up {
  animation: fadeUp 0.6s ease-out forwards;
  transform: translateZ(0);
  will-change: transform, opacity;
}

/* Float animations with GPU acceleration */
@keyframes float {
  0%, 100% {
    transform: translateY(0px) scale(1) translateZ(0);
  }
  50% {
    transform: translateY(-20px) scale(1.05) translateZ(0);
  }
}

@keyframes float-delayed {
  0%, 100% {
    transform: translateY(0px) scale(1) translateZ(0);
  }
  50% {
    transform: translateY(20px) scale(1.05) translateZ(0);
  }
}

.animate-float {
  animation: float 8s ease-in-out infinite;
  transform: translateZ(0);
  will-change: transform;
}

.animate-float-delayed {
  animation: float-delayed 10s ease-in-out infinite;
  animation-delay: 1s;
  transform: translateZ(0);
  will-change: transform;
}

/* FAQ Section with GPU acceleration */
.faq-item {
  @apply bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 border border-gray-200;
  transform: translateZ(0);
  will-change: box-shadow, border-color;
}

.faq-item:hover {
  border-color: rgba(255, 107, 0, 0.3);
}

.faq-content-enter-active,
.faq-content-leave-active {
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  max-height: 500px;
  overflow: hidden;
  transform: translateZ(0);
}

.faq-content-enter-from,
.faq-content-leave-to {
  max-height: 0;
  opacity: 0;
  transform: translateY(-10px) translateZ(0);
}

/* Resource cards with enhanced hover effects */
.resource-card {
  transform: translateZ(0);
  will-change: transform, box-shadow;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.resource-card:hover {
  transform: translateY(-4px) translateZ(0);
  box-shadow: 0 20px 25px -5px rgba(255, 107, 0, 0.1), 0 10px 10px -5px rgba(255, 107, 0, 0.04);
}

/* Search input focus enhancement */
input[type="search"]:focus {
  box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.1);
  transform: translateZ(0);
}

/* Support card hover effects with GPU acceleration */
.bg-white.rounded-xl.p-8 {
  transform: translateZ(0);
  will-change: transform, box-shadow;
  backface-visibility: hidden;
}

.hover\:scale-105:hover {
  transform: scale(1.05) translateZ(0) !important;
}

/* Testimonial section enhancement */
#support-testimonial .bg-white {
  transform: translateZ(0);
  will-change: transform;
  backface-visibility: hidden;
}

/* Reduced motion support for accessibility */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
  
  .fade-up, .animate-float, .animate-float-delayed {
    animation: none;
  }
  
  .hover\:scale-105:hover {
    transform: none !important;
  }
}

/* Section spacing optimization */
section {
  scroll-margin-top: 80px;
}

/* Enhanced button transitions */
button, a {
  transform: translateZ(0);
  backface-visibility: hidden;
}
</style>
