# Déployer Genwod v2 sur O2Switch (genwod.williamluong.fr)

La v1 reste en ligne sur genwodcci.williamluong.fr, on n'y touche pas : sa base est seulement **lue** pour reprendre les comptes et l'historique.
Les valeurs entre `<chevrons>` sont à remplacer. Chaque étape se vérifie avant de passer à la suivante.

## 1. Préparer l'hébergement (cPanel)

1. **Accès SSH** : outil « Autorisation SSH » (whitelist) → autoriser votre adresse IP actuelle.
2. **PHP 8.4** : « Sélectionner une version de PHP » → **8.4**, extensions `intl`, `pdo_mysql`, `mbstring`, `ctype`, `iconv` cochées.
3. **Base de données** : « Bases de données MySQL »
   - créer une base, ex. `<cpaneluser>_genwod2` ;
   - créer un utilisateur, ex. `<cpaneluser>_genwod2`, avec un mot de passe fort ;
   - l'ajouter à la base avec **tous les privilèges**.
4. **Version du serveur** : dans phpMyAdmin, noter la « Version du serveur » (ex. `10.11.6-MariaDB`).
5. **E-mail d'envoi** (vérification de compte, mot de passe oublié) : « Comptes de messagerie » → créer `no-reply@williamluong.fr`, puis noter le **serveur SMTP** et le port indiqués dans « Configurer le client de messagerie ».

## 2. Récupérer le code (SSH)

```bash
ssh <cpaneluser>@<serveur>.o2switch.net
php -v                                   # doit afficher PHP 8.4
cd ~
git clone -b refonte/v2 https://github.com/Eddykoneko/Genwod.git genwod-v2
cd genwod-v2
```

## 3. Configurer la production

Générer un secret :

```bash
php -r 'echo bin2hex(random_bytes(16)), PHP_EOL;'
```

Créer `~/genwod-v2/.env.local` (par exemple avec `nano .env.local`). Dans les URLs, un `@` ou un `:` du mot de passe s'écrit `%40` / `%3A` :

```dotenv
APP_ENV=prod
APP_SECRET=<secret généré>
DATABASE_URL="mysql://<utilisateur>:<mot de passe>@localhost:3306/<base>?serverVersion=<version, ex. 10.11.6-MariaDB>&charset=utf8mb4"
MAILER_DSN="smtp://no-reply%40williamluong.fr:<mot de passe>@<serveur SMTP>:465"
MAILER_FROM="Genwod <no-reply@williamluong.fr>"
DEFAULT_URI=https://genwod.williamluong.fr
EDITEUR_CONTACT=<votre adresse de contact>
```

## 4. Installer

```bash
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate -n
php bin/console app:catalogue:importer
php bin/console asset-map:compile
php bin/console cache:clear
```

`composer install` installe aussi les dépendances JavaScript (importmap). Le catalogue importe les mouvements et les 70 WODs de référence.

## 5. Reprendre les données de la v1

Les identifiants de la base v1 se trouvent dans le `.env.local` du site genwodcci (ligne `DATABASE_URL`). D'abord une **simulation**, qui n'écrit rien :

```bash
php bin/console app:import-v1 "<DATABASE_URL de la v1>" --simulation
```

Si le résumé est correct, lancer l'import réel :

```bash
php bin/console app:import-v1 "<DATABASE_URL de la v1>"
```

Les comptes gardent leur mot de passe. Pour donner les droits d'administration à votre compte :

```bash
php bin/console app:admin <votre e-mail>
```

## 6. Mettre en ligne

cPanel → « Domaines » → **genwod.williamluong.fr** → modifier la racine du document : `genwod-v2/public`.
L'ancien dossier du site reste en place : pour revenir en arrière, il suffit de remettre l'ancienne racine.
Le certificat HTTPS (AutoSSL) doit être actif sur le sous-domaine : le site redirige toujours vers `https://`.

## Mettre à jour plus tard

```bash
cd ~/genwod-v2
git pull
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate -n
php bin/console app:catalogue:importer
php bin/console asset-map:compile
php bin/console cache:clear
```

## En cas d'erreur 500

```bash
tail -n 50 ~/genwod-v2/var/log/prod-*.log
```
