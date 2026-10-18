# 🚀 Déploiement de XY sur Hostinger

Ce guide vous explique comment déployer le projet XY (Laravel 11) sur votre hébergement Hostinger (PHP 8.3 + MariaDB).

---

## 📋 Prérequis

- Accès SSH/SFTP à votre hébergement Hostinger
- Base de données MariaDB créée via hPanel
- Accès DNS pour configurer le sous-domaine wildcard `*.xy-cm.com`
- Compte GeniusPay avec clés API (pour les abonnements payants)

---

## 1. Upload du code

Transférez l'archive `xy-laravel.tar.gz` sur votre hébergement via SSH/SFTP :

```bash
# Se connecter en SSH
ssh utilisateur@votre-serveur.hostinger.com

# Aller dans le répertoire de votre site
cd /home/utilisateur/nom_du_site

# Décompresser l'archive
tar xzf xy-laravel.tar.gz
cd xy-laravel
```

---

## 2. Installation des dépendances

```bash
composer install --no-dev --optimize-autoloader
```

---

## 3. Configuration de l'environnement

Copiez le fichier d'exemple et éditez-le :

```bash
cp .env.example .env
nano .env
```

Remplissez les variables suivantes :

```env
APP_NAME=XY
APP_ENV=production
APP_DEBUG=false
APP_URL=https://xy-cm.com
APP_DOMAIN=xy-cm.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=votre_base_de_donnees
DB_USERNAME=votre_utilisateur_db
DB_PASSWORD=votre_mot_de_passe_db

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=votre_email@xy-cm.com
MAIL_PASSWORD=votre_mot_de_passe_email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=votre_email@xy-cm.com
MAIL_FROM_NAME="XY"

# ⚠️ CLÉS GENIUSPAY - À OBTENIR CHEZ GENIUSPAY
GENIUSPAY_BASE_URL=https://api.geniuspay.com
GENIUSPAY_API_KEY=votre_cle_api_geniuspay
GENIUSPAY_SECRET_KEY=votre_cle_secrete_geniuspay
GENIUSPAY_WEBHOOK_SECRET=votre_webhook_secret_geniuspay
GENIUSPAY_TIMEOUT=30
```

> **⚠️ Sécurité** : Ne partagez jamais votre fichier `.env`. Les clés GeniusPay sont sensibles.

---

## 4. Génération de la clé d'application

```bash
php artisan key:generate
```

---

## 5. Migration de la base de données

```bash
php artisan migrate --force
```

Cela créera toutes les tables nécessaires (utilisateurs, boutiques, produits, abonnements, paiements, contacts, QR codes, pages, réseaux sociaux, publicité, tarifs régionaux, etc.).

---

## 6. Lien du stockage public

```bash
php artisan storage:link
```

Cela crée le lien symbolique pour les fichiers uploadés (images de produits, QR codes, etc.).

---

## 7. Configuration du DNS

Dans l'interface DNS de votre domaine `xy-cm.com` (hPanel ou votre registraire) :

| Type | Nom | Valeur | TTL |
|------|-----|--------|-----|
| A | * | IP_de_votre_serveur | 3600 |
| A | @ | IP_de_votre_serveur | 3600 |
| CNAME | www | @ | 3600 |

Le sous-domaine wildcard `*.xy-cm.com` permettra à chaque boutique d'avoir son adresse (ex: `monboutique.xy-cm.com`).

---

## 8. Configuration SSL

Assurez-vous que le certificat SSL est activé pour `xy-cm.com` et tous les sous-domaines via l'interface hPanel ou Let's Encrypt.

---

## 9. Premier utilisateur administrateur

Créez votre premier compte administrateur via tinker :

```bash
php artisan tinker
```

Puis dans le console PHP :

```php
$user = \App\Models\User::create([
    'name' => 'Administrateur',
    'email' => 'admin@xy-cm.com',
    'password' => bcrypt('votre_mot_de_passe_securise'),
    'is_admin' => true,
]);
exit;
```

---

## 10. Vérification du déploiement

Accédez à `https://xy-cm.com` dans votre navigateur. Vous devriez voir la page d'accueil de XY.

Créez un compte, testez le parcours complet :
1. Testez un sous-domaine (ex: `testboutique.xy-cm.com`)
2. Créez un compte
3. Choisissez un type d'activité
4. Choisissez un sous-domaine
5. Ajoutez un produit
6. Choisissez un abonnement et payez via GeniusPay
7. Votre site est publié !

---

## 📁 Structure des dossiers importants

| Dossier | Rôle |
|---------|------|
| `app/` | Code PHP (modèles, contrôleurs, services) |
| `config/` | Configuration (app, geniuspay) |
| `database/migrations/` | Migrations de la base de données |
| `resources/views/` | Vues Blade (HTML) |
| `routes/` | Routes web et API |
| `public/` | Fichiers publics (index.php, assets) |
| `storage/` | Stockage des fichiers uploadés |

---

## 🔧 Commandes utiles

```bash
# Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Voir les logs en temps réel
tail -f storage/logs/laravel.log

# Relancer les migrations en cas de problème
php artisan migrate:refresh --seed

# Mettre à jour le code
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
```

---

## 🆘 Dépannage

| Problème | Solution |
|----------|----------|
| Erreur 500 | Vérifiez `storage/logs/laravel.log` |
| Sous-domaine ne résout pas | Vérifiez le DNS wildcard `*.xy-cm.com` |
| Paiement GeniusPay échoue | Vérifiez les clés API dans `.env` |
| Images ne s'affichent pas | Lancez `php artisan storage:link` |
| Erreur de base de données | Vérifiez les identifiants DB dans `.env` |

---

## 📞 Support

Pour toute question sur le déploiement, contactez le support XY.

---

© 2026 XY - Plateforme de création de sites pour l'Afrique
