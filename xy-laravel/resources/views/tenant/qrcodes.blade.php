@extends('layouts.app', ['title' => 'QR Codes'])

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
            <h1 class="text-xl font-bold mb-6">Mes QR Codes</h1>
            <p class="text-gray-600">Générez des QR codes pour vos menus, flyers, affiches. Téléchargez en PNG ou SVG.</p>
            @if(isset($qrcodes) && $qrcodes->count() > 0)
                <div class="space-y-3 mt-4">
                    @foreach ($qrcodes as $qr)
                        <div class="flex justify-between items-center p-3 border-b border-gray-50">
                            <div>
                                <p class="font-medium">{{ $qr->name }}</p>
                                <p class="text-xs text-gray-500">{{ $qr->scans_count }} scans</p>
                            </div>
                            <a href="{{ route('tenant.qrcodes.download', $qr->id) }}" class="text-green-600 hover:text-green-700 font-medium text-sm">Télécharger</a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">Aucun QR code pour le moment.</p>
            @endif
        </div>
    </div>
</div>
@endsection
