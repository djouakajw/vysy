<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }} - {{ config('app.name') }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Créez votre boutique en ligne en 2 minutes avec XY' }}">
    <meta property="og:title" content="{{ $ogTitle ?? config('app.name') }}">
    <meta property="og:description" content="{{ $ogDescription ?? 'Créez votre boutique en ligne en 2 minutes avec XY' }}">
    <meta property="og:type" content="website">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #00a86b 0%, #00d4aa 100%); }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900">

    @yield('content')

    @stack('scripts')

</body>
</html>
