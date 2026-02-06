#!/bin/bash

# Laravel Deployment Script for Shared Hosting
# This script pulls the latest code, runs artisan commands, and syncs images
# Usage: ./deploy.sh

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="$HOME/linkadi-web"
PUBLIC_HTML_DIR="$HOME/public_html"
BRANCH="main"

# Function to print colored messages
print_message() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check if we're in the right directory or if project directory exists
if [ ! -d "$PROJECT_DIR" ]; then
    print_error "Project directory not found: $PROJECT_DIR"
    print_message "Please update PROJECT_DIR in the script or ensure the directory exists"
    exit 1
fi

# Navigate to project directory
cd "$PROJECT_DIR" || exit 1

print_message "Starting deployment process..."
print_message "Project directory: $PROJECT_DIR"
print_message "Public HTML directory: $PUBLIC_HTML_DIR"

# Step 1: Pull latest code from main branch
print_message "Step 1: Pulling latest code from $BRANCH branch..."
if git pull origin "$BRANCH"; then
    print_message "✓ Successfully pulled latest code"
else
    print_error "Failed to pull code from git"
    exit 1
fi

# Step 2: Install/Update Composer dependencies
print_message "Step 2: Installing/updating Composer dependencies..."
if command_exists composer; then
    composer install --no-dev --optimize-autoloader --no-interaction
    print_message "✓ Composer dependencies installed"
else
    print_warning "Composer not found, skipping dependency installation"
fi

# Step 3: Run Artisan commands
print_message "Step 3: Running Laravel Artisan commands..."

# Clear all caches first
print_message "  - Clearing application cache..."
php artisan cache:clear || print_warning "cache:clear failed"

print_message "  - Clearing config cache..."
php artisan config:clear || print_warning "config:clear failed"

print_message "  - Clearing route cache..."
php artisan route:clear || print_warning "route:clear failed"

print_message "  - Clearing view cache..."
php artisan view:clear || print_warning "view:clear failed"

# Optimize and cache
print_message "  - Caching configuration..."
php artisan config:cache || print_warning "config:cache failed"

print_message "  - Caching routes..."
php artisan route:cache || print_warning "route:cache failed"

print_message "  - Caching views..."
php artisan view:cache || print_warning "view:cache failed"

print_message "  - Optimizing application..."
php artisan optimize || print_warning "optimize failed"

# Run migrations (optional - uncomment if needed)
# print_message "  - Running database migrations..."
# php artisan migrate --force || print_warning "migrate failed"

# Step 4: Copy images from public/images to public_html/images
print_message "Step 4: Syncing images to public_html..."

if [ ! -d "$PUBLIC_HTML_DIR" ]; then
    print_error "Public HTML directory not found: $PUBLIC_HTML_DIR"
    print_message "Creating directory..."
    mkdir -p "$PUBLIC_HTML_DIR"
fi

# Create images directory in public_html if it doesn't exist
mkdir -p "$PUBLIC_HTML_DIR/images"

# Copy images with rsync (if available) or cp
if command_exists rsync; then
    print_message "  - Using rsync to copy images..."
    rsync -av --delete "$PROJECT_DIR/public/images/" "$PUBLIC_HTML_DIR/images/" || {
        print_warning "rsync failed, trying cp..."
        cp -r "$PROJECT_DIR/public/images/"* "$PUBLIC_HTML_DIR/images/" 2>/dev/null || true
    }
else
    print_message "  - Using cp to copy images..."
    cp -r "$PROJECT_DIR/public/images/"* "$PUBLIC_HTML_DIR/images/" 2>/dev/null || true
fi

# Set proper permissions (adjust as needed for your hosting)
print_message "  - Setting permissions..."
chmod -R 755 "$PUBLIC_HTML_DIR/images" 2>/dev/null || print_warning "Could not set permissions"

print_message "✓ Images synced successfully"

# Step 5: Final optimizations
print_message "Step 5: Running final optimizations..."

# Clear opcache if available (may require sudo or specific permissions)
if command_exists php; then
    print_message "  - Clearing OPcache..."
    php -r "if (function_exists('opcache_reset')) { opcache_reset(); }" 2>/dev/null || print_warning "OPcache reset not available or failed"
fi

# Summary
print_message ""
print_message "=========================================="
print_message "Deployment completed successfully!"
print_message "=========================================="
print_message ""
print_message "Summary:"
print_message "  ✓ Code pulled from $BRANCH branch"
print_message "  ✓ Dependencies updated"
print_message "  ✓ Laravel caches cleared and optimized"
print_message "  ✓ Images synced to public_html/images"
print_message ""
print_message "Next steps:"
print_message "  - Verify the application is working correctly"
print_message "  - Check logs if you encounter any issues: $PROJECT_DIR/storage/logs"
print_message ""
