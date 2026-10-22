# CLAUDE.md

Guide pour travailler sur ce dépôt (Genwod v2, branche `refonte/v2`).

## Stack

- Symfony 7.4 LTS, PHP 8.4+, Doctrine ORM 3, MySQL 8 (dev/prod), SQLite (tests)
- Twig + Twig Components + Live Components, Turbo, Stimulus, AssetMapper (pas de Node)
- EasyAdmin 5, UX Icons (Lucide, icônes figées dans `assets/icons/`)

## Commandes

```bash
php bin/console doctrine:migrations:migrate
php bin/console app:catalogue:importer          # données de référence, idempotent
php bin/console doctrine:fixtures:load          # démo : admin@genwod.fr / lea@genwod.fr, mdp genwod-demo-2026
php bin/phpunit                                 # tests (SQLite, base remise à zéro par Foundry)
vendor/bin/phpstan analyse --memory-limit=1G    # niveau 8 (nécessite var/cache/dev : cache:warmup)
vendor/bin/php-cs-fixer fix                     # règles @Symfony
php bin/console ux:icons:lock                   # après avoir utilisé une nouvelle icône
```

## Conventions

- Code métier en **français** (entités, méthodes, routes, templates), noms de relations clairs (`athlete`, `wod`, `auteur`).
- Les listes fermées sont des **enums PHP** (`src/Enum`) qui implémentent `TranslatableInterface` : leur libellé s'affiche automatiquement dans les formulaires et EasyAdmin.
- Toute action qui modifie des données passe en **POST + CSRF** ; les droits fins passent par des **Voters** (`WOD_VOIR`, `WOD_GERER`, `COMMENTAIRE_SUPPRIMER`).
- Interactions sans rechargement : réponses **Turbo Stream** (`*.stream.html.twig`) avec repli en redirection classique.
- Les formulaires invalides renvoient un **422** (nécessaire pour Turbo).
- Composants Twig anonymes dans `templates/components/` ; le seul composant PHP est le Live Component `WodRecherche`.
- Éviter le N+1 sur les listes de WODs : `WodRepository::prechargerLignes()`.

## Designs

- Trois propositions de refonte (`studio`, `performance`, `ludique`) en plus du design actuel : `DesignListener` (cookie + `?design=`) fait chercher Twig d'abord dans `templates/designs/<design>/`. Partiels communs à réutiliser : `wod/_chrono_attributs`, `wod/_chrono_dialog`, `wod/_seance_form`, `generateur/_enregistrer`. Les ids des cibles Turbo Stream (`interactions-*`, `commentaires-liste`, `nb-commentaires`…) doivent être conservés.
- Fonctions Twig `resume_athlete()` et `badges_athlete()` (`App\Twig\AthleteExtension`).

## Architecture

- `WodGenerator` : génération déterministe (graine) depuis le catalogue `Mouvement`. Règles : matériel disponible, alternance des familles, ≤ 1 cardio, ≤ 1 gainage, volumes arrondis.
- `Seance` est l'unique source des scores ; le classement par WOD et les records sont calculés depuis elle (`SeanceRepository`). Les points sont figés à l'enregistrement (`EnregistreurSeance` + `Points`).
- `Statistiques` : série de jours consécutifs et heatmap.
- `config/donnees/catalogue.php` : catalogue de mouvements et **uniquement des WODs de référence de la communauté CrossFit** (Girls, Hero WODs, CrossFit Open, classiques ; quantité 0 = « Max » ; les échelles et pyramides de l'Open sont saisies ligne par ligne comme des chippers, leurs totaux officiels sont vérifiés par PagesPubliquesTest), avec `schema` (21-15-9…) et `toutChaqueMinute` (EMOM type Chelsea). Les mouvements `actif = false` servent aux benchmarks mais pas au générateur.
- `app:import-v1` : reprise des données de la v1 (schéma avec colonnes `user_id_id` / `exercice_id_id`).
