# RATP Réseaux de Surface — Documentation Technique

Système de gestion des plaintes et des missions de contrôle qualité pour la RATP Réseaux de Surface.

---

## Stack technique

| Composant | Technologie |
|---|---|
| Backend | Laravel 13 / PHP 8.4 |
| Base de données | PostgreSQL |
| Frontend | Tailwind CSS v4, Alpine.js |
| Cartographie | Leaflet.js |
| Authentification | Laravel Breeze |
| Tests | PHPUnit 12 |

---

## Prérequis

- PHP 8.4 avec extensions : `pgsql`, `mbstring`, `xml`, `curl`, `zip`, `bcmath`
- PostgreSQL 14+
- Composer
- Node.js 22+ / npm

---

## Installation (développement)

```bash
git clone <url-du-repo>
cd hackathon-ratp

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Configurer `.env` :

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ratp_dev
DB_USERNAME=ratp_user
DB_PASSWORD=votre_mot_de_passe
```

```bash
php artisan migrate
php artisan db:seed

npm run dev
php artisan serve
```

---

## Installation (production)

```bash
composer install --optimize-autoloader
npm ci && npm run build

php artisan migrate --force
php artisan db:seed --force

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Démarrage avec pm2 :

```bash
pm2 start "php artisan serve --host=0.0.0.0 --port=8001" --name ratp
pm2 save && pm2 startup
```

---

## Structure du projet

```
app/
├── Enums/               # UserRole, ComplaintStep, SanctionType...
├── Http/
│   ├── Controllers/     # Un controller par domaine métier
│   ├── Middleware/      # ApiTokenMiddleware
│   └── Requests/        # Form Requests pour la validation
├── Models/              # Modèles Eloquent
└── Notifications/       # Notifications Laravel (base de données)

database/
├── factories/           # Factories pour les tests et le seed
├── migrations/
└── seeders/             # DatabaseSeeder avec données de démo

resources/views/
├── com/                 # Vues rôle Agent Com
├── manager/             # Vues rôle Manager
├── rh/                  # Vues rôle RH
├── mouche/              # Vues rôle Mouche
├── qrcode/              # Formulaires publics QR Code
├── components/          # Composants Blade réutilisables
└── layouts/             # Layouts principaux

routes/
├── web.php              # Routes web (public + auth)
└── api.php              # Routes API (authentification par token Bearer)
```

---

## Rôles utilisateurs

| Rôle | Description |
|---|---|
| `Com` | Agent de communication — triage et classification des plaintes |
| `Manager` | Manager d'équipe — traitement et missions mouche |
| `RH` | Ressources humaines — sanctions et gratifications |
| `Chauffeur` | Chauffeur de bus — consultation de son dossier |
| `Mouche` | Agent de contrôle qualité — soumission de rapports |

---

## API

Authentification par token Bearer dans le header `Authorization`.

| Méthode | Endpoint | Description |
|---|---|---|
| `GET` | `/api/complaints/pending` | Plainte la plus ancienne en attente de classification |
| `POST` | `/api/complaints/severity` | Enregistre la sévérité (niveau 0–4, nature, justification) |

### GET `/api/complaints/pending`

```json
{
  "id": 42,
  "complaint_type": "Incivilité",
  "description": "Le conducteur a..."
}
```

### POST `/api/complaints/severity`

```json
{
  "complaint_id": 42,
  "level": 3,
  "is_positive": false,
  "justification": "Comportement grave signalé"
}
```

---

## Tests

```bash
php artisan test --compact
```

Les tests utilisent une base SQLite en mémoire (configurée dans `phpunit.xml`).

---

## Variables d'environnement clés

| Variable | Description |
|---|---|
| `APP_ENV` | `local` ou `production` |
| `APP_DEBUG` | `true` en dev, `false` en prod |
| `APP_URL` | URL publique de l'application |
| `DB_*` | Connexion PostgreSQL |
| `API_TOKEN` | Token d'authentification pour l'API IA |
| `CACHE_DRIVER` | `file` par défaut, `redis` recommandé en prod |
| `QUEUE_CONNECTION` | `sync` en dev, `database` en prod |
