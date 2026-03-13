@php
    $googleAnalyticsMeasurementId = trim((string) config('services.google_analytics.measurement_id', ''));
    $googleAnalyticsHost = request()->getHost();
    $googleAnalyticsLocalHosts = ['localhost', '127.0.0.1', '::1'];
    $googleAnalyticsIsLocalhost = in_array($googleAnalyticsHost, $googleAnalyticsLocalHosts, true);
    $googleAnalyticsIsAdminUser = (bool) auth()->user()?->is_admin;
    $googleAnalyticsEnabled = $googleAnalyticsMeasurementId !== ''
        && ! $googleAnalyticsIsLocalhost
        && ! $googleAnalyticsIsAdminUser;
@endphp

@if ($googleAnalyticsEnabled)
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $googleAnalyticsMeasurementId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', '{{ $googleAnalyticsMeasurementId }}');
    </script>
@endif
