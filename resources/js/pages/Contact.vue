<template>
  <div>
    <!-- Hero Section - Premium Modern Design -->
    <section id="contact-hero" class="relative text-gray-900 overflow-hidden hero-section" aria-labelledby="contact-title">
      <!-- Soft Gradient Background -->
      <div class="absolute inset-0 hero-gradient"></div>
      
      <!-- Subtle Pattern Overlay -->
      <div class="absolute inset-0 hero-pattern"></div>
      
      <!-- Floating Geometric Shapes -->
      <div class="absolute top-10 left-10 w-48 h-48 bg-orange-400/10 rounded-full blur-3xl animate-float" aria-hidden="true"></div>
      <div class="absolute bottom-10 right-10 w-56 h-56 bg-orange-300/10 rounded-full blur-3xl animate-float-delayed" aria-hidden="true"></div>
      
      <!-- Content Container -->
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
        <!-- Badge -->
        <div class="inline-flex items-center px-4 py-2 rounded-full bg-white/90 backdrop-blur-sm shadow-lg mb-8">
          <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 animate-pulse" aria-hidden="true"></span>
          <span class="text-sm font-semibold text-orange-600">Get in Touch</span>
        </div>

        <h1 id="contact-title" class="hero-heading mb-6">
          <span class="text-gray-900">Talk to Our </span>
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-orange-500">Call Tracking Experts</span>
        </h1>
        
        <!-- Subheading -->
        <p class="text-xl text-gray-700 max-w-4xl mx-auto mb-4 leading-relaxed">
          Have questions about pricing, features, or getting started? Our team is ready to help you find the right plan for your business — fast response guaranteed.
        </p>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto">
          Response within 24 hours. Secure communication. Real experts.
        </p>
      </div>
    </section>

    <!-- Contact Section -->
    <section id="contact-form-section" class="py-24 bg-gray-50" aria-labelledby="form-title">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <!-- Contact Form -->
          <div>
            <!-- Trust Indicators -->
            <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl p-6 mb-8 shadow-md">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center">
                  <svg class="w-6 h-6 text-orange-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-sm font-semibold text-gray-900">Response within 24 hours</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-6 h-6 text-orange-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-sm font-semibold text-gray-900">99.9% uptime support</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-6 h-6 text-orange-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-sm font-semibold text-gray-900">Secure & confidential</span>
                </div>
              </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12">
              <h2 id="form-title" class="text-3xl font-bold text-gray-900 mb-2">Send Us a Message</h2>
              <p class="text-gray-600 mb-8">Fill out the form below and our team will get back to you within 24 business hours.</p>
            
            <form @submit.prevent="handleSubmit" class="space-y-7" novalidate aria-label="Contact form">
              <!-- Name -->
              <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                  Full Name <span class="text-red-500" aria-label="required">*</span>
                </label>
                <input
                  v-model="formData.name"
                  @input="validateField('name')"
                  type="text"
                  id="name"
                  name="name"
                  required
                  aria-required="true"
                  aria-invalid="errors.name ? 'true' : 'false'"
                  :aria-describedby="errors.name ? 'name-error' : undefined"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                  :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-200': errors.name }"
                  placeholder="Your Full Name"
                />
                <p v-if="errors.name" id="name-error" class="text-red-500 text-sm mt-1" role="alert">{{ errors.name }}</p>
              </div>

              <!-- Email -->
              <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                  Email Address <span class="text-red-500" aria-label="required">*</span>
                </label>
                <input
                  v-model="formData.email"
                  @input="validateField('email')"
                  type="email"
                  id="email"
                  name="email"
                  required
                  aria-required="true"
                  aria-invalid="errors.email ? 'true' : 'false'"
                  :aria-describedby="errors.email ? 'email-error' : undefined"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                  :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-200': errors.email }"
                  placeholder="your@email.com"
                />
                <p v-if="errors.email" id="email-error" class="text-red-500 text-sm mt-1" role="alert">{{ errors.email }}</p>
              </div>

              <!-- Phone -->
              <div>
                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                  Phone Number <span class="text-gray-400 text-xs">(Optional)</span>
                </label>
                <input
                  v-model="formData.phone"
                  type="tel"
                  id="phone"
                  name="phone"
                  aria-label="Phone number optional"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                  placeholder="+91 98765 43210"
                />
              </div>

              <!-- Company -->
              <div>
                <label for="company" class="block text-sm font-semibold text-gray-700 mb-2">
                  Company Name <span class="text-gray-400 text-xs">(Optional)</span>
                </label>
                <input
                  v-model="formData.company"
                  type="text"
                  id="company"
                  name="company"
                  aria-label="Company name optional"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                  placeholder="Your Company Name"
                />
              </div>

              <!-- Subject -->
              <div>
                <label for="subject" class="block text-sm font-semibold text-gray-700 mb-2">
                  Subject <span class="text-red-500" aria-label="required">*</span>
                </label>
                <select
                  v-model="formData.subject"
                  @change="validateField('subject')"
                  id="subject"
                  name="subject"
                  required
                  aria-required="true"
                  aria-invalid="errors.subject ? 'true' : 'false'"
                  :aria-describedby="errors.subject ? 'subject-error' : undefined"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                  :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-200': errors.subject }"
                >
                  <option value="">Select a subject</option>
                  <option value="general">General Enquiry</option>
                  <option value="sales">Sales & Pricing</option>
                  <option value="support">Technical Support</option>
                  <option value="feature">Feature Request</option>
                  <option value="partnership">Partnership</option>
                  <option value="other">Other</option>
                </select>
                <p v-if="errors.subject" id="subject-error" class="text-red-500 text-sm mt-1" role="alert">{{ errors.subject }}</p>
              </div>

              <!-- Message -->
              <div>
                <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">
                  Message <span class="text-red-500" aria-label="required">*</span>
                </label>
                <textarea
                  v-model="formData.message"
                  @input="validateField('message')"
                  id="message"
                  name="message"
                  rows="6"
                  required
                  aria-required="true"
                  aria-invalid="errors.message ? 'true' : 'false'"
                  :aria-describedby="errors.message ? 'message-error' : undefined"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none transition resize-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                  :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-200': errors.message }"
                  placeholder="Tell us about your business and how we can help..."
                ></textarea>
                <p v-if="errors.message" id="message-error" class="text-red-500 text-sm mt-1" role="alert">{{ errors.message }}</p>
                <p class="text-xs text-gray-500 mt-1">Minimum 10 characters</p>
              </div>

              <!-- Submit Button -->
              <div>
                <button
                  type="submit"
                  class="w-full text-white font-bold py-4 px-6 rounded-lg transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-xl hover:scale-105"
                  :disabled="isSubmitting"
                  style="background: linear-gradient(135deg, #ff6b00 0%, #ff8c33 100%);"
                  :aria-busy="isSubmitting"
                >
                  <span v-if="!isSubmitting">Send Message</span>
                  <span v-else class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Sending...
                  </span>
                </button>
                
                <!-- Privacy Microcopy -->
                <p class="text-xs text-gray-500 text-center mt-3 flex items-center justify-center">
                  <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                  </svg>
                  Your information is safe with us. We never share your data with third parties.
                </p>
              </div>

              <!-- Server Error Message -->
              <transition name="fade">
                <div v-if="submitError" class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg" role="alert">
                  <div class="flex items-start">
                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-red-800 font-medium">{{ submitError }}</p>
                  </div>
                </div>
              </transition>

              <!-- Success Message -->
              <transition name="fade">
                <div v-if="showSuccess" class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg" role="status">
                  <div class="flex items-start">
                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <div>
                      <p class="font-bold text-green-800">Message sent successfully!</p>
                      <p class="text-green-700 text-sm mt-1">Thank you for contacting us. We'll respond within 24 hours.</p>
                    </div>
                  </div>
                </div>
              </transition>
            </form>
            
            <!-- Trust Badge / Testimonial Snippet -->
            <div class="mt-8 bg-gray-50 rounded-lg p-6 border-l-4 border-orange-500">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <svg class="w-8 h-8 text-orange-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm text-gray-700 italic mb-2">
                    "The Callytics team responded to our query within a few hours and helped us set up call tracking for our entire sales team. Excellent support experience!"
                  </p>
                  <p class="text-xs font-semibold text-gray-900">- Priya M., Marketing Head — Ahmedabad</p>
                </div>
              </div>
            </div>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="space-y-8">
            <!-- Contact Cards -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
              <h2 class="text-2xl font-bold text-gray-900 mb-6">Contact Information</h2>
              
              <div class="space-y-6">
                <div class="flex items-start">
                  <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: #ffe6cc;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ff6b00;">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div class="ml-4">
                    <h4 class="font-semibold text-gray-900 mb-1">Email</h4>
                    <a href="mailto:hello@callytics.io" style="color: #ff6b00;" @mouseover="$event.target.style.color='#cc5500'" @mouseout="$event.target.style.color='#ff6b00'">
                      hello@callytics.io
                    </a>
                  </div>
                </div>

                <div class="flex items-start">
                  <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: #ffe6cc;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ff6b00;">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                  </div>
                  <div class="ml-4">
                    <h4 class="font-semibold text-gray-900 mb-1">Phone</h4>
                    <a href="tel:+919876543210" style="color: #ff6b00;" @mouseover="$event.target.style.color='#cc5500'" @mouseout="$event.target.style.color='#ff6b00'">
                      +91 98765 43210
                    </a>
                  </div>
                </div>

              </div>
            </div>

            <!-- Support Card -->
            <div class="relative rounded-2xl shadow-xl p-8 text-white overflow-hidden">
              <div class="absolute inset-0 bg-gradient-to-br from-orange-600 via-orange-500 to-orange-400"></div>
              <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(255,255,255,0.1)_0%,_transparent_50%)]"></div>
              
              <div class="relative">
                <h2 class="text-2xl font-bold mb-4">Need Immediate Help?</h2>
                <p class="text-orange-50 mb-6">
                  Our support team is available Monday to Saturday to assist you with any urgent queries or issues.
                </p>
                <div>
                  <a
                    href="mailto:support@callytics.com"
                    class="inline-flex items-center gap-2 bg-white font-bold px-6 py-3 rounded-lg hover:shadow-xl transition-all duration-300 hover:scale-105 text-center"
                    style="color: #ff6b00;"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Email Support
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';

