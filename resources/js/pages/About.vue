<template>
  <div>
    <!-- Hero Section - Premium Modern Design -->
    <section id="about-hero" class="relative text-gray-900 overflow-hidden hero-section" aria-labelledby="about-title">
      <!-- Soft Gradient Background -->
      <div class="absolute inset-0 hero-gradient"></div>
      
      <!-- Subtle Pattern Overlay -->
      <div class="absolute inset-0 hero-pattern"></div>
      
      <!-- Floating Geometric Shapes -->
      <div class="absolute top-10 left-10 w-48 h-48 bg-orange-400/10 rounded-full blur-3xl animate-float" aria-hidden="true"></div>
      <div class="absolute bottom-10 right-10 w-56 h-56 bg-orange-300/10 rounded-full blur-3xl animate-float-delayed" aria-hidden="true"></div>
      
      <!-- Content Container -->
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
        <h1 id="about-title" class="hero-heading mb-6">
          <span class="text-gray-900">About Callytics – Intelligent </span>
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-orange-500">Call Tracking & Analytics Software</span>
        </h1>
        
        <!-- SEO Introduction Paragraph -->
        <p class="text-xl text-gray-700 max-w-4xl mx-auto mb-4 leading-relaxed">
          We're transforming how businesses measure marketing success with our <strong>call tracking software</strong> and <strong>call analytics platform</strong>. Callytics provides <strong>business call monitoring</strong> solutions that optimize <strong>marketing ROI</strong>, helping companies bridge the gap between digital campaigns and real customer conversations.
        </p>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto">
          Join 10,000+ businesses that trust Callytics to track, analyze, and optimize their phone communications.
        </p>
      </div>
    </section>

    <!-- Our Story -->
    <section id="our-story" class="py-20 bg-white" aria-labelledby="story-title">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div>
            <h2 id="story-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">Our Story</h2>
            <div class="space-y-5 text-gray-700 text-lg leading-relaxed">
              <p>
                Every year, businesses invest millions in digital marketing campaigns—Google Ads, Facebook ads, SEO, content marketing. Yet when a potential customer picks up the phone to call, <strong>that critical moment vanishes into a black hole</strong>.
              </p>
              <p>
                Which campaign drove the call? Which keyword? Which landing page? <em>Nobody knows</em>. Marketing teams are forced to make decisions based on incomplete data, wasting budget on channels that don't convert and missing opportunities to scale what works.
              </p>
              <p>
                <strong>We founded Callytics in 2020 to solve this problem.</strong> We believe that phone calls are one of the highest-intent actions a customer can take—and they deserve the same level of tracking, attribution, and optimization as every website click.
              </p>
              <p>
                Today, we're proud to serve over <strong>10,000 businesses worldwide</strong>—from local service providers to Fortune 500 enterprises—helping them connect marketing spend to real revenue, understand customer behavior, and make smarter, data-driven decisions.
              </p>
            </div>
            
            <!-- CTA Button with Internal Link -->
            <div class="mt-8">
              <router-link
                to="/features"
                class="inline-flex items-center px-6 py-3 font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105"
                style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%); color: white;"
              >
                Explore Our Features
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </router-link>
            </div>
          </div>
          <div class="relative h-96">
            <!-- Modern Abstract Design -->
            <div class="absolute inset-0 rounded-2xl shadow-2xl overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100">
              <!-- Grid Pattern -->
              <div class="absolute inset-0" style="background-image: linear-gradient(rgba(255, 107, 0, 0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 107, 0, 0.03) 1px, transparent 1px); background-size: 40px 40px;"></div>
              
              <!-- Floating Orange Elements -->
              <div class="absolute top-10 right-10 w-32 h-32 rounded-full bg-gradient-to-br from-orange-400 to-orange-500 opacity-20 blur-2xl"></div>
              <div class="absolute bottom-16 left-8 w-40 h-40 rounded-full bg-gradient-to-tr from-orange-300 to-orange-400 opacity-15 blur-3xl"></div>
              <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-24 h-24 rounded-full bg-orange-500 opacity-10 blur-xl"></div>
              
              <!-- Geometric Shapes -->
              <div class="absolute top-8 left-8 w-20 h-20 border-4 border-orange-200 rounded-lg rotate-12 opacity-30"></div>
              <div class="absolute bottom-12 right-12 w-16 h-16 border-4 border-orange-300 rounded-full opacity-40"></div>
              <div class="absolute top-1/4 right-1/4 w-12 h-12 bg-orange-100 rounded-lg rotate-45 opacity-50"></div>
              
              <!-- Stats Cards Overlay -->
              <div class="absolute inset-0 flex items-center justify-center p-8">
                <div class="grid grid-cols-2 gap-4 w-full">
                  <div class="bg-white/90 backdrop-blur-sm rounded-xl p-6 shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300">
                    <div class="text-4xl font-bold text-orange-600 mb-1">10K+</div>
                    <div class="text-sm text-gray-700 font-semibold">Businesses Served</div>
                  </div>
                  <div class="bg-white/90 backdrop-blur-sm rounded-xl p-6 shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300">
                    <div class="text-4xl font-bold text-orange-600 mb-1">50M+</div>
                    <div class="text-sm text-gray-700 font-semibold">Calls Tracked</div>
                  </div>
                  <div class="bg-white/90 backdrop-blur-sm rounded-xl p-6 shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300">
                    <div class="text-4xl font-bold text-orange-600 mb-1">99.9%</div>
                    <div class="text-sm text-gray-700 font-semibold">Uptime</div>
                  </div>
                  <div class="bg-white/90 backdrop-blur-sm rounded-xl p-6 shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300">
                    <div class="text-4xl font-bold text-orange-600 mb-1">24/7</div>
                    <div class="text-sm text-gray-700 font-semibold">Support</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Mission & Vision -->
    <section id="mission-vision" class="py-20 bg-gray-50" aria-labelledby="mission-title">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
          <div class="bg-white p-10 rounded-2xl shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300">
            <div class="w-16 h-16 rounded-lg flex items-center justify-center mb-6" style="background-color: #ffe6cc;">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ff6b00;" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <h2 id="mission-title" class="text-2xl font-bold text-gray-900 mb-4">Our Mission</h2>
            <p class="text-gray-700 text-lg leading-relaxed">
              To empower businesses with intelligent call tracking and analytics tools that drive better marketing decisions and enhance customer relationships.
            </p>
          </div>
          <div class="bg-white p-10 rounded-2xl shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300">
            <div class="w-16 h-16 rounded-lg flex items-center justify-center mb-6" style="background-color: #ffd699;">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #cc5500;" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Vision</h2>
            <p class="text-gray-700 text-lg leading-relaxed">
              To become the world's most trusted call tracking platform, setting the standard for transparency, reliability, and innovation in marketing analytics.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Our Values -->
    <section id="our-values" class="py-20 bg-white" aria-labelledby="values-title">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 id="values-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Our Values</h2>
          <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            The principles that guide everything we do
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
          <div v-for="(value, index) in values" :key="index" class="text-center p-8 rounded-xl hover:bg-gray-50 transition-all duration-300 fade-up" :style="{ animationDelay: `${index * 0.1}s` }">
            <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg hover:shadow-xl hover:scale-110 transition-all duration-300" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);">
              <span class="text-3xl text-white font-bold">{{ index + 1 }}</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-4">{{ value.title }}</h3>
            <p class="text-gray-600 leading-relaxed">{{ value.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Why Businesses Trust Callytics -->
    <section id="trust" class="py-20 bg-gradient-to-br from-gray-50 to-gray-100" aria-labelledby="trust-title">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 id="trust-title" class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why Businesses Trust Callytics</h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Industry-leading security, reliability, and support that powers your business growth
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div class="bg-white rounded-xl p-8 shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 text-center">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">Enterprise-Grade Security</h3>
            <p class="text-gray-600">Bank-level encryption and secure data storage to protect your business information.</p>
          </div>

          <div class="bg-white rounded-xl p-8 shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 text-center">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">GDPR Compliance</h3>
            <p class="text-gray-600">Fully compliant with global data protection regulations and privacy standards.</p>
          </div>

          <div class="bg-white rounded-xl p-8 shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 text-center">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">Reliable Cloud Infrastructure</h3>
            <p class="text-gray-600">99.9% uptime guarantee with redundant systems and automatic failover protection.</p>
          </div>

          <div class="bg-white rounded-xl p-8 shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 text-center">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">Dedicated Support Team</h3>
            <p class="text-gray-600">24/7 expert support available via phone, email, and live chat to help you succeed.</p>
          </div>
        </div>

        <!-- Internal Links -->
        <div class="mt-12 text-center">
          <p class="text-gray-700 text-lg mb-6">
            Ready to experience the Callytics difference?
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <router-link
              to="/pricing"
              class="inline-flex items-center justify-center px-6 py-3 font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105"
              style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%); color: white;"
            >
              View Pricing Plans
              <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </router-link>
            <router-link
              to="/contact"
              class="inline-flex items-center justify-center px-6 py-3 bg-white text-orange-600 font-bold rounded-lg border-2 border-orange-600 hover:bg-orange-50 transition-all duration-300 hover:scale-105"
            >
              Contact Our Team
              <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section id="final-cta" class="py-20 text-white" style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);" aria-labelledby="cta-title">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 id="cta-title" class="text-3xl md:text-4xl font-bold mb-6">Join Us on Our Journey</h2>
        <p class="text-xl text-orange-50 mb-4">
          Be part of the next generation of data-driven marketing
        </p>
        <p class="text-lg text-orange-100 mb-8 font-medium">
          Start your 14-day free trial today — no credit card required.
        </p>
        <router-link
          to="/contact"
          class="inline-block px-8 py-4 bg-white font-bold rounded-lg shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105"
          style="color: #ff6b00;"
        >
          Get Started Today
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';

