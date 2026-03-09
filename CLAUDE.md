# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

- **Symfony 7.0** (PHP >= 8.2) with Doctrine ORM
- **PostgreSQL** (via Docker)
- **Twig** templates with Symfony UX Turbo and Stimulus
- **Symfony AssetMapper** (no Webpack/Encore)

## Common Commands

```bash
# Start the database
docker compose up -d

# Install dependencies
composer install

# Run database migrations
php bin/console doctrine:migrations:migrate

# Create a new migration after entity changes
php bin/console doctrine:migrations:diff

# Clear cache
php bin/console cache:clear

# Run tests
php bin/phpunit

# Run a single test file
php bin/phpunit tests/path/to/TestFile.php
```

## Architecture

### Entities and Relationships

The central entity is **Exercice** (fitness exercise), which has OneToMany relationships to:
- `HistoriqueExercice` — user completion records (stores `nombreRepetition`)
- `Leaderboard` — per-exercise scores per user
- `LikeDislike` — like/dislike votes per user
- `Commentaire` — user comments
- `Favoris` — user bookmarks

**User** implements `UserInterface` and `PasswordAuthenticatedUserInterface`. User score (`getScore()`) is computed dynamically by summing `nombreRepetition` across all `HistoriqueExercice` records. User profiles include: nom, prenom, email, age, poids, taille, genre.

### Controller Structure

Controllers are split between public-facing and admin:
- **Public**: `ExerciceController`, `HistoriqueExerciceController`, `LeaderboardController`, `RechercheExerciceController`, `CommentaireController`, `FavoriController`, `LikeDislikeController`, `UserController`, `HomeController`
- **Admin** (require `ROLE_ADMIN`, prefixed `/admin/`): `AdminExerciceController`, `AdminUserController`, `AdminCommentaireController`, `AdminFavorisController`, `AdminHistoriqueExerciceController`, `AdminLeaderboardController`, `AdminLikeDislikeController`
- **Auth**: `SecurityController`, `RegistrationController`, `ResetPasswordController`

### Security

- Custom `LoginFormAuthenticator` at `src/Security/LoginFormAuthenticator.php` using email/password
- Routes under `/admin` require `ROLE_ADMIN`
- Routes under `/user` require `ROLE_USER`
- Email verification via `symfonycasts/verify-email-bundle`
- Password reset via `symfonycasts/reset-password-bundle`
- Login redirects to `app_home` on success

### Naming Convention Note

Entity relationship properties use `_id` suffix (e.g., `$user_id`, `$exercice_id`) even though they hold full entity objects, not raw IDs. This is a project-specific convention — getters/setters follow the same pattern (`getUserId()`, `setExerciceId()`).

### Key Repository Methods

- `ExerciceRepository::findBySearch(?string $searchTexte, ?int $dureeMin, ?int $dureeMax)` — full-text search on type/description with duration filter
- `ExerciceRepository::findRandom(SessionInterface $session)` — returns a random exercise, avoiding the last shown (stored in session)
- `LeaderboardRepository::findGlobalLeaderboard()` — aggregates scores across all exercises, grouped by user

### Templates

Templates mirror the controller structure. Shared layout pieces:
- `templates/base.html.twig` — main layout
- `templates/navbar.html.twig` — user-facing navbar
- `templates/adminNavbar.html.twig` — admin navbar
- `templates/userNavbar.html.twig` — authenticated user navbar

### Database

PostgreSQL via Docker Compose (`compose.yaml`). Connection configured via `DATABASE_URL` in `.env`.
