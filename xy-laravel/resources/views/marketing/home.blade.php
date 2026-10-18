<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XY - Créez votre boutique en 2 minutes</title>
    <meta name="description" content="Créez votre site professionnel avec WhatsApp, paiement en ligne, QR codes. Sans code, sans engagement.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #00a86b 0%, #00d4aa 100%); }
        .text-gradient { background: linear-gradient(135deg, #00a86b 0%, #00d4aa 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-white text-gray-900">

    <!-- Nav -->
    <nav class="bg-white/80 backdrop-blur sticky top-0 z-50 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/" class="gradient-bg text-white font-bold text-xl tracking-tight">XY</a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:items-center space-x-8">
                        <a href="#" class="text-gray-600 hover:text-green-600 font-medium">Fonctionnalités</a>
                        <a href="/pricing" class="text-gray-600 hover:text-green-600 font-medium">Tarifs</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="#" class="text-gray-600 hover:text-green-600 font-medium">Connexion</a>
                    <a href="#" class="gradient-bg text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-green-700 transition">Créer ma boutique</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="gradient-bg py-20 px-4 text-white text-center">
        <h1 class="text-4xl sm:text-6xl font-extrabold mb-6 leading-tight">
            Créez votre boutique <br><span class="block text-gradient">en 2 minutes</span>
        </h1>
        <p class="text-lg sm:text-xl text-green-100 mb-8 max-w-2xl mx-auto">
            Site pro, WhatsApp, QR codes, paiement en ligne. Pour commerçants, restaurants, services et associations en Afrique.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <input type="text" id="subdomain" placeholder="votre-boutique" class="px-4 py-3 rounded-lg text-gray-900 w-full sm:w-auto sm:max-w-md focus:ring-2 focus:ring-white outline-none">
            <span class="px-4 py-3 bg-white text-gray-900 rounded-lg font-mono font-bold">.xy-cm.com</span>
            <button onclick="checkSubdomain()" class="w-full sm:w-auto bg-white text-green-600 px-6 py-3 rounded-lg font-bold hover:bg-gray-100 transition">Tester</button>
        </div>
        <p id="subdomain-msg" class="mt-4 text-sm text-green-200 font-medium hidden"></p>
    </section>

    <!-- Features -->
    <section class="py-20 px-4 max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Tout ce dont vous avez besoin</h2>
            <p class="text-gray-600">Lancez votre activité en ligne sans compétences techniques</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center text-xl mb-4">⚡</div>
                <h3 class="text-xl font-bold mb-2">Création express</h3>
                <p class="text-gray-600 text-sm">Formulaire unique, IA pour générer vos textes, publiez en 2 minutes.</p>
            </div>
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center text-xl mb-4">📱</div>
                <h3 class="text-xl font-bold mb-2">WhatsApp & Contact</h3>
                <p class="text-gray-600 text-sm">Boutons WhatsApp, appel, email, lien externe. Vos clients vous contactent directement.</p>
            </div>
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center text-xl mb-4">📊</div>
                <h3 class="text-xl font-bold mb-2">QR Codes</h3>
                <p class="text-gray-600 text-sm">Générez des QR codes pour vos menus, flyers, affiches. Téléchargez en PNG ou SVG.</p>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-20 px-4 bg-gray-50 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Prêt à lancer votre boutique ?</h2>
        <p class="text-gray-600 mb-8">Rejoignez des centaines de commerçants en Afrique qui utilisent XY.</p>
        <a href="#" class="gradient-bg text-white px-8 py-3 rounded-xl font-bold text-lg hover:bg-green-700 transition shadow-lg shadow-green-200">Créer ma boutique gratuitement</a>
    </section>

    <footer class="bg-gray-900 text-white py-12 px-4 text-center">
        <div class="gradient-bg text-white font-bold text-xl mb-4">XY</div>
        <p class="text-gray-400 text-sm mb-6">© 2026 XY - Création de boutiques en ligne pour l'Afrique</p>
        <div class="flex justify-center space-x-6 text-sm">
            <a href="#" class="text-gray-400 hover:text-white">Conditions</a>
            <a href="#" class="text-gray-400 hover:text-white">Confidentialité</a>
            <a href="#" class="text-gray-400 hover:text-white">Contact</a>
        </div>
    </footer>

    <script>
        async function checkSubdomain() {
            const name = document.getElementById('subdomain').value;
            const msg = document.getElementById('subdomain-msg');
            if (!name) return;
            msg.classList.remove('hidden');
            try {
                const res = await fetch(`/api/subdomain/check?name=${encodeURIComponent(name)}`);
                const data = await res.json();
                if (data.available) {
                    msg.textContent = '✓ ' + data.message;
                    msg.className = 'mt-4 text-sm text-green-400 font-medium';
                } else {
                    msg.textContent = '✗ ' + data.message + (data.suggestions ? ' Suggestion : ' + data.suggestions[0] : '');
                    msg.className = 'mt-4 text-sm text-red-400 font-medium';
                }
            } catch (e) {
                msg.textContent = 'Erreur de vérification';
                msg.className = 'mt-4 text-sm text-red-400 font-medium';
            }
        }
    </script>
</body>
</html>
