<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        html { background-color: oklch(1 0 0); }
        html.dark { background-color: oklch(0.145 0 0); }
    </style>

    <title inertia>{{ config('app.name', 'Joni\'s Blog') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.png" type="image/png">
    <link rel="apple-touch-icon" href="/favicon.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="canonical" href="https://blog.joniputkinen.com/">
    <meta name="theme-color" content="#ffffff">

    <!-- SEO meta -->
    <meta name="description" content="A blog about web development, coding, personal projects and life in general. Written by Joni Putkinen.">
    <meta name="robots" content="index, follow">
    <meta name="keywords" content="web development, web developer, Joni Putkinen">

    <!-- Open Graph -->
    <meta property="og:title" content="Joni's Blog | Joni Putkinen - Web Developer & Designer">
    <meta property="og:description" content="Web developer and designer based in Rantasalmi, Finland. Specializing in creating modern, responsive websites and applications.">
    <meta property="og:image" content="https://blog.joniputkinen.com/fallbackimage.jpg">

    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@PutkinenJoni">
    <meta name="twitter:creator" content="@PutkinenJoni">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Blog",
      "url": "https://blog.joniputkinen.com/",
      "name": "Joni's Blog",
      "description": "A blog about web development, coding, personal projects and life in general.",
      "publisher": {
        "@type": "Person",
        "name": "Joni Putkinen"
      }
    }
    </script>

    @routes
    @viteReactRefresh
    @viteCustom(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
    @inertiaHead
</head>

<body class="font-sans antialiased">

    {{-- Blade H1 for crawlers on pages without React H1 --}}
    @if(request()->attributes->get('isCrawler') && empty($__env->yieldContent('botH1')))
        <h1 class="sr-only">
            @yield('botH1', "Latest posts from Joni's Blog | Joni Putkinen – Web Developer & Designer")
        </h1>
    @endif

    <!-- Fallback content for users without JavaScript -->
    <noscript>
        <div style="padding: 20px; text-align: center; margin-top: 20px;">
            <h2>Joni's Blog | Joni Putkinen - Web Developer & Designer</h2>
            <p>A blog about web development, coding, personal projects and life in general.</p>
            <p>Please enable JavaScript for the full experience.</p>
        </div>
    </noscript>
    
    @inertia
</body>

</html>
