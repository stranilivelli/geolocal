# Reciproca SMS — Strutture Convenzionate

Applicazione Laravel 11 + Filament 3 per la gestione e pubblicazione
delle strutture sanitarie convenzionate di Reciproca SMS.

## Stack

| Layer         | Tecnologia             |
|---------------|------------------------|
| Framework     | Laravel 11             |
| Admin panel   | Filament 3             |
| Database      | PostgreSQL 16          |
| Frontend map  | Blade + Alpine.js + Google Maps JS API |
| Auth          | Laravel Breeze / Filament Shield |
| Hosting       | VPS (Nginx + PHP 8.3 + FPM) |
| Deploy        | GitHub Actions → SSH   |

## Installazione locale

```bash
# 1. Clona e installa dipendenze
git clone https://github.com/tuo-repo/reciproca-app
cd reciproca-app
composer install
npm install && npm run build

# 2. Configura ambiente
cp .env.example .env
php artisan key:generate

# 3. Configura .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=reciproca
DB_USERNAME=reciproca
DB_PASSWORD=password_sicura

GOOGLE_MAPS_API_KEY=la-tua-chiave

FILAMENT_DOMAIN=admin.reciprocasms.it   # opzionale, pannello su sottodominio

# 4. Migra e popola
php artisan migrate
php artisan db:seed --class=JoomlaImportSeeder

# 5. Crea utente admin
php artisan make:filament-user

# 6. Avvia
php artisan serve
```

## Struttura chiave

```
app/
  Models/
    Location.php          ← model con scope published, nearby, ecc.
    Category.php          ← specializzazioni mediche
  Filament/
    Resources/
      LocationResource.php       ← admin CRUD completo
      LocationResource/Pages/    ← List, Create, Edit
  Http/
    Controllers/
      Api/LocationController.php ← API pubblica per la mappa

database/
  migrations/
    ..._create_categories_table.php
    ..._create_locations_table.php
  seeders/
    JoomlaImportSeeder.php  ← importa da dump Joomla

routes/
  api.php    ← GET /api/v1/locations, /categories, /provinces
  web.php    ← pagine pubbliche Blade
```

## API pubblica

```
GET /api/v1/locations
  ?city=Firenze
  ?province=FI
  ?category_id[]=50&category_id[]=56
  ?search=fisioterapia
  ?featured=1
  ?lat=43.77&lng=11.25&radius_km=20   ← ricerca per prossimità

GET /api/v1/locations/{slug}
GET /api/v1/categories
GET /api/v1/provinces
```

## Deploy su VPS

```bash
# Nginx config (semplificata)
server {
    listen 443 ssl;
    server_name reciprocasms.it www.reciprocasms.it;
    root /var/www/reciproca/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }
}

# Pannello admin su sottodominio separato (consigliato)
server {
    listen 443 ssl;
    server_name admin.reciprocasms.it;
    root /var/www/reciproca/public;
    # stessa config, ma con autenticazione IP opzionale
}
```

## Import da Joomla

Due modalità:

**A — Connessione diretta al DB Joomla** (se entrambi i DB sono sullo stesso server):
```php
// config/database.php
'joomla' => [
    'driver'   => 'mysql',
    'host'     => env('DB_JOOMLA_HOST'),
    'database' => env('DB_JOOMLA_DATABASE'),
    'username' => env('DB_JOOMLA_USERNAME'),
    'password' => env('DB_JOOMLA_PASSWORD'),
],
```

**B — CSV**: esporta `sp8r4_gmapfp` in CSV da phpMyAdmin,
salvalo in `database/seeders/data/gmapfp.csv`
e aggiorna `JoomlaImportSeeder::getData()` per leggerlo.

## Comandi utili

```bash
php artisan make:filament-user          # crea admin
php artisan optimize:clear              # svuota cache
php artisan queue:work                  # avvia worker (per hits asincroni)
php artisan filament:upgrade            # aggiorna Filament
```
