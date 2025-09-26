<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        html { background-color: oklch(1 0 0); }
        html.dark { background-color: oklch(0.145 0 0); }
    </style>

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.png" type="image/png">
    <link rel="apple-touch-icon" href="/favicon.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="canonical" href="https://blog-app-production-16c2.up.railway.app/">
    <meta name="theme-color" content="#ffffff">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Blog",
      "url": "https://blog-app-production-16c2.up.railway.app/",
      "name": "Joni's Blog",
      "description": "A blog about web development, coding, personal projects and life in general.",
      "publisher": {
        "@type": "Person",
        "name": "Joni"
      }
    }
    </script>

    @routes
    @viteReactRefresh
    @viteCustom(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
