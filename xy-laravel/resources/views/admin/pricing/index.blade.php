@extends('admin.layout', ['title' => 'Tarifs régionaux'])

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Gestion des tarifs régionaux</h2>
                <p class="text-gray-600">Configurez les prix par forfait et par région. Les prix affichés sont déjà réduits de 50%.</p>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                    @foreach($regions as $regionCode => $regionName)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $regionName }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($plans as $planId)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="font-medium text-gray-900">{{ ucfirst(str_replace('-', ' ', $planId)) }}</span>
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $planId === 'business' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $planId === 'business' ? 'Populaire' : '' }}
                                </span>
                            </div>
                        </td>
                        @foreach($regions as $regionCode => $regionName)
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $rp = $pricing[$planId][$regionCode] ?? null;
                                @endphp
                                @if($rp && $rp['price_monthly'])
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($rp['price_monthly'], 0, ',', ' ') }} FCFA</span>
                                    @if($rp['original_price_monthly'] && $rp['original_price_monthly'] > $rp['price_monthly'])
                                        <span class="text-xs text-gray-400 strike-through ml-1">{{ number_format($rp['original_price_monthly'], 0, ',', ' ') }} FCFA</span>
                                    @endif
                                    <span class="text-xs text-green-600 ml-1 font-bold">-{{ $rp['discount_percent'] }}%</span>
                                @else
                                    <span class="text-sm text-gray-400">Non configuré</span>
                                @endif
                                <button
                                    onclick="editPrice('{{ $planId }}', '{{ $regionCode }}')"
                                    class="mt-2 text-xs text-green-600 hover:underline px-2 py-1 bg-green-50 rounded"
                                >
                                    Modifier
                                </button>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 z-50 bg-black bg-opacity-50 hidden">
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Modifier le prix</h3>
                <form id="editPriceForm" class="space-y-4">
                    <input type="hidden" id="edit_plan_id">
                    <input type="hidden" id="edit_region_code">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom du plan</label>
                        <input type="text" id="edit_plan_name" readonly class="w-full px-3 py-2 bg-gray-100 border rounded text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Région</label>
                        <input type="text" id="edit_region_name" readonly class="w-full px-3 py-2 bg-gray-100 border rounded text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Prix mensuel (FCFA)</label>
                        <input type="number" id="edit_price_monthly" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm" min="0">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Prix annuel (FCFA)</label>
                        <input type="number" id="edit_price_yearly" class="w-full px-3 py-2 border border-gray-300 rounded text-sm" min="0">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Prix original mensuel (FCFA)</label>
                        <input type="number" id="edit_original_monthly" class="w-full px-3 py-2 border border-gray-300 rounded text-sm" min="0">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Remise (%)</label>
                        <input type="number" id="edit_discount" class="w-full px-3 py-2 border border-gray-300 rounded text-sm" min="0" max="100">
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Annuler</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white hover:bg-green-700 rounded">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function editPrice(planId, regionCode) {
        // Fetch current data and populate modal
        document.getElementById('edit_plan_id').value = planId;
        document.getElementById('edit_region_code').value = regionCode;
        document.getElementById('editModal').classList.remove('hidden');
    }
    
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
    
    document.getElementById('editPriceForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const planId = document.getElementById('edit_plan_id').value;
        const regionCode = document.getElementById('edit_region_code').value;
        
        const formData = {
            price_monthly: document.getElementById('edit_price_monthly').value,
            price_yearly: document.getElementById('edit_price_yearly').value,
            currency: 'XOF',
            original_price_monthly: document.getElementById('edit_original_monthly').value,
            original_price_yearly: document.getElementById('edit_price_yearly').value ? document.getElementById('edit_price_yearly').value * 2 : null,
            discount_percent: document.getElementById('edit_discount').value,
            is_active: true,
        };
        
        try {
            const response = await fetch(`/admin/pricing/${planId}/${regionCode}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(formData),
            });
            
            if (response.ok) {
                closeEditModal();
                location.reload();
            } else {
                alert('Erreur lors de la mise à jour');
            }
        } catch (err) {
            alert('Erreur de connexion');
        }
    });
</script>
@endsection
