@extends('layouts.app', ['title' => $tenant->name])

@section('content')
<div class="min-h-screen bg-white text-gray-900">
    <nav class="border-b bg-white/95 backdrop-blur sticky top-0 z-20 px-6 py-3">
        <div class="max-w-7xl mx-auto flex justify-between">
            <div class="flex items-center space-x-3">
                <span class="font-bold text-lg text-green-600">{{ $tenant->name }}</span>
            </div>
            <div>
                <a href="{{ $tenant->public_url }}" class="text-sm text-gray-600 hover:text-gray-900">{{ $tenant->public_url }}</a>
            </div>
        </div>
    </nav>
    <div class="max-w-5xl mx-auto py-10 px-6">
        <header class="text-center mb-10">
            <h1 class="text-3xl font-bold mb-2">{{ $tenant->name }}</h1>
            <p class="text-gray-600">{{ $tenant->description ?? 'Découvrez notre offre.' }}</p>
        </header>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <section class="space-y-6">
                <h2 class="text-xl font-bold mb-2">Notre offre</h2>
                @if(isset($products) && $products->count() > 0)
                    @foreach ($products as $product)
                        <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                            <h3 class="font-semibold mb-1">{{ $product->name }}</h3>
                            @if($product->price)
                                <p class="text-lg font-bold text-green-700">{{ $product->price }} {{ $product->currency }}</p>
                            @endif
                            @if($product->description)
                                <p class="text-gray-700 mt-2">{{ $product->description }}</p>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                        <p class="text-gray-600 text-sm">Aucun produit ajouté pour le moment.</p>
                    </div>
                @endif
                <div class="flex gap-3">
                    @if(isset($deliveryModes) && is_array($deliveryModes))
                        @foreach ($deliveryModes as $mode)
                            <span class="px-3 py-1 rounded-full border border-green-200 text-green-700 text-xs font-medium">{{ ucfirst($mode) }}</span>
                        @endforeach
                    @endif
                </div>
            </section>
            <section class="bg-green-600 rounded-2xl p-8 text-white flex flex-col justify-center min-h-[300px]">
                <h2 class="text-xl font-bold mb-4">Passez à l'action</h2>
                @if($primaryAction == 'whatsapp' && $actionValue)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $actionValue) }}" class="w-full bg-white text-green-700 px-6 py-4 rounded-xl font-bold text-center hover:bg-green-50 transition text-lg">Contacter par WhatsApp</a>
                @elseif($primaryAction == 'call' && $actionValue)
                    <a href="tel:{{ $actionValue }}" class="w-full bg-white text-green-700 px-6 py-4 rounded-xl font-bold text-center hover:bg-green-50 transition text-lg">Appeler</a>
                @elseif($primaryAction == 'external_link' && $actionValue)
                    <a href="{{ $actionValue }}" target="_blank" class="w-full bg-white text-green-700 px-6 py-4 rounded-xl font-bold text-center hover:bg-green-50 transition text-lg">Visiter le lien</a>
                @else
                    <button class="w-full bg-white text-green-700 px-6 py-4 rounded-xl font-bold text-center hover:bg-green-50 transition text-lg">Prendre contact</button>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