const submitError = ref('');

const formData = reactive({
  name: '',
  email: '',
  phone: '',
  company: '',
  subject: '',
  message: ''
});

const errors = reactive({
  name: '',
  email: '',
  subject: '',
  message: ''
});

const isSubmitting = ref(false);
const showSuccess = ref(false);

// Real-time field validation
const validateField = (field) => {
  switch (field) {
    case 'name':
      if (!formData.name.trim()) {
        errors.name = 'Name is required';
      } else if (formData.name.trim().length < 2) {
        errors.name = 'Name must be at least 2 characters';
      } else {
        errors.name = '';
      }
      break;
    
    case 'email':
      if (!formData.email.trim()) {
        errors.email = 'Email is required';
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
        errors.email = 'Please enter a valid email address';
      } else {
        errors.email = '';
      }
      break;
    
    case 'subject':
      if (!formData.subject) {
        errors.subject = 'Please select a subject';
      } else {
        errors.subject = '';
      }
      break;
    
    case 'message':
      if (!formData.message.trim()) {
        errors.message = 'Message is required';
      } else if (formData.message.trim().length < 10) {
        errors.message = 'Message must be at least 10 characters';
      } else {
        errors.message = '';
      }
      break;
  }
};

const validateForm = () => {
  let isValid = true;
  
  // Validate all fields
  validateField('name');
  validateField('email');
  validateField('subject');
  validateField('message');
  
  // Check if any errors exist
  if (errors.name || errors.email || errors.subject || errors.message) {
    isValid = false;
  }

  return isValid;
};

