@extends('layouts.app', ['title' => 'Publicité et suivi des campagnes'])

@section('content')
<div class="min-h-screen bg-gray-50">
    <nav class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="/" class="gradient-bg text-white font-bold text-xl tracking-tight">XY</a>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('tenant.dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">Tableau de bord</a>
                    <a href="{{ route('tenant.site.edit') }}" class="text-sm text-gray-600 hover:text-gray-900">Mon site</a>
                </div>
            </div>
        </div>
    </nav>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl p-6 border border-gray-100">
            @if (!$canAccessAds)
                <div class="p-6 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-6 0 3 3 0 01-6 0 8 8 0 116 0z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-bold text-gray-900">Fonctionnalité réservée aux plans Premium</h3>
                            <p class="text-sm text-gray-700 mt-1">
                                Les identifiants publicitaires et le suivi des campagnes sont accessibles uniquement aux abonnements Prime (5 000 FCFA/mois) et Panneau (7 000 FCFA/mois).
                            </p>
                            <a href="/pricing" class="mt-4 inline-block bg-green-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-green-700 transition">
                                Passer à un plan supérieur
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Publicité et suivi des campagnes</h1>
                    <button onclick="document.getElementById('addAdModal').classList.remove('hidden')" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition">
                        + Ajouter un compte publicitaire
                    </button>
                </div>

                <p class="text-gray-600 text-sm mb-6">
                    Configurez vos identifiants publicitaires (Meta Pixel, Google Ads, TikTok Ads) pour suivre les performances de vos campagnes sur votre site {{ $tenant->public_url }}.
                </p>

                @if(count($advertisingAccounts) > 0)
                    <div class="space-y-3">
                        @foreach ($advertisingAccounts as $ad)
                            <div class="flex justify-between items-center p-4 bg-white border border-gray-100 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl">
                                        @php
                                            $icons = ['meta_ads' => '📘', 'google_ads' => '🔍', 'tiktok_ads' => '🎵', 'meta_pixel' => '📊'];
                                            echo $icons[$ad->platform] ?? '📊';
                                        @endphp
                                    </span>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $ad->account_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $ad->platform }}</p>
                                        @if($ad->pixel_id)
                                            <p class="text-xs text-gray-500">Pixel: {{ $ad->pixel_id }}</p>
                                        @endif
                                        @if($ad->conversion_id)
                                            <p class="text-xs text-gray-500">Conversion: {{ $ad->conversion_id }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold 
                                        @if($ad->is_active) bg-green-100 text-green-700 @else bg-gray-100 text-gray-500 @endif
                                    ">
                                        @if($ad->is_active) Actif @else Inactif @endif
                                    </span>
                                    <button onclick="document.getElementById('editAd{{ $ad->id }}').classList.remove('hidden')" class="text-xs text-gray-600 hover:text-gray-900">Modifier</button>
                                    <form action="{{ route('tenant.advertising.destroy', $advertisingAccount->id ?? $ad->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:text-red-800">Supprimer</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Edit Modal -->
                            <div id="editAd{{ $ad->id }}" class="fixed inset-0 z-50 bg-black bg-opacity-50 hidden">
                                <div class="fixed inset-0 flex items-center justify-center p-4">
                                    <div class="bg-white rounded-lg p-6 max-w-md w-full">
                                        <h3 class="text-lg font-bold text-gray-900 mb-4">Modifier le compte publicitaire</h3>
                                        <form action="{{ route('tenant.advertising.update', $ad->id) }}" method="POST" class="space-y-4">
                                            @csrf @method('PUT')
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Nom du compte</label>
                                                <input type="text" name="account_name" value="{{ old('account_name', $ad->account_name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Pixel ID</label>
                                                    <input type="text" name="pixel_id" value="{{ old('pixel_id', $ad->pixel_id) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                                    <p class="text-xs text-gray-500 mt-1">ID du pixel (ex: 123456789)</p>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Conversion ID</label>
                                                    <input type="text" name="conversion_id" value="{{ old('conversion_id', $ad->conversion_id) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                                    <p class="text-xs text-gray-500 mt-1">ID de conversion Google Ads</p>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Token d'accès</label>
                                                <input type="text" name="access_token" value="{{ old('access_token', $ad->access_token) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                                <p class="text-xs text-gray-500 mt-1">Token d'accès API (optionnel)</p>
                                            </div>
                                            <div class="flex items-center">
                                                <input type="checkbox" name="is_active" value="1" {{ $ad->is_active ? 'checked' : '' }} class="w-4 h-4 text-green-600 rounded border-gray-300">
                                                <label class="ml-2 text-sm text-gray-700">Actif sur le site public</label>
                                            </div>
                                            <div class="flex justify-end space-x-3 pt-4">
                                                <button type="button" onclick="document.getElementById('editAd{{ $ad->id }}').classList.add('hidden')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Annuler</button>
                                                <button type="submit" class="px-4 py-2 bg-green-600 text-white hover:bg-green-700 rounded">Enregistrer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 bg-white rounded-xl border border-gray-100">
                        <p class="text-gray-500 text-lg">Aucun compte publicitaire configuré.</p>
                        <p class="text-xs text-gray-400 mt-2">Ajoutez vos identifiants publicitaires pour suivre vos campagnes.</p>
                    </div>
                @endif

                <!-- Add Modal -->
                <div id="addAdModal" class="fixed inset-0 z-50 bg-black bg-opacity-50 hidden">
                    <div class="fixed inset-0 flex items-center justify-center p-4">
                        <div class="bg-white rounded-lg p-6 max-w-md w-full">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Ajouter un compte publicitaire</h3>
                            <form action="{{ route('tenant.advertising.store') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Plateforme publicitaire</label>
                                    <select name="platform" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                        @foreach($platforms as $code => $name)
                                            <option value="{{ $code }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nom du compte</label>
                                    <input type="text" name="account_name" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Pixel ID</label>
                                        <input type="text" name="pixel_id" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Conversion ID</label>
                                        <input type="text" name="conversion_id" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Token d'accès</label>
                                    <input type="text" name="access_token" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                    <p class="text-xs text-gray-500 mt-1">Token d'accès API (optionnel)</p>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-green-600 rounded border-gray-300">
                                    <label class="ml-2 text-sm text-gray-700">Actif sur le site public</label>
                                </div>
                                <div class="flex justify-end space-x-3 pt-4">
                                    <button type="button" onclick="document.getElementById('addAdModal').classList.add('hidden')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Annuler</button>
                                    <button type="submit" class="px-4 py-2 bg-green-600 text-white hover:bg-green-700 rounded">Ajouter</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
