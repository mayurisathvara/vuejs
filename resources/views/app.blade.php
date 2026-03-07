<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Primary SEO Meta Tags -->
    <title>Callytics - Call Analytics & Management Platform</title>
    <meta name="description" content="Callytics is a powerful call analytics and management platform. Track call logs, manage SIMs, monitor team performance, and generate insightful reports.">
    <meta name="keywords" content="call analytics, call management, SIM management, call reports, team management, call tracking, business phone analytics">
    <meta name="author" content="Callytics">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#1a2035">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Callytics - Call Analytics & Management Platform">
    <meta property="og:description" content="Track call logs, manage SIMs, monitor team performance, and generate insightful reports with Callytics.">
    <meta property="og:image" content="/logo/logo.png">
    <meta property="og:site_name" content="Callytics">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Callytics - Call Analytics & Management Platform">
    <meta name="twitter:description" content="Track call logs, manage SIMs, monitor team performance, and generate insightful reports with Callytics.">
    <meta name="twitter:image" content="/logo/logo.png">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/logo/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/logo/favicon.png">
    <link rel="shortcut icon" href="/logo/favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/logo/favicon.png">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Fonts and icons -->
    <script src="/assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular", 
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["/assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/assets/css/plugins.min.css" />
    <link rel="stylesheet" href="/assets/css/adminpro.min.css" />
    <link rel="stylesheet" href="/assets/css/demo.css" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app"></div>
    
    <!-- Theme JavaScript -->
    <script src="/assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="/assets/js/core/popper.min.js"></script>
    <script src="/assets/js/core/bootstrap.min.js"></script>
    <script src="/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="/assets/js/plugin/chart.js/chart.min.js"></script>
    <script src="/assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>
    <script src="/assets/js/plugin/chart-circle/circles.min.js"></script>
    <script src="/assets/js/plugin/datatables/datatables.min.js"></script>
    <script src="/assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>
    <script src="/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <script src="/assets/js/adminpro.min.js"></script>
    <script src="/assets/js/demo.js"></script>
</body>
</html>
