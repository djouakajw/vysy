@extends('layouts.app', ['title' => 'Produits'])

@section('content')
<div class="min-h-screen bg-gray-50">
    <nav class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="/" class="gradient-bg text-white font-bold text-xl tracking-tight">XY</a>
            </div>
        </div>
    </nav>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl p-6 border border-gray-100">
            <h1 class="text-xl font-bold mb-6">Mes produits</h1>
            @if($products->count() > 0)
                <div class="space-y-3">
                    @foreach ($products as $product)
                        <div class="flex justify-between items-center p-3 border-b border-gray-50">
                            <div>
                                <p class="font-medium">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">{{ $product->price }} {{ $product->currency }}</p>
                            </div>
                            <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">Actif</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">Aucun produit pour le moment.</p>
            @endif
        </div>
    </div>
</div>
@endsection
