<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarifs XY - Abonnements à partir de 2 000 FCFA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #00a86b 0%, #00d4aa 100%); }
        .text-gradient { background: linear-gradient(135deg, #00a86b 0%, #00d4aa 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-white text-gray-900">

    <nav class="bg-white/80 backdrop-blur sticky top-0 z-50 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/" class="gradient-bg text-white font-bold text-xl tracking-tight">XY</a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:items-center space-x-8">
                        <a href="/" class="text-gray-600 hover:text-green-600 font-medium">Accueil</a>
                        <a href="#" class="text-gray-600 hover:text-green-600 font-medium">Fonctionnalités</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="#" class="text-gray-600 hover:text-green-600 font-medium">Connexion</a>
                    <a href="#" class="gradient-bg text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-green-700 transition">Créer ma boutique</a>
                </div>
            </div>
        </div>
    </nav>

    <section class="py-20 px-4 text-center">
        <h1 class="text-4xl sm:text-5xl font-extrabold mb-6 text-gray-900">
            Choisissez votre forfait <span class="text-gradient">XY</span>
        </h1>
        <p class="text-lg text-gray-600 mb-12 max-w-2xl mx-auto">
            Abonnements mensuels ou annuels avec réductions. Paiement sécurisé via GeniusPay (Mobile Money, carte bancaire).
        </p>

        <div class="max-w-6xl mx-auto">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Gratuit -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <h3 class="text-xl font-bold text-gray-900">Gratuit</h3>
                    <div class="mt-2">
                        <span class="text-3xl font-bold text-gray-900">0 FCFA</span>
                        <span class="text-gray-500">/ mois</span>
                    </div>
                    <p class="text-gray-500 text-sm mt-3">Pour tester votre boutique</p>
                    <ul class="list-disc list-inside text-sm text-gray-600 mt-6 space-y-2">
                        <li>1 sous-domaine .xy-cm.com</li>
                        <li>Jusqu'à 5 produits</li>
                        <li>Bouton WhatsApp</li>
                        <li>QR code de base</li>
                        <li>Site en brouillon (pas de publication)</li>
                    </ul>
                    <button class="w-full mt-6 py-3 rounded-lg border border-gray-200 bg-white text-gray-700 font-semibold hover:bg-gray-50 transition">Commencer gratuitement</button>
                </div>

                <!-- Starter -->
                <div class="bg-white p-6 rounded-xl border-2 border-green-600 shadow-lg relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-green-600 text-white px-3 py-1 rounded-full text-xs font-bold uppercase">POPULAIRE</div>
                    <h3 class="text-xl font-bold text-gray-900">Starter</h3>
                    <div class="mt-2">
                        <span class="text-3xl font-bold text-green-600">2 000 FCFA</span>
                        <span class="text-gray-500">/ mois</span>
                    </div>
                    <p class="text-gray-500 text-sm mt-3">Pour les petits commerçants</p>
                    <ul class="list-disc list-inside text-sm text-gray-600 mt-6 space-y-2">
                        <li>1 sous-domaine personnalisé</li>
                        <li>Jusqu'à 50 produits</li>
                        <li>WhatsApp + Appel + Email</li>
                        <li>QR codes PNG/SVG</li>
                        <li>Statistiques basiques</li>
                        <li>Publication après paiement</li>
                    </ul>
                    <button class="w-full mt-6 py-3 rounded-lg bg-green-600 text-white font-bold hover:bg-green-700 transition shadow-lg shadow-green-200">Choisir Starter</button>
                </div>

                <!-- Business -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <h3 class="text-xl font-bold text-gray-900">Business</h3>
                    <div class="mt-2">
                        <span class="text-3xl font-bold text-gray-900">5 000 FCFA</span>
                        <span class="text-gray-500">/ mois</span>
                    </div>
                    <p class="text-gray-500 text-sm mt-3">Pour les boutiques en croissance</p>
                    <ul class="list-disc list-inside text-sm text-gray-600 mt-6 space-y-2">
                        <li>3 sous-domaines</li>
                        <li>Jusqu'à 500 produits</li>
                        <li>Lien externe personnalisé</li>
                        <li>Statistiques avancées</li>
                        <td>Marketing inclus</td>
                        <li>Support prioritaire</li>
                    </ul>
                    <button class="w-full mt-6 py-3 rounded-lg border border-gray-200 bg-white text-gray-700 font-semibold hover:bg-gray-50 transition">Choisir Business</button>
                </div>

                <!-- Premium -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <h3 class="text-xl font-bold text-gray-900">Premium</h3>
                    <div class="mt-2">
                        <span class="text-3xl font-bold text-gray-900">10 000 FCFA</span>
                        <span class="text-gray-500">/ mois</span>
                    </div>
                    <p class="text-gray-500 text-sm mt-3">Pour les grandes activités</p>
                    <ul class="list-disc list-inside text-sm text-gray-600 mt-6 space-y-2">
                        <li>10 sous-domaines</li>
                        <li>Produits illimités</li>
                        <li>IA intégrée</td>
                        <li>Campagnes marketing</li>
                        <li>Domaine personnalisé</li>
                        <li>Support 24/7</li>
                    </ul>
                    <button class="w-full mt-6 py-3 rounded-lg border border-gray-200 bg-white text-gray-700 font-semibold hover:bg-gray-50 transition">Choisir Premium</button>
                </div>
            </div>
        </div>

        <!-- Annual discounts -->
        <div class="max-w-2xl mx-auto mt-16 bg-green-50 rounded-xl p-6 border border-green-100">
            <h3 class="text-lg font-bold text-green-800 mb-4">💡 Économisez avec l'abonnement annuel</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div class="bg-white p-3 rounded-lg">
                    <p class="font-bold text-gray-900">Starter</p>
                    <p class="text-green-600 text-xs">-17% (2 mois offerts)</p>
                </div>
                <div class="bg-white p-3 rounded-lg">
                    <p class="font-bold text-gray-900">Business</p>
                    <p class="text-green-600 text-xs">-20% (2 mois offerts)</p>
                </div>
                <div class="bg-white p-3 rounded-lg">
                    <p class="font-bold text-gray-900">Premium</p>
                    <p class="text-green-600 text-xs">-25% (3 mois offerts)</p>
                </div>
            </div>
        </div>
    </section>

</body>
</html>