const handleSubmit = async () => {
  submitError.value = '';

  if (!validateForm()) {
    const firstError = document.querySelector('[aria-invalid="true"]');
    if (firstError) {
      firstError.focus();
      firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return;
  }

  isSubmitting.value = true;

  try {
    const response = await fetch('/api/contact', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ ...formData }),
    });

    const data = await response.json();

    if (!response.ok) {
      if (response.status === 422 && data.errors) {
        Object.keys(data.errors).forEach((field) => {
          if (field in errors) {
            errors[field] = data.errors[field][0];
          }
        });
        const firstError = document.querySelector('[aria-invalid="true"]');
        if (firstError) {
          firstError.focus();
          firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      } else {
        submitError.value = data.message || 'Something went wrong. Please try again.';
      }
      return;
    }

    showSuccess.value = true;
    formData.name = '';
    formData.email = '';
    formData.phone = '';
    formData.company = '';
    formData.subject = '';
    formData.message = '';

    setTimeout(() => {
      const successMessage = document.querySelector('[role="status"]');
      if (successMessage) {
        successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }, 100);

    setTimeout(() => {
      showSuccess.value = false;
    }, 8000);

  } catch {
    submitError.value = 'Network error. Please check your connection and try again.';
  } finally {
    isSubmitting.value = false;
  }
};

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
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "123 Business Avenue",
      "addressLocality": "San Francisco",
      "addressRegion": "CA",
      "postalCode": "94102",
      "addressCountry": "US"
    },
    "contactPoint": [
      {
        "@type": "ContactPoint",
        "contactType": "Customer Service",
        "telephone": "+1-800-123-4567",
        "email": "hello@callytics.com",
        "availableLanguage": "English",
        "areaServed": "US"
      },
      {
        "@type": "ContactPoint",
        "contactType": "Technical Support",
        "telephone": "+1-800-123-4567",
        "email": "support@callytics.com",
        "availableLanguage": "English",
        "hoursAvailable": "24/7"
      },
      {
        "@type": "ContactPoint",
        "contactType": "Sales",
        "telephone": "+1-800-123-4567",
        "email": "sales@callytics.com",
        "availableLanguage": "English"
      }
    ],
    "sameAs": [
      "https://www.facebook.com/callytics",
      "https://www.twitter.com/callytics",
      "https://www.linkedin.com/company/callytics"
    ]
  };

  // LocalBusiness Schema
  const localBusinessSchema = {
    "@context": "https://schema.org",
    "@type": "LocalBusiness",
    "@id": "https://callytics.com",
    "name": "Callytics",
    "image": "https://callytics.com/logo/logo.png",
    "telephone": "+1-800-123-4567",
    "email": "hello@callytics.com",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "123 Business Avenue",
      "addressLocality": "San Francisco",
      "addressRegion": "CA",
      "postalCode": "94102",
      "addressCountry": "US"
    },
    "geo": {
      "@type": "GeoCoordinates",
      "latitude": "37.7749",
      "longitude": "-122.4194"
    },
    "openingHoursSpecification": [
      {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": [
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday"
        ],
        "opens": "09:00",
        "closes": "18:00"
      }
    ],
    "priceRange": "$$",
    "aggregateRating": {
      "@type": "AggregateRating",
      "ratingValue": "4.8",
      "ratingCount": "320"
    }
  };

  // Insert schemas into document head
  const orgScript = document.createElement('script');
  orgScript.type = 'application/ld+json';
  orgScript.text = JSON.stringify(organizationSchema);
  document.head.appendChild(orgScript);

  const businessScript = document.createElement('script');
  businessScript.type = 'application/ld+json';
  businessScript.text = JSON.stringify(localBusinessSchema);
  document.head.appendChild(businessScript);
});
</script>

