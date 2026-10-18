@extends('admin.layout', ['title' => 'Comptes publicitaires'])

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Comptes publicitaires des vendeurs</h2>
            <p class="text-gray-600">Gestion des identifiants publicitaires (Meta Ads, Google Ads, TikTok Ads, Meta Pixel) réservée aux plans Premium (5 000 FCFA et +).</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Boutique</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plateforme</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Compte</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pixel ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($advertisingAccounts as $ad)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $ad->tenant->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $ad->tenant->slug }}.xy-cm.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                @if($ad->platform === 'meta_ads') bg-blue-100 text-blue-600
                                @elseif($ad->platform === 'google_ads') bg-red-100 text-red-600
                                @elseif($ad->platform === 'tiktok_ads') bg-black-100 text-black
                                @elseif($ad->platform === 'meta_pixel') bg-purple-100 text-purple-600
                                @else bg-gray-100 text-gray-600 @endif
                            >
                                {{ $ad->platform }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">{{ $ad->account_name }}</span>
                            <p class="text-xs text-gray-500">{{ $ad->account_id }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $ad->pixel_id ?? '-' }}</span>
                            @if($ad->conversion_id)
                                <span class="text-xs text-gray-500 block">Conv: {{ $ad->conversion_id }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-500">{{ $ad->tenant->plan ?? 'Non défini' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex items-center leading-5 rounded-full 
                                @if($ad->is_active)
                                    bg-green-100 text-green-800
                                @else
                                    bg-red-100 text-red-800
                                @endif
                            >
                                <span class="w-2 h-2 mr-1 rounded-full 
                                    @if($ad->is_active) bg-green-500 @else bg-red-500 @endif"></span>
                                @if($ad->is_active) Actif @else Inactif @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="toggleAd({{ $ad->id }})" class="text-green-600 hover:text-green-800">
                                {{ $ad->is_active ? 'Désactiver' : 'Activer' }}
                            </button>
                            <form action="{{ route('admin.advertising.destroy', $ad->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 ml-4">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">Aucun compte publicitaire configuré.</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        {{ $advertisingAccounts->links() }}
    </div>
</div>

<script>
    function toggleAd(id) {
        fetch(`/admin/advertising/${id}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
</script>
@endsection
