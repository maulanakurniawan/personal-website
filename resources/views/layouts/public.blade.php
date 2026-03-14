<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $resolvedMetaTitle = trim($metaTitle ?? 'Maulana Kurniawan · Senior PHP/Laravel Developer');
        $resolvedMetaDescription = trim($metaDescription ?? 'Personal website of Maulana Kurniawan, a senior PHP/Laravel developer building practical web applications, internal tools, and useful software products.');
        $resolvedCanonical = url()->current();
        $resolvedImage = $metaImage ?? asset('assets/web-app-manifest-512x512.png');
    @endphp

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $resolvedMetaTitle }}</title>
    <meta name="description" content="{{ $resolvedMetaDescription }}">
    <meta name="robots" content="{{ $metaRobots ?? 'index,follow' }}">
    <link rel="canonical" href="{{ $resolvedCanonical }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Maulana Kurniawan">
    <meta property="og:title" content="{{ $resolvedMetaTitle }}">
    <meta property="og:description" content="{{ $resolvedMetaDescription }}">
    <meta property="og:url" content="{{ $resolvedCanonical }}">
    <meta property="og:image" content="{{ $resolvedImage }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $resolvedMetaTitle }}">
    <meta name="twitter:description" content="{{ $resolvedMetaDescription }}">
    <meta name="twitter:image" content="{{ $resolvedImage }}">

    <link rel="icon" type="image/svg+xml" href="/assets/favicon.svg" />
    <link rel="icon" type="image/png" href="/assets/favicon-96x96.png" sizes="96x96" />
    <link rel="shortcut icon" href="/assets/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Maulana Kurniawan" />
    <link rel="manifest" href="/assets/site.webmanifest" />

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Person",
            "name": "Maulana Kurniawan",
            "jobTitle": "Senior PHP/Laravel Developer",
            "url": "{{ config('app.url') }}",
            "description": "{{ $resolvedMetaDescription }}"
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.google-analytics')
</head>
<body class="flex min-h-screen flex-col bg-base-100 text-base-content" data-theme="light">
    <nav class="border-b border-base-200 bg-base-100/95 backdrop-blur">
        <div class="container mx-auto flex items-center justify-between gap-3 px-4 py-3">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2" aria-label="Home">
                <img src="/assets/logo.svg" alt="Maulana Kurniawan" class="h-8 w-8 shrink-0" />
                <span class="text-sm font-semibold tracking-tight md:text-base">Maulana Kurniawan</span>
            </a>

            <div class="flex items-center gap-1 text-sm">
                <a href="{{ route('home') }}" class="btn btn-ghost btn-sm">Home</a>
                <a href="{{ route('menawan') }}" class="btn btn-ghost btn-sm">Menawan</a>
                <a href="{{ route('articles.index') }}" class="btn btn-ghost btn-sm">Articles</a>
                <a href="{{ route('contact.show') }}" class="btn btn-primary btn-sm">Contact</a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto flex-1 px-4 py-8 md:py-10">{{ $slot }}</main>

    <footer class="border-t border-base-200">
        <div class="container mx-auto flex flex-col gap-3 px-4 py-6 text-sm text-base-content/70 md:flex-row md:items-center md:justify-between">
            <div class="space-y-1">
                <p class="text-sm font-medium text-base-content">Maulana Kurniawan</p>
                <p class="text-xs text-base-content/60">Senior PHP/Laravel developer building practical web applications and useful software.</p>
                <p class="text-xs">© {{ date('Y') }} Maulana Kurniawan</p>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <a href="{{ route('home') }}" class="transition-opacity hover:opacity-100 opacity-80">Home</a>
                <a href="{{ route('menawan') }}" class="transition-opacity hover:opacity-100 opacity-80">Menawan</a>
                <a href="{{ route('articles.index') }}" class="transition-opacity hover:opacity-100 opacity-80">Articles</a>
                <a href="{{ route('contact.show') }}" class="transition-opacity hover:opacity-100 opacity-80">Contact</a>
                <a href="{{ route('terms') }}" class="transition-opacity hover:opacity-100 opacity-80">Terms</a>
                <a href="{{ route('privacy') }}" class="transition-opacity hover:opacity-100 opacity-80">Privacy</a>
            </div>
        </div>
    </footer>
</body>
</html>