<style scoped>
.hero-heading {
  font-size: clamp(1.75rem, 2vw + 0.875rem, 2.5rem);
}

/* Performance Optimizations */
/* Use GPU acceleration for animations via transform and opacity properties */
/* Recommend converting images to WebP format for faster loading */
/* Consider lazy loading off-screen content with Intersection Observer API */

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

/* Transition Animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  will-change: opacity;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
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

/* Enhanced Form Focus States */
input:focus,
select:focus,
textarea:focus {
  outline: none;
  border-color: #ff8c33 !important;
  box-shadow: 0 0 0 3px rgba(255, 140, 51, 0.1), 0 1px 3px rgba(0, 0, 0, 0.1) !important;
  transform: translateY(-1px);
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

input:hover,
select:hover,
textarea:hover {
  border-color: #cbd5e1;
  transition: border-color 0.2s ease;
}

/* Enhanced Error States */
input[aria-invalid="true"],
select[aria-invalid="true"],
textarea[aria-invalid="true"] {
  border-color: #ef4444 !important;
  background-color: #fef2f2;
}

input[aria-invalid="true"]:focus,
select[aria-invalid="true"]:focus,
textarea[aria-invalid="true"]:focus {
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1), 0 1px 3px rgba(0, 0, 0, 0.1) !important;
}

