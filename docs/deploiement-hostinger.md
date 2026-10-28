# Mettre Genwod v2 en ligne sur Hostinger (genwod.williamluong.fr)

Installation **une seule fois**, puis chaque `git push` sur la branche suivie met le site à jour tout seul :
GitHub lance les tests et, **seulement s'ils passent**, se connecte en SSH à Hostinger et exécute `bin/deployer`
(code, Composer, migrations, catalogue, assets, cache).

La v1 reste en ligne sur genwodcci.williamluong.fr : sa base est seulement **lue** pour reprendre les comptes et l'historique.
Les valeurs entre `<chevrons>` sont à remplacer.

## 1. Préparer l'hébergement (hPanel)

1. **SSH** : Avancé → Accès SSH → **Activer**. Noter l'**IP**, le **port** (65002) et l'**utilisateur** (`u123456789`).
2. **PHP 8.4** : Avancé → Configuration PHP → version **8.4** (extensions `intl`, `pdo_mysql` actives).
3. **Base de données** : Bases de données → Gestion → créer une base et son utilisateur (ex. `u123456789_genwod2`), noter le mot de passe.
   Dans phpMyAdmin, noter la **version du serveur** (ex. `10.11.10-MariaDB`).
4. **E-mail d'envoi** (vérification de compte, mot de passe oublié) : E-mails → créer `no-reply@williamluong.fr`.
   Serveur SMTP indiqué dans « Configurer les appareils » (en général `smtp.hostinger.com`, port 465).
5. **Dossier du site** : Domaines → Sous-domaines → repérer le **dossier** de `genwod.williamluong.fr`
   (ex. `/home/u123456789/domains/williamluong.fr/public_html/genwod`). C'est `<DOSSIER>` dans la suite.
6. **HTTPS** : Sécurité → SSL → vérifier que le certificat couvre `genwod.williamluong.fr` (le site force `https://`).

## 2. Installer le code (SSH, une fois)

```bash
ssh -p 65002 <utilisateur>@<IP>
php -v                                   # doit afficher PHP 8.4
mv <DOSSIER> <DOSSIER>-ancien            # l'ancien site cassé, gardé de côté
git clone -b refonte/v2 https://github.com/Eddykoneko/Genwod.git <DOSSIER>
cd <DOSSIER>
```

Le `.htaccess` à la racine du projet sert tout depuis `public/` : rien d'autre (`.env.local`, `vendor/`…) n'est accessible depuis le web.

## 3. Configurer la production

```bash
php -r 'echo bin2hex(random_bytes(16)), PHP_EOL;'      # génère APP_SECRET
nano .env.local
```

Dans les URLs, un `@` ou un `:` du mot de passe s'écrit `%40` / `%3A` :

```dotenv
APP_ENV=prod
APP_SECRET=<secret généré>
DATABASE_URL="mysql://<utilisateur base>:<mot de passe>@localhost:3306/<base>?serverVersion=<version, ex. 10.11.10-MariaDB>&charset=utf8mb4"
MAILER_DSN="smtp://no-reply%40williamluong.fr:<mot de passe>@smtp.hostinger.com:465"
MAILER_FROM="Genwod <no-reply@williamluong.fr>"
DEFAULT_URI=https://genwod.williamluong.fr
EDITEUR_CONTACT=<votre adresse de contact>
```

Puis le premier déploiement :

```bash
bash bin/deployer
```

## 4. Reprendre les données de la v1

Les identifiants de la base v1 sont dans le `.env.local` du site genwodcci :

```bash
grep DATABASE_URL <dossier de genwodcci>/.env.local
php bin/console app:import-v1 "<DATABASE_URL de la v1>" --simulation   # résumé, rien n'est écrit
php bin/console app:import-v1 "<DATABASE_URL de la v1>"                # import réel
php bin/console app:admin <votre e-mail>                               # droits d'administration
```

Les comptes gardent leur mot de passe. Le site est en ligne : https://genwod.williamluong.fr

## 5. Brancher le déploiement automatique (une fois)

Sur votre Mac, une clé SSH réservée au déploiement :

```bash
ssh-keygen -t ed25519 -C genwod-deploiement -f ~/.ssh/genwod_deploiement -N ""
cat ~/.ssh/genwod_deploiement.pub
```

hPanel → Avancé → Accès SSH → Clés SSH → **ajouter** le contenu affiché (la clé `.pub`).
Puis, toujours sur le Mac, les secrets GitHub (la clé privée ne s'affiche pas) :

```bash
gh secret set HOSTINGER_SSH_KEY --repo Eddykoneko/Genwod < ~/.ssh/genwod_deploiement
gh secret set HOSTINGER_HOST --repo Eddykoneko/Genwod --body "<IP>"
gh secret set HOSTINGER_PORT --repo Eddykoneko/Genwod --body "65002"
gh secret set HOSTINGER_USER --repo Eddykoneko/Genwod --body "<utilisateur>"
gh secret set HOSTINGER_PATH --repo Eddykoneko/Genwod --body "<DOSSIER>"
ssh-keyscan -p 65002 <IP> | gh secret set HOSTINGER_KNOWN_HOSTS --repo Eddykoneko/Genwod
```

C'est tout : au prochain `git push` sur `refonte/v2`, l'onglet **Actions** du dépôt montre les tests puis « Déploiement Hostinger ».
Pour suivre `main` après la fusion de la v2 : Settings → Secrets and variables → Actions → **Variables** → `BRANCHE_DEPLOIEMENT` = `main`
(et `git checkout main` dans `<DOSSIER>`).

## En cas de souci

```bash
tail -n 20 <DOSSIER>/var/log/deploiements.log      # historique des déploiements
tail -n 50 <DOSSIER>/var/log/prod-*.log            # erreurs de l'application (erreur 500)
```

Revenir à l'ancien site : `mv <DOSSIER> <DOSSIER>-v2 && mv <DOSSIER>-ancien <DOSSIER>`.
