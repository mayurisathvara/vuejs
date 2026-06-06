import { createRouter, createWebHistory } from 'vue-router';

// Import pages
import Features from '../pages/Features.vue';
import HowItWorks from '../pages/HowItWorks.vue';
import About from '../pages/About.vue';
import Services from '../pages/Services.vue';
import Help from '../pages/Help.vue';
import Pricing from '../pages/Pricing.vue';
import Contact from '../pages/Contact.vue';
import Privacy from '../pages/Privacy.vue';
import Terms from '../pages/Terms.vue';
import NotFound from '../pages/NotFound.vue';

const routes = [
  {
    path: '/',
    name: 'Features',
    component: Features,
    meta: { 
      title: 'Callytics - Track, Analyze & Optimize Every Business Call',
      description: 'Track every business call with Callytics. Real-time analytics, call recording, and powerful insights to optimize your marketing ROI.'
    }
  },
  {
    path: '/how-it-works',
    name: 'HowItWorks',
    component: HowItWorks,
    meta: {
      title: 'How Callytics Works – 4 Simple Steps to Track Business Calls',
      description: 'Learn how Callytics works in 4 easy steps: register, install the app, let calls sync automatically, and analyse performance with real-time dashboards.'
    }
  },
  {
    path: '/about',
    name: 'About',
    component: About,
    meta: { 
      title: 'About Callytics – Call Tracking & Analytics Company',
      description: 'Learn about Callytics, our mission to help businesses track, analyze, and optimize phone calls using powerful call tracking and analytics software.'
    }
  },
  {
    path: '/services',
    name: 'Services',
    component: Services,
    meta: { 
      title: 'Call Tracking Services | Callytics Analytics Platform',
      description: 'Discover Callytics call tracking and analytics services including call recording, attribution tracking, and advanced reporting tools.'
    }
  },
  {
    path: '/help',
    name: 'Help',
    component: Help,
    meta: { 
      title: 'Callytics Support – Help Center & Customer Assistance',
      description: 'Get expert support for Callytics call tracking software. Access FAQs, live chat, email support, and integration assistance.'
    }
  },
  {
    path: '/pricing',
    name: 'Pricing',
    component: Pricing,
    meta: { 
      title: 'Callytics Pricing – Call Tracking Plans & Costs',
      description: 'View Callytics call tracking pricing plans. Flexible monthly and annual options with a 14-day free trial. No credit card required.'
    }
  },
  {
    path: '/contact',
    name: 'Contact',
    component: Contact,
    meta: { 
      title: 'Contact Callytics – Call Tracking Support & Sales',
      description: 'Contact Callytics for call tracking software support, sales inquiries, or partnership opportunities. Our team is ready to help.'
    }
  },
  {
    path: '/privacy',
    name: 'Privacy',
    component: Privacy,
    meta: { 
      title: 'Privacy Policy | Callytics Data Protection',
      description: 'Read our privacy policy to learn how Callytics protects your data and ensures GDPR compliance.'
    }
  },
  {
    path: '/terms',
    name: 'Terms',
    component: Terms,
    meta: { 
      title: 'Terms of Service | Callytics Legal Terms',
      description: 'Review the terms and conditions for using Callytics call tracking and analytics services.'
    }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFound,
    meta: { 
      title: '404 - Page Not Found | Callytics',
      description: 'The page you are looking for could not be found.'
    }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else if (to.hash) {
      return {
        el: to.hash,
        behavior: 'smooth',
      };
    } else {
      return { top: 0, behavior: 'smooth' };
    }
  }
});

// Update document title and meta tags for SEO
router.beforeEach((to) => {
  document.title = to.meta.title || 'Callytics - Call Tracking & Analytics';
  
  let metaDescription = document.querySelector('meta[name="description"]');
  if (to.meta.description) {
    if (metaDescription) {
      metaDescription.setAttribute('content', to.meta.description);
    } else {
      metaDescription = document.createElement('meta');
      metaDescription.setAttribute('name', 'description');
      metaDescription.setAttribute('content', to.meta.description);
      document.head.appendChild(metaDescription);
    }
  }
  
  let canonical = document.querySelector('link[rel="canonical"]');
  const currentUrl = window.location.origin + to.path;
  if (canonical) {
    canonical.setAttribute('href', currentUrl);
  } else {
    canonical = document.createElement('link');
    canonical.setAttribute('rel', 'canonical');
    canonical.setAttribute('href', currentUrl);
    document.head.appendChild(canonical);
  }
  
});

export default router;
