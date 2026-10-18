@extends('layouts.app', ['title' => 'Contacts'])

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
            <h1 class="text-xl font-bold mb-6">Mes contacts</h1>
            @if(isset($contacts) && $contacts->count() > 0)
                <div class="space-y-3">
                    @foreach ($contacts as $contact)
                        <div class="flex justify-between items-center p-3 border-b border-gray-50">
                            <div>
                                <p class="font-medium">{{ $contact->name ?? 'Anonyme' }}</p>
                                <p class="text-xs text-gray-500">{{ $contact->phone ?? $contact->email }} · {{ $contact->source }}</p>
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
                    @endforeach
                </div>
                {{ $contacts->links() }}
            @else
                <p class="text-gray-500 text-center py-8">Aucun contact pour le moment.</p>
            @endif
        </div>
    </div>
</div>
@endsection
