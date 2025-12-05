#!/bin/bash

echo "🚀 TinderKW API Setup Script"
echo "=============================="
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ .env file not found!"
    echo "Please copy .env.example to .env and configure your environment variables."
    exit 1
fi

echo "✅ .env file found"

# Check if database is configured
if grep -q "DB_CONNECTION=pgsql" .env; then
    echo "✅ PostgreSQL configured"
else
    echo "⚠️  Warning: PostgreSQL not configured in .env"
fi

# Install dependencies
echo ""
echo "📦 Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Generate JWT secret if not set
if grep -q "JWT_SECRET=your-secret-key" .env; then
    echo ""
    echo "🔑 Generating JWT secret..."
    JWT_SECRET=$(php -r "echo base64_encode(random_bytes(32));")
    sed -i "s|JWT_SECRET=your-secret-key-change-this-in-production|JWT_SECRET=$JWT_SECRET|g" .env
    echo "✅ JWT secret generated"
fi

# Run migrations
echo ""
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Seed database
echo ""
read -p "Do you want to seed the database with sample data? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🌱 Seeding database..."
    php artisan db:seed
    echo "✅ Database seeded with 100 sample people"
fi

# Generate Swagger documentation
echo ""
echo "📚 Generating Swagger documentation..."
php artisan l5-swagger:generate

echo ""
echo "✨ Setup complete!"
echo ""
echo "📝 Next steps:"
echo "1. Update your .env file with PostgreSQL credentials"
echo "2. Update MAIL_USERNAME and MAIL_PASSWORD for Mailtrap"
echo "3. Run: php artisan serve"
echo "4. Visit: http://localhost:8000/api/documentation"
echo ""
echo "📮 Postman collection: TinderKW-API.postman_collection.json"
echo ""