/* Button Hover Effects with GPU Acceleration */
button {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  will-change: transform;
}

button:hover {
  transform: translateY(-2px) translateZ(0);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
}

button:active {
  transform: translateY(0) translateZ(0);
}

/* Card Hover Effects */
.hover\:shadow-xl {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  will-change: transform, box-shadow;
}

.hover\:shadow-xl:hover {
  transform: translateY(-4px) translateZ(0) scale(1.02);
}

/* Success Message Animation */
@keyframes slideInDown {
  from {
    opacity: 0;
    transform: translateY(-20px) translateZ(0);
  }
  to {
    opacity: 1;
    transform: translateY(0) translateZ(0);
  }
}

@keyframes pulse-success {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
  }
}

[role="alert"] {
  animation: slideInDown 0.4s cubic-bezier(0.4, 0, 0.2, 1), pulse-success 2s ease-in-out 3;
}

/* Fade-up animations for content sections */
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
}

.fade-up-delay-1 {
  animation: fadeUp 0.6s ease-out 0.1s forwards;
  opacity: 0;
}

.fade-up-delay-2 {
  animation: fadeUp 0.6s ease-out 0.2s forwards;
  opacity: 0;
}

.fade-up-delay-3 {
  animation: fadeUp 0.6s ease-out 0.3s forwards;
  opacity: 0;
}

/* Loading Spinner Animation */
@keyframes spin {
  to {
    transform: rotate(360deg) translateZ(0);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Link Hover Effects */
a {
  transition: color 0.2s ease, transform 0.2s ease;
  display: inline-block;
}

a:hover {
  transform: translateX(3px);
}

/* Icon animations */
svg {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

button:hover svg,
a:hover svg {
  transform: scale(1.1) translateZ(0);
}

/* Mobile Responsive Adjustments */
@media (max-width: 768px) {
  .hero-section {
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
  }
  
  input, select, textarea {
    font-size: 16px; /* Prevent zoom on iOS */
  }
  
  button:hover {
    transform: none; /* Disable hover effects on touch devices */
  }
  
  .hover\:shadow-xl:hover {
    transform: none;
  }
}

/* Print Styles for SEO/Accessibility */
@media print {
  nav, button, .animate-float, .animate-float-delayed {
    display: none;
  }
}
</style>
