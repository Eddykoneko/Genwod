# Mettre Genwod v2 en ligne sur Hostinger (genwod.williamluong.fr)

Une fois installé, chaque `git push` sur la branche suivie met le site à jour tout seul :
GitHub lance les tests et, **seulement s'ils passent**, se connecte en SSH à Hostinger et exécute `bin/deployer`
(code, Composer, migrations, catalogue, assets, cache).

La v1 reste en ligne sur genwodcci.williamluong.fr : sa base est seulement **lue** pour reprendre les comptes et l'historique.
Les valeurs entre `<chevrons>` sont à remplacer.

## Installation en place (octobre 2026)

| Élément | Emplacement |
|---|---|
| Code de la v2 (dépôt git, `.env.local`, `vendor/`) | `~/apps/genwod-v2`, hors du web |
| Dossier du sous-domaine genwod.williamluong.fr | `~/domains/williamluong.fr/public_html/genwod` |
| Lien vers le seul dossier exposé | `public_html/genwod/app` → `~/apps/genwod-v2/public` |
| Ancien site (erreur 500), gardé de côté | `~/apps/genwod-ancien` |
| Base de données (MariaDB 11.8) | `u855251883_genwod2` |
| v1, seulement lue pour la reprise | `public_html/genwodcci` (base `u855251883_genwodcci`) |

Le `.htaccess` de `public_html/genwod` fait deux choses :

```apache
# PHP 8.4 pour ce site uniquement : les autres sites du compte gardent la version choisie dans hPanel (8.3).
AddHandler application/x-httpd-alt-php84___lsphp .php
RewriteEngine On
RewriteRule ^(?!app/)(.*)$ app/$1 [L]
```

En ligne de commande, `php` est en 8.3 : `bin/deployer` utilise de lui-même `/opt/alt/php84/usr/bin/php`, y compris pour Composer.
Pour lancer une commande Symfony à la main :

```bash
cd ~/apps/genwod-v2
/opt/alt/php84/usr/bin/php bin/console <commande>
```

## Refaire l'installation sur un autre site

1. **hPanel** : activer SSH et y ajouter sa clé ; créer une base MySQL et son utilisateur.
2. **Code** : `git clone -b refonte/v2 https://github.com/Eddykoneko/Genwod.git ~/apps/<site>`.
3. **Configuration** : `~/apps/<site>/.env.local` (droits 600) avec `APP_ENV=prod`, `APP_SECRET`, `DATABASE_URL`
   (`…?serverVersion=11.8.9-MariaDB&charset=utf8mb4`, un `@` ou `:` du mot de passe s'écrit `%40` / `%3A`),
   `MAILER_DSN`, `MAILER_FROM`, `DEFAULT_URI`.
4. **Installation** : `bash ~/apps/<site>/bin/deployer`.
5. **Web** : dans le dossier du sous-domaine, le `.htaccess` ci-dessus et `ln -s ~/apps/<site>/public app`.
6. **Données v1** (facultatif) : `app:import-v1 "<DATABASE_URL de la v1>" --simulation`, puis sans `--simulation` ; `app:admin <e-mail>`.

## Brancher le déploiement automatique (une fois)

Sur le Mac, une clé réservée à GitHub et les secrets du dépôt (la clé privée ne s'affiche pas) :

```bash
ssh-keygen -t ed25519 -C genwod-deploiement-github -f ~/.ssh/genwod_deploiement_github -N ""
gh secret set HOSTINGER_SSH_KEY --repo Eddykoneko/Genwod < ~/.ssh/genwod_deploiement_github
gh secret set HOSTINGER_HOST --repo Eddykoneko/Genwod --body "147.79.103.15"
gh secret set HOSTINGER_PORT --repo Eddykoneko/Genwod --body "65002"
gh secret set HOSTINGER_USER --repo Eddykoneko/Genwod --body "u855251883"
gh secret set HOSTINGER_PATH --repo Eddykoneko/Genwod --body "/home/u855251883/apps/genwod-v2"
ssh-keyscan -p 65002 147.79.103.15 2>/dev/null | gh secret set HOSTINGER_KNOWN_HOSTS --repo Eddykoneko/Genwod
cat ~/.ssh/genwod_deploiement_github.pub
```

hPanel → Avancé → Accès SSH → Clés SSH → **ajouter** la ligne affichée par la dernière commande.
Puis, sur le serveur, restreindre cette clé au seul déploiement en préfixant sa ligne dans `~/.ssh/authorized_keys` :

```
command="bash /home/u855251883/apps/genwod-v2/bin/deploiement-distant",no-pty,no-port-forwarding,no-agent-forwarding,no-X11-forwarding ssh-ed25519 … genwod-deploiement-github
```

Si hPanel réécrit ce fichier (ajout ou retrait d'une clé), la restriction disparaît mais le déploiement continue de fonctionner : il suffit de la remettre.

C'est tout : à chaque `git push` sur `refonte/v2`, l'onglet **Actions** du dépôt montre les tests puis « Déploiement Hostinger ».
Pour suivre `main` après la fusion de la v2 : Settings → Secrets and variables → Actions → **Variables** → `BRANCHE_DEPLOIEMENT` = `main`
(et `git checkout main` dans `~/apps/genwod-v2`).

## En cas de souci

```bash
tail -n 20 ~/apps/genwod-v2/var/log/deploiements.log      # historique des déploiements
tail -n 50 ~/apps/genwod-v2/var/log/prod-*.log            # erreurs de l'application (erreur 500)
```
