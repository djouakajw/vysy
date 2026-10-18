@extends('layouts.app', ['title' => 'Abonnement'])

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
            <h1 class="text-xl font-bold mb-6">Mon abonnement</h1>
            @if(isset($subscription))
                <div class="p-4 bg-green-50 rounded-lg border border-green-100">
                    <p class="font-bold text-green-800">Abonnement {{ ucfirst($subscription->plan) }}</p>
                    <p class="text-green-700">{{ $subscription->price }} {{ $subscription->currency }} / mois</p>
                    @if($subscription->expires_at)
                        <p class="text-xs text-green-600 mt-2">Expire le {{ $subscription->expires_at->format('d/m/Y') }}</p>
                    @endif
                </div>
            @else
                <p class="text-gray-600">Aucun abonnement actif.</p>
                <a href="/pricing" class="mt-4 inline-block gradient-bg text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-green-700 transition">Souscrire</a>
            @endif
            @if(isset($payments) && $payments->count() > 0)
                <h2 class="text-lg font-bold mb-4 mt-8">Historique des paiements</h2>
                <div class="space-y-3">
                    @foreach ($payments as $payment)
                        <div class="flex justify-between items-center p-3 border-b border-gray-50">
                            <div>
                                <p class="font-medium">{{ $payment->amount }} {{ $payment->currency }}</p>
                                <p class="text-xs text-gray-500">{{ $payment->created_at->format('d/m/Y') }}</p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-bold 
                                @switch($payment->status)
                                    @case('confirmed') bg-green-100 text-green-700 @break
                                    @case('pending') bg-yellow-100 text-yellow-700 @break
                                    @case('failed') bg-red-100 text-red-700 @break
                                    @default bg-gray-100 text-gray-700 @endswitch
                            ">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
