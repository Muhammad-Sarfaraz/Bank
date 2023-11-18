#!/bin/bash
# Author: Sarfaraz

# Ensure you have Composer installed
if ! command -v composer &> /dev/null; then
    echo "Composer is not installed. Please install Composer and try again."
    exit 1
fi

echo "Setup will take 5/10 Minutes, So please be patient. Thank You!"

# Install Laravel dependencies
composer install
echo "Composer install is completed."

# Generate a key for the application
php artisan key:generate

# Run database migrations
php artisan migrate:fresh --seed
echo "Database seed is completed."

# Storage link
php artisan storage:link

# Ensure you have Node installed
if ! node -v composer &> /dev/null; then
    echo "Node is not installed. Please install Node and try again."
    exit 1
fi

# Install Node.js dependencies and build assets
npm install
echo "npm install is completed."
npm run prod
echo "Production build is completed."

# Cache Clear
php artisan optimize:clear

# Output information
echo "Congratulations! Setup Complete 🎉"
