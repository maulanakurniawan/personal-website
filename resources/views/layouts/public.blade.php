<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $resolvedMetaTitle = trim($metaTitle ?? 'Simple Time Tracking for Freelancers - SoloHours');
        $resolvedMetaDescription = trim($metaDescription ?? 'SoloHours is a simple time tracking app for freelancers, consultants, and solo professionals. Track billable hours, organize projects, and export invoice-ready timesheets.');
        $resolvedCanonical = url()->current();
        $resolvedImage = $metaImage ?? asset('assets/web-app-manifest-512x512.png');
    @endphp

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $resolvedMetaTitle }}</title>
    <meta name="description" content="{{ $resolvedMetaDescription }}">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="{{ $resolvedCanonical }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="SoloHours">
    <meta property="og:title" content="{{ $resolvedMetaTitle }}">
    <meta property="og:description" content="{{ $resolvedMetaDescription }}">
    <meta property="og:url" content="{{ $resolvedCanonical }}">
    <meta property="og:image" content="{{ $resolvedImage }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $resolvedMetaTitle }}">
    <meta name="twitter:description" content="{{ $resolvedMetaDescription }}">
    <meta name="twitter:image" content="{{ $resolvedImage }}">

    <link rel="icon" type="image/svg+xml" href="/assets/favicon.svg" />

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "SoftwareApplication",
            "name": "SoloHours",
            "applicationCategory": "BusinessApplication",
            "operatingSystem": "Web",
            "description": "{{ $resolvedMetaDescription }}",
            "url": "{{ config('app.url') }}",
            "offers": [
                {
                    "@type": "Offer",
                    "priceCurrency": "USD",
                    "availability": "https://schema.org/InStock"
                }
            ]
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.google-analytics')
</head>
<body class="flex min-h-screen flex-col bg-base-100 text-base-content" data-theme="light">
    <nav class="border-b border-base-200 bg-base-100/95 backdrop-blur">
        <div class="container mx-auto flex items-center justify-between px-4 py-3">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5" aria-label="SoloHours home">
                <img src="/assets/logo.svg" alt="SoloHours" class="h-9 w-9 shrink-0" />
                <span class="text-base font-semibold tracking-tight">SoloHours</span>
            </a>

            <div class="flex items-center gap-1.5 text-sm">
                <a href="{{ route('pricing') }}" class="btn btn-ghost btn-sm">Pricing</a>
                <a href="{{ route('guides') }}" class="btn btn-ghost btn-sm">Guides</a>
                <a href="{{ route('contact.show') }}" class="btn btn-ghost btn-sm">Contact</a>

                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Login</a>
                    <a href="{{ route('signup') }}" class="btn btn-primary btn-sm">Start Tracking Time</a>
                @endauth
            </div>
        </div>
    </nav>

    @php
        $flashToasts = [];

        if (session('success')) {
            $flashToasts[] = ['type' => 'success', 'message' => session('success')];
        }

        if (session('error')) {
            $flashToasts[] = ['type' => 'error', 'message' => session('error')];
        }

        if ($errors->any()) {
            foreach ($errors->all() as $errorMessage) {
                $flashToasts[] = ['type' => 'error', 'message' => $errorMessage];
            }
        }

        if (session('status')) {
            $statusMessage = session('status') === 'verification-link-sent'
                ? 'A fresh verification link has been sent to your email address.'
                : session('status');

            $flashToasts[] = ['type' => 'success', 'message' => $statusMessage];
        }
    @endphp

    @if($flashToasts)
        <div class="toast toast-top toast-end z-[70]" id="public-flash-toast-stack">
            @foreach($flashToasts as $toast)
                <div class="sh-toast {{ $toast['type'] === 'error' ? 'sh-toast-error' : 'sh-toast-success' }}" role="status" data-toast>
                    <span>{{ $toast['message'] }}</span>
                    <button type="button" class="sh-toast-close" aria-label="Dismiss" data-toast-close>✕</button>
                </div>
            @endforeach
        </div>
    @endif

    <main class="container mx-auto flex-1 px-4 py-8 md:py-10">
        {{ $slot }}
    </main>

    <footer class="border-t border-base-200">
        <div class="container mx-auto flex flex-wrap items-center justify-between gap-4 px-4 py-6 text-sm text-base-content/70">
            <span>© {{ date('Y') }} SoloHours</span>

            <div class="flex items-center gap-4">
                <a href="{{ route('pricing') }}" class="transition-opacity hover:opacity-100 opacity-80">Pricing</a>
                <a href="{{ route('contact.show') }}" class="transition-opacity hover:opacity-100 opacity-80">Contact</a>
                <a href="{{ route('guides') }}" class="transition-opacity hover:opacity-100 opacity-80">Guides</a>
                <a href="{{ route('terms') }}" class="transition-opacity hover:opacity-100 opacity-80">Terms</a>
                <a href="{{ route('privacy') }}" class="transition-opacity hover:opacity-100 opacity-80">Privacy</a>
            </div>
        </div>
    </footer>

    <script>
        (() => {
            document.querySelectorAll('[data-toast]').forEach((toast) => {
                const removeToast = () => {
                    toast.classList.add('opacity-0', 'translate-x-2');
                    window.setTimeout(() => toast.remove(), 180);
                };

                window.setTimeout(removeToast, 3800);
                toast.querySelector('[data-toast-close]')?.addEventListener('click', removeToast);
            });
        })();
    </script>
</body>
</html>