const values = [
  {
    title: 'Customer First',
    description: 'Every decision we make starts with our customers\' needs and success in mind.'
  },
  {
    title: 'Innovation',
    description: 'We continuously push boundaries to deliver cutting-edge solutions.'
  },
  {
    title: 'Integrity',
    description: 'We believe in transparency, honesty, and doing the right thing always.'
  },
  {
    title: 'Excellence',
    description: 'We strive for excellence in every aspect of our product and service.'
  },
  {
    title: 'Collaboration',
    description: 'We work together with our customers and partners to achieve shared goals.'
  },
  {
    title: 'Accountability',
    description: 'We take ownership of our commitments and deliver on our promises.'
  }
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
    "description": "Intelligent call tracking and analytics software helping businesses track, analyze, and optimize phone calls for better marketing ROI.",
    "foundingDate": "2020",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "123 Business Avenue",
      "addressLocality": "San Francisco",
      "addressRegion": "CA",
      "postalCode": "94102",
      "addressCountry": "US"
    },
    "contactPoint": {
      "@type": "ContactPoint",
      "contactType": "Customer Service",
      "telephone": "+1-800-123-4567",
      "email": "hello@callytics.com",
      "availableLanguage": "English",
      "areaServed": "Worldwide"
    },
    "sameAs": [
      "https://www.facebook.com/callytics",
      "https://www.twitter.com/callytics",
      "https://www.linkedin.com/company/callytics"
    ],
    "slogan": "Intelligent Call Tracking & Analytics Software",
    "numberOfEmployees": {
      "@type": "QuantitativeValue",
      "value": "50-100"
    },
    "knowsAbout": [
      "Call Tracking",
      "Call Analytics",
      "Marketing Analytics",
      "ROI Optimization",
      "Business Intelligence"
    ]
  };

  // AboutPage Schema
  const aboutPageSchema = {
    "@context": "https://schema.org",
    "@type": "AboutPage",
    "name": "About Callytics – Intelligent Call Tracking & Analytics Software",
    "description": "Learn about Callytics, our mission to help businesses track, analyze, and optimize phone calls using powerful call tracking and analytics software.",
    "url": "https://callytics.com/about",
    "mainEntity": {
      "@type": "Organization",
      "name": "Callytics",
      "foundingDate": "2020",
      "mission": "To empower businesses with intelligent call tracking and analytics tools that drive better marketing decisions and enhance customer relationships.",
      "award": [
        "Best Call Tracking Software 2023",
        "Top Marketing Analytics Platform 2024"
      ]
    }
  };

  // Insert schemas into document head
  const orgScript = document.createElement('script');
  orgScript.type = 'application/ld+json';
  orgScript.text = JSON.stringify(organizationSchema);
  document.head.appendChild(orgScript);

  const aboutScript = document.createElement('script');
  aboutScript.type = 'application/ld+json';
  aboutScript.text = JSON.stringify(aboutPageSchema);
  document.head.appendChild(aboutScript);
});
</script>

