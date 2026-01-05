#!/bin/bash

# Ploi.io Deployment Script for Savvy Backend
# This script is meant to be used with Ploi.io deployment commands

set -e

echo "🚀 Starting deployment..."

# Pull latest changes from repository
echo "📥 Pulling latest changes..."
git pull origin main

# Install/update PHP dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Install/update Node dependencies
echo "📦 Installing NPM dependencies..."
npm ci

# Build frontend assets
echo "🔨 Building frontend assets..."
npm run build

# Clear and cache Laravel configs
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run database migrations
echo "📊 Running database migrations..."
php artisan migrate --force

# Run seeders (safe to re-run - uses updateOrCreate/firstOrCreate)
echo "🌱 Running seeders..."
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan db:seed --class=PortfolioServicesAndIndustriesSeeder --force
php artisan db:seed --class=TestimonialSeeder --force

# Cache configs for production
echo "⚡ Caching for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
echo "🔧 Optimizing..."
php artisan optimize

# Create storage link if it doesn't exist
echo "🔗 Creating storage link..."
php artisan storage:link || true

# Restart queue workers (if using)
echo "🔄 Restarting queue workers..."
php artisan queue:restart || true

echo "✅ Deployment completed successfully!"
