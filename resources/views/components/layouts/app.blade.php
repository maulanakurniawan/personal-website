@props(['metaTitle' => null, 'metaDescription' => null, 'adminBackend' => false])

@include('layouts.app', [
    'slot' => $slot,
    'metaTitle' => $metaTitle,
    'metaDescription' => $metaDescription,
    'adminBackend' => $adminBackend,
])