<style scoped>
/* Performance Optimizations */
/* Convert images to WebP format for 25-35% smaller file sizes */
/* Minify and bundle CSS/JS in production builds */
/* Use GPU acceleration for animations via transform and opacity properties */

/* Accessibility - Reduced Motion Support */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Premium Hero Section Styles */
.hero-section {
  padding-top: 2rem;
  padding-bottom: 2rem;
  background: linear-gradient(180deg, #fff8f3 0%, #ffffff 100%);
  will-change: transform;
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

/* Enhanced Float Animations with GPU Acceleration */
@keyframes float {
  0%, 100% {
    transform: translateY(0px) translateZ(0) scale(1);
  }
  50% {
    transform: translateY(-20px) translateZ(0) scale(1.05);
  }
}

@keyframes float-delayed {
  0%, 100% {
    transform: translateY(0px) translateZ(0) scale(1);
  }
  50% {
    transform: translateY(20px) translateZ(0) scale(1.05);
  }
}

.animate-float {
  animation: float 8s ease-in-out infinite;
  will-change: transform;
}

.animate-float-delayed {
  animation: float-delayed 10s ease-in-out infinite;
  animation-delay: 1s;
  will-change: transform;
}

/* Fade-up Animation for Values Section */
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
  opacity: 0;
}

/* Card Hover Effects with GPU Acceleration */
.hover\:shadow-xl,
.hover\:shadow-2xl {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  will-change: transform, box-shadow;
}

.hover\:shadow-xl:hover,
.hover\:shadow-2xl:hover {
  transform: translateY(-4px) translateZ(0);
}

.hover\:scale-105:hover {
  transform: scale(1.05) translateZ(0);
}

/* Button and Link Transitions */
a,
button {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Smooth Scrolling for Internal Links */
html {
  scroll-behavior: smooth;
}

/* Stats Cards Enhanced Animations */
@keyframes pulse-glow {
  0%, 100% {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  }
  50% {
    box-shadow: 0 20px 25px -5px rgba(255, 107, 0, 0.2), 0 10px 10px -5px rgba(255, 107, 0, 0.1);
  }
}

/* Icon Animations */
svg {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

button:hover svg,
a:hover svg {
  transform: scale(1.1) translateZ(0);
}

/* Focus States for Accessibility */
a:focus,
button:focus {
  outline: 3px solid rgba(255, 140, 51, 0.5);
  outline-offset: 2px;
}

/* Loading Optimization */
img {
  content-visibility: auto;
}

/* Mobile Responsive Adjustments */
@media (max-width: 768px) {
  .hero-section {
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
  }
  
  /* Disable hover effects on touch devices */
  .hover\:scale-105:hover,
  .hover\:shadow-xl:hover,
  .hover\:shadow-2xl:hover {
    transform: none;
  }
}

/* High Contrast Mode Support */
@media (prefers-contrast: high) {
  .text-gray-600,
  .text-gray-700 {
    color: #000;
  }
  
  .bg-gray-50 {
    background-color: #fff;
  }
}

/* Print Styles */
@media print {
  .hero-gradient,
  .hero-pattern,
  .animate-float,
  .animate-float-delayed,
  button,
  a[class*="router-link"] {
    display: none;
  }
  
  section {
    page-break-inside: avoid;
  }
}
</style>
