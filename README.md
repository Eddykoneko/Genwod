# Genwod v2 — générateur de WODs CrossFit

Genwod génère des entraînements CrossFit (*Workout Of the Day*) adaptés au format, à la durée, à la difficulté et au **matériel disponible**. Un chrono plein écran accompagne la séance, chaque résultat est enregistré, et des classements entretiennent la motivation.

> Projet présenté pour le titre professionnel **DWWM**, puis entièrement remodelé.
> La version d'origine (Symfony 7.0) reste intacte sur la branche `main` ; cette refonte vit sur `refonte/v2`.

## Fonctionnalités

| | |
|---|---|
| 🎲 **Générateur** | For Time, AMRAP, EMOM, Tabata, de 4 à 40 min. Sélection équilibrée par familles de mouvements, volumes adaptés à la difficulté et arrondis aux valeurs « naturelles » (15, 21, 400 m…). Déterministe : chaque WOD a une **URL partageable** (graine). |
| ⏱️ **Chrono intégré** | Décompte de 10 s, intervalles EMOM et Tabata, bips (Web Audio), écran maintenu allumé (Wake Lock), compteur de tours. Le score est **reporté automatiquement** dans le formulaire. |
| 📚 **Bibliothèque** | Recherche « live » (Live Component) dans les titres, consignes et mouvements, avec filtres reflétés dans l'URL. **43 WODs de référence de la communauté CrossFit** : les Girls (Fran, Grace, Cindy, Helen, Diane, Annie…), des Hero WODs (Murph, DT, JT, Nate), 20 WODs de l'**Open** (11.1, 12.1, 13.2, 14.5, 15.1, 15.5, 17.1, 18.1, 18.2, 19.1, 20.1, 20.3, 20.5, 21.1, 22.1, 22.3, 23.1, 24.1, 25.1, 26.1 — échelles croissantes et pyramide comprises) et Tabata Something Else. Le WOD du jour est tiré parmi eux. |
| 🏆 **Classements** | Par WOD (meilleur score par athlète, RX avant Scaled) et global par points (semaine, mois, tout). Détection des **records personnels**. |
| 📈 **Tableau de bord** | Séances, points, série de jours consécutifs, part de séances en RX, heatmap d'activité sur 18 semaines, records. Profil public d'athlète. |
| 💬 **Communauté** | « J'aime », favoris et commentaires sans rechargement (Turbo Streams). |
| 🛠️ **Administration** | EasyAdmin 5 : WODs (avec leurs mouvements), catalogue, athlètes, séances, modération des commentaires. |
| 🔐 **Sécurité & RGPD** | Voters, CSRF partout, limitation des tentatives de connexion, mots de passe robustes, vérification d'e-mail, réinitialisation sans énumération de comptes, en-têtes HTTP de sécurité, **export JSON** et **suppression de compte** en libre-service. |

## Ce qui a changé depuis la v1

| v1 (soutenance DWWM) | v2 |
|---|---|
| Symfony 7.0, Doctrine ORM 2 | **Symfony 7.4 LTS**, Doctrine ORM 3, PHP 8.4 |
| « Générateur » = tirage aléatoire d'un exercice en base | **Algorithme de génération** à partir d'un catalogue de 47 mouvements, et 43 benchmarks CrossFit (Girls, Heroes, Open) |
| `Exercice` en texte libre, `HistoriqueExercice` + `Leaderboard` dupliqués | Modèle structuré : `Wod` → `WodMouvement` → `Mouvement`, `Seance` unique source des scores |
| Propriétés `user_id` / `exercice_id` contenant des objets | Nommage clair (`athlete`, `wod`), **enums PHP** typées |
| 7 contrôleurs CRUD admin générés | **EasyAdmin 5** |
| Like/favori en GET sans CSRF | POST + CSRF + Turbo Streams, contraintes d'unicité en base |
| Bootstrap + 3 CDN (Bootstrap, Font Awesome, Bootstrap Icons) | Design system sur mesure, sombre/clair, mobile d'abord, **icônes servies localement** (UX Icons) |
| Pas de tests | **54 tests** (unitaires + fonctionnels), **PHPStan niveau 8**, PHP-CS-Fixer, CI GitHub Actions |

## Propositions de design (aperçu)

En plus du design actuel, trois refontes complètes sont consultables avec le sélecteur en bas de page ou en ajoutant `?design=…` à l'adresse :

- **Studio** (`studio`) — clair, épuré, premium ; l'orange Genwod comme seul accent.
- **Performance** (`performance`) — tableau de bord sportif sombre, chiffres en mono, barre d'icônes.
- **Ludique** (`ludique`) — chaleureux et coloré, série 🔥 dans l'en-tête, badges à débloquer.

Chaque design a ses propres gabarits dans `templates/designs/<design>/` (Twig y cherche en priorité, via `DesignListener`) et sa feuille de style dans `assets/styles/designs/`. Les polices (Inter, Inter Tight, Space Grotesk, JetBrains Mono, Plus Jakarta Sans, Bricolage Grotesque — licence SIL OFL) sont hébergées localement dans `assets/fonts/`.

