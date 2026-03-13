@props(['metaTitle' => null, 'metaDescription' => null, 'metaRobots' => null])

@include('layouts.public', [
    'slot' => $slot,
    'metaTitle' => $metaTitle,
    'metaDescription' => $metaDescription,
    'metaRobots' => $metaRobots,
])
