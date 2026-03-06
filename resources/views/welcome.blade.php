<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <!-- SEO Meta Tags -->
        <title>Callytics - Track, Analyze & Optimize Every Business Call</title>
        <meta name="description" content="Track every business call with Callytics. Real-time analytics, call recording, and powerful insights to optimize your marketing ROI. Trusted by 2,000+ businesses. Start free trial.">
        <meta name="keywords" content="call tracking software, call analytics, business call monitoring, marketing ROI tracking, phone call tracking, call recording, lead tracking, conversion tracking">
        <meta name="author" content="Callytics">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ url()->current() }}">
        
        <!-- Open Graph Meta Tags -->
        <meta property="og:type" content="website">
        <meta property="og:title" content="Callytics - Track, Analyze & Optimize Every Business Call">
        <meta property="og:description" content="Track every business call with powerful analytics. Trusted by 2,000+ businesses. Start your free trial today.">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="Callytics">
        <meta property="og:image" content="{{ asset('logo/logo.png') }}">
        
        <!-- Twitter Card Meta Tags -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Callytics - Track, Analyze & Optimize Every Business Call">
        <meta name="twitter:description" content="Track every business call with powerful analytics. Trusted by 2,000+ businesses.">
        <meta name="twitter:image" content="{{ asset('logo/logo.png') }}">
        
        <!-- Favicon -->
        <link rel="icon" type="image/png" href="/favicon/favicon.png">
        
        <!-- Structured Data - Organization Schema -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "Callytics",
            "url": "{{ url('/') }}",
            "logo": "{{ asset('logo/logo.png') }}",
            "description": "Call tracking and analytics software for businesses",
            "address": {
                "@type": "PostalAddress",
                "addressCountry": "US"
            },
            "contactPoint": {
                "@type": "ContactPoint",
                "email": "hello@callytics.com",
                "contactType": "customer service"
            },
            "sameAs": []
        }
        </script>
        
        <!-- Structured Data - SoftwareApplication Schema -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "SoftwareApplication",
            "name": "Callytics",
            "applicationCategory": "BusinessApplication",
            "operatingSystem": "Web",
            "offers": {
                "@type": "Offer",
                "price": "49",
                "priceCurrency": "USD"
            },
            "aggregateRating": {
                "@type": "AggregateRating",
                "ratingValue": "4.8",
                "ratingCount": "2000"
            }
        }
        </script>

        <!-- Vite -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div id="app"></div>
    </body>
</html>