## Stack

- **Back** : Symfony 7.4 LTS, Doctrine ORM 3 / Migrations, Security (form login, remember-me, login throttling, voters), Validator, Mailer, Rate Limiter, SymfonyCasts Verify Email & Reset Password, EasyAdmin 5
- **Front** : Twig + Twig Components, **Live Components**, Turbo (Drive + Streams), Stimulus, AssetMapper (aucun build Node), UX Icons (Lucide)
- **Qualité** : PHPUnit 13, Zenstruck Foundry, PHPStan 2 (+ extensions Symfony & Doctrine), PHP-CS-Fixer
- **Base de données** : MySQL 8 / MariaDB en dev et en prod, SQLite pour les tests

## Installation

Prérequis : PHP 8.4+ (extensions `intl`, `pdo_mysql`), Composer, MySQL 8 (MAMP, Docker…).

```bash
composer install

# Base de données : MAMP, ou bien Docker (MySQL + Mailpit)
docker compose up -d

# Adapter DATABASE_URL dans .env.local si besoin, puis :
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console app:catalogue:importer      # mouvements + WODs de référence (idempotent)

# Données de démonstration (optionnel)
php bin/console doctrine:fixtures:load

symfony serve   # ou : php -S 127.0.0.1:8000 -t public
```

Comptes de démo créés par les fixtures (mot de passe `genwod-demo-2026`) : `admin@genwod.fr` (administrateur) et `lea@genwod.fr`.

## Commandes utiles

```bash
php bin/console app:generer amrap --duree=15 --materiel=kettlebell   # générer un WOD dans le terminal
php bin/console app:catalogue:importer                               # (ré)importer le catalogue
php bin/console app:import-v1 "mysql://user:pass@host/gen_wod" --simulation   # reprendre les données v1
composer qualite                                                     # style + PHPStan + tests
```

## Architecture

```
src/
├── Command/            app:generer, app:catalogue:importer, app:import-v1
├── Controller/         Accueil, Generateur, Wod, Classement, Espace, Athlete, auth…
│   └── Admin/          Tableau de bord et CRUD EasyAdmin
├── Entity/             User, Wod, WodMouvement, Mouvement, Seance, Commentaire, Favori, WodLike
├── Enum/               WodType, Difficulte, Mode, Categorie, Equipement, Unite, Niveau
├── Generator/          WodGenerator (algorithme) + CriteresGeneration
├── Service/            Points, Statistiques (séries, heatmap), EnregistreurSeance, Interactions, CatalogueImporter
├── Security/           EmailVerifier, Voter/ (WodVoter, CommentaireVoter)
└── Twig/Components/    WodRecherche (Live Component)
templates/components/   WodCard, Tableau, Flammes, Avatar, Stat, Heatmap… (composants anonymes)
assets/controllers/     chrono, theme, partage, force-mdp… (Stimulus)
config/donnees/         catalogue.php — données de référence (mouvements, WODs de référence CrossFit)
```

### Le générateur en bref

1. Filtre le catalogue selon le matériel coché (le poids du corps est toujours disponible), exclut les mouvements très techniques en difficulté facile ou en Tabata.
2. Détermine la structure : nombre de mouvements, tours imposés et facteur de volume selon le format et la durée (ex. EMOM : un nombre de mouvements qui divise la durée).
3. Choisit les mouvements en **alternant les familles** (au plus un cardio et un gainage), en privilégiant le matériel sélectionné.
4. Calcule les volumes (`volume de base × format × difficulté`) puis les arrondit à des valeurs habituelles.

Le tout utilise `Random\Randomizer` (PHP 8.2+) initialisé avec une graine : mêmes critères + même graine = même WOD.

### Barème des points

10 pts par séance, +5 / +10 pour un WOD intermédiaire / difficile, +5 en RX, +5 pour un record personnel.

## Tests et qualité

```bash
php bin/phpunit                      # 54 tests, base SQLite créée automatiquement
vendor/bin/phpstan analyse           # niveau 8
vendor/bin/php-cs-fixer fix          # règles @Symfony
```

## Déploiement (O2Switch ou équivalent)

```bash
composer install --no-dev --optimize-autoloader
APP_ENV=prod php bin/console doctrine:migrations:migrate -n
APP_ENV=prod php bin/console app:catalogue:importer
APP_ENV=prod php bin/console asset-map:compile
APP_ENV=prod php bin/console cache:clear
```

Variables à définir en production : `APP_ENV=prod`, `APP_SECRET`, `DATABASE_URL`, `MAILER_DSN`, `MAILER_FROM`, `DEFAULT_URI` et `EDITEUR_CONTACT` (adresse affichée dans les mentions légales).

Pour reprendre les comptes et l'historique de la v1, déployez la v2 sur une **nouvelle base**, puis lancez `app:import-v1` en pointant vers l'ancienne base (d'abord avec `--simulation`). Les mots de passe existants restent valables.

---

© William Luong — projet DWWM
