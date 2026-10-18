@extends('layouts.app', ['title' => $seoTitle ?? $tenant->name])

@section('styles')
<meta name="description" content="{{ $seoDescription }}">
<meta name="keywords" content="{{ $seoKeywords }}">
<link rel="canonical" href="{{ $canonicalUrl }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $canonicalUrl }}">
@if($ogImage)
<meta property="og:image" content="{{ $ogImage }}">
@endif
<meta name="twitter:card" content="{{ $twitterCard ?? 'summary_large_image' }}">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">

@foreach($advertisingAccounts as $ad)
    @if($ad->platform === 'meta_pixel' && $ad->pixel_id)
    <meta property="fb:pixel_id" content="{{ $ad->pixel_id }}">
    <script>
      !function(f,b,e,v,n,t,s)
      {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};
      if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
      n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t,s)}(window, document,'script',
      'https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', '{{ $ad->pixel_id }}');
      fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
      src="https://www.facebook.com/tr?id={{ $ad->pixel_id }}&ev=PageView&noscript=1"
      /></noscript>
    @endif
    @if($ad->platform === 'google_ads' && $ad->conversion_id)
    <!-- Google Ads Conversion -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ad->conversion_id }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{{ $ad->conversion_id }}');
    </script>
    @endif
    @if($ad->platform === 'tiktok_ads' && $ad->pixel_id)
    <script>
      !function (w, d, t) {
        w = w || t; d = d || t; var s = d.createElement(t);
        s.src = 'https://www.tiktok.com/events.js';
        w.ttc = w.ttc || []; w.ttc.push(s);
        d.getElementsByTagName(t)[0].parentNode.appendChild(s);
      }(window, document, 'script');
      w.ttc && w.ttc.load('{{ $ad->pixel_id }}', {
        /* other parameters */
      });
      w.ttc && w.ttc.page();
    </script>
    @endif
@endforeach
@endsection

@section('content')
<div class="min-h-screen bg-white text-gray-900">
    <nav class="border-b bg-white/95 backdrop-blur sticky top-0 z-20 px-6 py-3">
        <div class="max-w-7xl mx-auto flex justify-between">
            <div class="flex items-center space-x-3">
                <span class="font-bold text-lg text-green-600">{{ $tenant->name }}</span>
            </div>
            <div class="flex items-center space-x-4">
                @foreach($socialAccounts as $social)
                    @php
                        $platforms = [
                            'meta' => 'https://facebook.com/',
                            'instagram' => 'https://instagram.com/',
                            'tiktok' => 'https://tiktok.com/@',
                            'whatsapp' => 'https://wa.me/',
                            'youtube' => 'https://youtube.com/@',
                            'google_business' => 'https://www.google.com/maps/search/?api=1&query=',
                        ];
                        $url = $social->access_token;
                        $icon = match($social->platform) {
                            'meta' => '📘',
                            'instagram' => '📷',
                            'tiktok' => '🎵',
                            'whatsapp' => '💬',
                            'youtube' => '▶️',
                            'google_business' => '📍',
                            default => '🔗',
                        };
                    @endphp
                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-gray-600 hover:text-green-600 flex items-center space-x-1">
                        <span>{{ $icon }}</span>
                        <span>{{ $social->account_name }}</span>
                    </a>
                @endforeach
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
