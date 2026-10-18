@extends('admin.layout', ['title' => 'Réseaux sociaux'])

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Gestion des réseaux sociaux</h2>
            <p class="text-gray-600">Toutes les connexions sociales des boutiques XY</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Boutique</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plateforme</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Compte</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($socialAccounts as $social)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $social->tenant->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $social->tenant->slug }}.xy-cm.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                @if($social->platform === 'meta') bg-blue-100 text-blue-600
                                @elseif($social->platform === 'instagram') bg-purple-100 text-purple-600
                                @elseif($social->platform === 'tiktok') bg-black-100 text-black
                                @elseif($social->platform === 'whatsapp') bg-green-100 text-green-600
                                @elseif($social->platform === 'google_business') bg-red-100 text-red-600
                                @else bg-gray-100 text-gray-600 @endif
                            >
                                {{ $social->account_name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $social->account_id }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-500">{{ $social->tenant->plan ?? 'Non défini' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex items-center leading-5 rounded-full 
                                @if($social->is_active)
                                    bg-green-100 text-green-800
                                @else
                                    bg-red-100 text-red-800
                                @endif
                            >
                                <span class="w-2 h-2 mr-1 rounded-full 
                                    @if($social->is_active) bg-green-500 @else bg-red-500 @endif"></span>
                                @if($social->is_active) Actif @else Inactif @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="toggleSocial({{ $social->id }})" class="text-green-600 hover:text-green-800">
                                {{ $social->is_active ? 'Désactiver' : 'Activer' }}
                            </button>
                            <form action="{{ route('admin.social.destroy', $social->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 ml-4">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">Aucun compte social configuré.</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        {{ $socialAccounts->links() }}
    </div>
</div>

<script>
    function toggleSocial(id) {
        fetch(`/admin/social/${id}/toggle`, {
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
