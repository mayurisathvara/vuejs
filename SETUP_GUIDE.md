# Callytics - Marketing Website

A modern SaaS marketing website built with Laravel 11, Vue 3 (Composition API), Vue Router, and Tailwind CSS.

## 🚀 Features

- **Single Page Application (SPA)** with Vue Router
- **Modern Gradient Design** with Tailwind CSS
- **Fully Responsive** design for all devices
- **No Authentication Required** - Pure marketing site
- **Smooth Scrolling** and animations
- **Scroll to Top** button
- **Active Navigation** highlighting

## 📋 Pages

1. **Home** - Hero section, Features, How It Works, Why Choose Us, Pricing Preview, FAQ
2. **About** - Company story, Mission & Vision, Values, Team
3. **Services** - Detailed service offerings and features
4. **Pricing** - Three pricing tiers with feature comparison
5. **Contact** - Contact form with client-side validation
6. **Privacy Policy** - Complete privacy policy page
7. **Terms of Service** - Comprehensive terms and conditions

## 🛠️ Tech Stack

- **Backend**: Laravel 11
- **Frontend**: Vue 3 (Composition API)
- **Routing**: Vue Router 4
- **Styling**: Tailwind CSS 3
- **Build Tool**: Vite 5
- **Package Manager**: npm

## 📦 Installation

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- npm

### Setup Steps

1. **Install PHP Dependencies**
```bash
composer install
```

2. **Install Node Dependencies**
```bash
npm install
```

3. **Environment Configuration**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Build Assets**

For development with hot reload:
```bash
npm run dev
```

For production build:
```bash
npm run build
```

5. **Start Laravel Server**
```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## 📁 Project Structure

```
resources/
├── css/
│   └── app.css                 # Tailwind directives
├── js/
│   ├── app.js                  # Vue app initialization
│   ├── App.vue                 # Root Vue component
│   ├── router/
│   │   └── index.js            # Vue Router configuration
│   ├── layouts/
│   │   └── MainLayout.vue      # Main layout wrapper
│   ├── components/
│   │   ├── Navbar.vue          # Navigation bar
│   │   └── Footer.vue          # Footer component
│   └── pages/
│       ├── Home.vue            # Home page
│       ├── About.vue           # About page
│       ├── Services.vue        # Services page
│       ├── Pricing.vue         # Pricing page
│       ├── Contact.vue         # Contact page
│       ├── Privacy.vue         # Privacy policy
│       └── Terms.vue           # Terms of service
└── views/
    └── welcome.blade.php       # Main Laravel view (SPA entry)
```

## 🎨 Design System

### Color Palette

- **Primary**: Indigo/Blue gradient (#4f46e5 to #6366f1)
- **Accent**: Purple/Cyan gradients
- **Neutral**: Gray scale for text and backgrounds

### Typography

- Clean, modern sans-serif font stack
- Responsive font sizes
- Bold headings with appropriate hierarchy

### Components

- Gradient hero sections
- Card-based layouts
- Smooth transitions and animations
- Interactive elements with hover states

## 🚀 Development

### Running Development Server

```bash
# Terminal 1 - Laravel Server
php artisan serve

# Terminal 2 - Vite Dev Server
npm run dev
```

### Building for Production

```bash
npm run build
```

This will generate optimized assets in the `public/build` directory.

## 📝 Configuration Files

- **vite.config.js** - Vite and Vue plugin configuration
- **tailwind.config.js** - Tailwind CSS customization
- **postcss.config.js** - PostCSS plugins
- **routes/web.php** - Laravel routes (SPA catch-all)

## 🔧 Customization

### Changing Brand Colors

Edit `tailwind.config.js`:
```javascript
theme: {
  extend: {
    colors: {
      primary: {
        // Your custom colors
      },
    },
  },
}
```

### Adding New Pages

1. Create new component in `resources/js/pages/`
2. Add route in `resources/js/router/index.js`
3. Update navigation in `resources/js/components/Navbar.vue`

### Contact Form

The contact form includes client-side validation only. To enable backend submission:

1. Create a controller: `php artisan make:controller ContactController`
2. Add route in `routes/api.php`
3. Update form submission in `Contact.vue`

## 📱 Responsive Breakpoints

- **Mobile**: < 768px
- **Tablet**: 768px - 1024px
- **Desktop**: > 1024px

## ⚡ Performance

- Lazy loading for routes
- Optimized images
- Minified CSS and JS in production
- Tree-shaking with Vite

## 🐛 Troubleshooting

### Assets not loading

```bash
npm run build
php artisan optimize:clear
```

### Vue Router not working

Ensure `.htaccess` is configured for SPA routing (already included in Laravel).

### Tailwind classes not applying

```bash
npm run dev
# or rebuild
npm run build
```

## 📄 License

This is a custom project. Feel free to use it as a template for your own projects.

## 🤝 Support

For questions or issues, refer to:
- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Documentation](https://vuejs.org/)
- [Tailwind CSS Documentation](https://tailwindcss.com/)

## 🎯 Features Checklist

- ✅ Vue 3 with Composition API
- ✅ Vue Router with smooth scrolling
- ✅ Tailwind CSS 3
- ✅ Responsive design
- ✅ Gradient hero sections
- ✅ Modern SaaS UI
- ✅ Scroll to top button
- ✅ Active navigation highlighting
- ✅ FAQ with accordions
- ✅ Pricing comparison tables
- ✅ Contact form validation
- ✅ Component-based architecture
- ✅ No authentication required
- ✅ Static marketing content

---

Built with ❤️ using Laravel, Vue, and Tailwind CSS
