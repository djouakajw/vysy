@extends('layouts.app', ['title' => 'Tableau de bord'])

@section('content')
<div class="min-h-screen bg-gray-50">
    <nav class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="/" class="gradient-bg text-white font-bold text-xl tracking-tight">XY</a>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">{{ $user->name ?? $tenant->user->name }}</span>
                    <a href="{{ route('tenant.site') }}" class="text-sm text-gray-600 hover:text-green-600">Voir mon site</a>
                    <form action="/auth/logout" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-700 font-medium text-sm">Déconnexion</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (!$tenant)
            <div class="bg-white rounded-xl p-8 border border-gray-100 text-center">
                <h2 class="text-xl font-bold mb-4">Bienvenue sur XY</h2>
                <p class="text-gray-600 mb-6">Vous n'avez pas encore de boutique. Créez votre première boutique en 2 minutes.</p>
                <a href="/" class="gradient-bg text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition">Créer ma boutique</a>
            </div>
        @else
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                <div class="bg-white p-4 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase font-bold">Visites</p>
                    <p class="text-2xl font-bold">{{ $stats['visits'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase font-bold">Clics WhatsApp</p>
                    <p class="text-2xl font-bold">{{ $stats['whatsapp_clicks'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase font-bold">Clics téléphone</p>
                    <p class="text-2xl font-bold">{{ $stats['phone_clicks'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase font-bold">Scans QR</p>
                    <p class="text-2xl font-bold">{{ $stats['qr_scans'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase font-bold">Contacts</p>
                    <p class="text-2xl font-bold">{{ $stats['contacts'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase font-bold">Produits</p>
                    <p class="text-2xl font-bold">{{ $stats['products'] }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recent contacts -->
                <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 p-6">
                    <h3 class="text-lg font-bold mb-4">Derniers contacts</h3>
                    <div class="space-y-3">
                        @foreach ($recentContacts as $contact)
                            <div class="flex justify-between items-center p-3 border-b border-gray-50 last:border-0">
                                <div>
                                    <p class="font-medium">{{ $contact->name ?? 'Anonyme' }}</p>
                                    <p class="text-xs text-gray-500">{{ $contact->source }} · {{ $contact->created_at->diffForHumans() }}</p>
                                </div>
                                <span class="px-2 py-1 rounded-full text-xs font-bold 
                                    @switch($contact->status)
                                        @case('new') bg-yellow-100 text-yellow-700 @break
                                        @case('contacted') bg-blue-100 text-blue-700 @break
                                        @case('in_discussion') bg-purple-100 text-purple-700 @break
                                        @case('converted') bg-green-100 text-green-700 @break
                                        @default bg-gray-100 text-gray-700 @endswitch
                                    ">
                                    {{ ucfirst($contact->status) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-4">Aucun contact pour le moment.</p>
                        @endforeach
                    </div>
                </div>

                <!-- Subscription & actions -->
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <h3 class="text-lg font-bold mb-4">Mon abonnement</h3>
                    @if ($subscription)
                        <div class="space-y-4">
                            <p class="font-bold">{{ ucfirst($subscription->plan) }}</p>
                            <p class="text-gray-600 text-sm">{{ $subscription->price }} {{ $subscription->currency }} / mois</p>
                            <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">Actif</span>
                            @if ($subscription->expires_at)
                                <p class="text-xs text-gray-500">Expire le {{ $subscription->expires_at->format('d/m/Y') }}</p>
                            @endif
                            <a href="/pricing" class="w-full py-2 rounded-lg bg-green-600 text-white text-center font-medium hover:bg-green-700 transition block">Renouveler mon abonnement</a>
                        </div>
                    @else
                        <div class="space-y-4">
                            <p class="text-gray-600 text-sm">Aucun abonnement actif. Votre site est en brouillon.</p>
                            <a href="/pricing" class="w-full py-2 rounded-lg bg-green-600 text-white text-center font-medium hover:bg-green-700 transition block">Souscrire à un abonnement</a>
                        </div>
                    @endif

                    <div class="mt-8 space-y-3">
                        <h3 class="text-lg font-bold">Actions rapides</h3>
                        <a href="{{ route('tenant.site.edit') }}" class="w-full py-2.5 rounded-lg border border-gray-200 text-left text-gray-700 font-medium hover:bg-gray-50 flex justify-between items-center">
                            Modifier mon site <span class="text-xs">→</span>
                        </a>
                        <a href="{{ route('tenant.products.index') }}" class="w-full py-2.5 rounded-lg border border-gray-200 text-left text-gray-700 font-medium hover:bg-gray-50 flex justify-between items-center">
                            Gérer mes produits <span class="text-xs">→</span>
                        </a>
                        <a href="{{ route('tenant.qrcodes.index') }}" class="w-full py-2.5 rounded-lg border border-gray-200 text-left text-gray-700 font-medium hover:bg-gray-50 flex justify-between items-center">
                            Gérer mes QR codes <span class="text-xs">→</span>
                        </a>
                        @if ($tenant->isPublished())
                            <a href="{{ $tenant->public_url }}" target="_blank" class="w-full py-2.5 rounded-lg bg-green-600 text-white text-center font-medium hover:bg-green-700 transition block">Voir mon site en ligne</a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
