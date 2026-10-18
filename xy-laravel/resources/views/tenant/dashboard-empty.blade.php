@extends('layouts.app', ['title' => 'Tableau de bord'])

@section('content')
<div class="min-h-screen bg-gray-50">
    <nav class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="/" class="gradient-bg text-white font-bold text-xl tracking-tight">XY</a>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">{{ $user->name }}</span>
                </div>
            </div>
        </div>
    </nav>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl p-8 border border-gray-100 text-center">
            <h2 class="text-xl font-bold mb-4">Bienvenue sur XY</h2>
            <p class="text-gray-600 mb-6">Créez votre première boutique en 2 minutes.</p>
            <a href="/" class="gradient-bg text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition">Créer ma boutique</a>
        </div>
    </div>
</div>
@endsection
