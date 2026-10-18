@extends('layouts.app', ['title' => 'Mes réseaux sociaux'])

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
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Mes réseaux sociaux</h1>
                <button onclick="document.getElementById('addSocialModal').classList.remove('hidden')" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition">
                    + Ajouter un réseau social
                </button>
            </div>

            <p class="text-gray-600 text-sm mb-6">
                Configurez les liens vers vos réseaux sociaux. Ils apparaîtront sur votre vitrine publique {{ $tenant->public_url }}.
            </p>

            @if(count($socialAccounts) > 0)
                <div class="space-y-3">
                    @foreach ($socialAccounts as $social)
                        <div class="flex justify-between items-center p-4 bg-white border border-gray-100 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-xl">
                                    @php
                                        $icons = ['meta' => '📘', 'instagram' => '📷', 'tiktok' => '🎵', 'whatsapp' => '💬', 'youtube' => '▶️', 'google_business' => '📍'];
                                        echo $icons[$social->platform] ?? '🔗';
                                    @endphp
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $social->account_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $social->platform }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($social->access_token)
                                    <a href="{{ $social->access_token }}" target="_blank" class="text-green-600 hover:text-green-700 text-sm font-medium">Ouvrir</a>
                                @endif
                                @if($social->refresh_token)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $social->refresh_token) }}" target="_blank" class="text-green-600 hover:text-green-700 text-sm font-medium">WhatsApp</a>
                                @endif
                                <span class="px-2 py-1 rounded-full text-xs font-bold 
                                    @if($social->is_active) bg-green-100 text-green-700 @else bg-gray-100 text-gray-500 @endif
                                ">
                                    @if($social->is_active) Actif @else Inactif @endif
                                </span>
                                <button onclick="document.getElementById('editSocial{{ $social->id }}').classList.remove('hidden')" class="text-xs text-gray-600 hover:text-gray-900">Modifier</button>
                                <form action="{{ route('tenant.social.destroy', $social->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:text-red-800">Supprimer</button>
                                </form>
                            </div>
                        </div>

                        <!-- Edit Modal -->
                        <div id="editSocial{{ $social->id }}" class="fixed inset-0 z-50 bg-black bg-opacity-50 hidden">
                            <div class="fixed inset-0 flex items-center justify-center p-4">
                                <div class="bg-white rounded-lg p-6 max-w-md w-full">
                                    <h3 class="text-lg font-bold text-gray-900 mb-4">Modifier le réseau social</h3>
                                    <form action="{{ route('tenant.social.update', $social->id) }}" method="POST" class="space-y-4">
                                        @csrf @method('PUT')
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Nom du compte</label>
                                            <input type="text" name="account_name" value="{{ old('account_name', $social->account_name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">URL ou numéro</label>
                                            <input type="text" name="url" value="{{ old('url', $social->access_token) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                            <p class="text-xs text-gray-500 mt-1">URL de la page ou numéro de téléphone</p>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="checkbox" name="is_active" value="1" {{ $social->is_active ? 'checked' : '' }} class="w-4 h-4 text-green-600 rounded border-gray-300">
                                            <label class="ml-2 text-sm text-gray-700">Actif sur le site public</label>
                                        </div>
                                        <div class="flex justify-end space-x-3 pt-4">
                                            <button type="button" onclick="document.getElementById('editSocial{{ $social->id }}').classList.add('hidden')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Annuler</button>
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
                    <p class="text-gray-500 text-lg">Aucun réseau social configuré.</p>
                    <p class="text-xs text-gray-400 mt-2">Ajoutez vos liens sociaux pour qu'ils apparaissent sur votre vitrine.</p>
                </div>
            @endif

            <!-- Add Modal -->
            <div id="addSocialModal" class="fixed inset-0 z-50 bg-black bg-opacity-50 hidden">
                <div class="fixed inset-0 flex items-center justify-center p-4">
                    <div class="bg-white rounded-lg p-6 max-w-md w-full">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Ajouter un réseau social</h3>
                        <form action="{{ route('tenant.social.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Plateforme</label>
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
                            <div>
                                <label class="block text-sm font-medium text-gray-700">URL ou numéro</label>
                                <input type="text" name="url" placeholder="https://facebook.com/moncompte ou +2376XXXXXXXX" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-green-600 rounded border-gray-300">
                                <label class="ml-2 text-sm text-gray-700">Actif sur le site public</label>
                            </div>
                            <div class="flex justify-end space-x-3 pt-4">
                                <button type="button" onclick="document.getElementById('addSocialModal').classList.add('hidden')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Annuler</button>
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white hover:bg-green-700 rounded">Ajouter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
