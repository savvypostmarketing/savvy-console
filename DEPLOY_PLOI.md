# Ploi.io Deployment Commands

Copy and paste these commands in your Ploi.io deployment script section:

## Deployment Script

```bash
cd {SITE_DIRECTORY}

git pull origin main

composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

npm ci
npm run build

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan migrate --force

# Seeders - safe to re-run (uses updateOrCreate)
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan db:seed --class=PortfolioServicesAndIndustriesSeeder --force

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

php artisan storage:link || true
php artisan queue:restart || true
```

## Environment Variables Required

Make sure these are set in your `.env` file on the server:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password

# Add other necessary variables...
```

## First Time Setup

If this is the first deployment, run these additional commands after the main deployment:

```bash
php artisan key:generate
php artisan db:seed --class=AdminUserSeeder --force
php artisan db:seed --class=PortfolioSampleSeeder --force
```

## Seeders Explained

| Seeder | Purpose | Safe to Re-run |
|--------|---------|----------------|
| `RolesAndPermissionsSeeder` | Creates/updates roles and permissions | Yes (updateOrCreate) |
| `PortfolioServicesAndIndustriesSeeder` | Creates predefined services and industries | Yes (updateOrCreate) |
| `AdminUserSeeder` | Creates initial admin user | Run once only |
| `PortfolioSampleSeeder` | Creates sample portfolio projects | Yes (updateOrCreate) |

## Quick Deploy (Just Updates)

For quick updates without full rebuild:

```bash
cd {SITE_DIRECTORY}
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan optimize:clear
php artisan optimize
```
