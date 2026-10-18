<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Administration' }} - XY Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #00a86b 0%, #00d4aa 100%); }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900">

    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200">
        <div class="p-6">
            <div class="gradient-bg text-white px-4 py-3 rounded-lg font-bold text-xl mb-8">XY Admin</div>
            <nav class="space-y-2">
                <a href="{{ route('admin.pricing.index') }}" class="flex items-center px-3 py-2 text-gray-700 hover:bg-green-50 hover:text-green-700 rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Tarifs régionaux
                </a>
                <a href="{{ route('admin.pricing.stats') }}" class="flex items-center px-3 py-2 text-gray-700 hover:bg-green-50 hover:text-green-700 rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Statistiques
                </a>
                <a href="{{ route('admin.social.index') }}" class="flex items-center px-3 py-2 text-gray-700 hover:bg-green-50 hover:text-green-700 rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Réseaux sociaux
                </a>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="ml-64">
        <header class="bg-white border-b border-gray-200">
            <div class="px-6 py-4">
                <h1 class="text-2xl font-bold text-gray-900">{{ $title ?? 'Administration' }}</h1>
            </div>
        </header>

        <main class="p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
