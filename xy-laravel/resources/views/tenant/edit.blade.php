@extends('layouts.app', ['title' => 'Mon site'])

@section('content')
<div class="min-h-screen bg-gray-50">
    <nav class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="/" class="gradient-bg text-white font-bold text-xl tracking-tight">XY</a>
            </div>
        </div>
    </nav>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl p-6 border border-gray-100">
            <h1 class="text-xl font-bold mb-4">Éditer mon site</h1>
            <p class="text-gray-600">Fonctionnalité en développement. Vous pourrez modifier l'identité, les couleurs, les produits et les informations de contact depuis cette interface.</p>
        </div>
    </div>
</div>
@endsection
