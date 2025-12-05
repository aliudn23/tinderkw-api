# TinderKW API

A Tinder-like REST API built with Laravel 12

## Features

- ✅ JWT-based device authentication
- ✅ People recommendation system with pagination
- ✅ Like/Dislike functionality
- ✅ View liked people history
- ✅ Email notification when person reaches 50+ likes
- ✅ Swagger/OpenAPI documentation

## Requirements

- PHP 8.2+
- PostgreSQL 13+
- Composer
- Laravel 12

## Installation

### 1. Clone the repository

```bash
git clone <your-repo-url>
cd tinderkw-api
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment

Copy `.env` file and update the following variables:

```env
# Database (PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=tinderkw
DB_USERNAME=postgres
DB_PASSWORD=your_password

# JWT Configuration
JWT_SECRET=your-secret-key-change-this-in-production
JWT_ALGO=HS256
JWT_TTL=43200

# Mailtrap (for testing)
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
ADMIN_EMAIL=aliudn@gmail.com
```

### 4. Generate JWT secret

```bash
php -r "echo base64_encode(random_bytes(32)) . PHP_EOL;"
```

Update `JWT_SECRET` in `.env` with the generated value.

### 5. Create PostgreSQL database

```bash
createdb tinderkw
# or using psql
psql -U postgres -c "CREATE DATABASE tinderkw;"
```

### 6. Run migrations

```bash
php artisan migrate
```

### 7. Seed the database (optional, for testing)

```bash
php artisan db:seed
```

This will create 100 sample people with random data.

### 8. Generate Swagger documentation

```bash
php artisan l5-swagger:generate
```

## Running the Application

### Development Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

### Swagger Documentation

Visit `http://localhost:8000/api/documentation` to view the interactive API documentation.

## API Endpoints

### Authentication

- `POST /api/register-device` - Register device and get JWT token

### People

- `GET /api/people` - Get recommended people (paginated)
- `GET /api/people/{id}` - Get person details

### Interactions

- `POST /api/interactions/like` - Like a person
- `POST /api/interactions/dislike` - Dislike a person
- `GET /api/interactions/liked` - Get list of liked people

## Database Schema

### Tables

**devices**
- id (PK)
- device_id (unique)
- device_type
- device_model
- last_active_at
- timestamps

**people**
- id (PK)
- name
- age
- pictures (JSON)
- latitude
- longitude
- city
- country
- like_count
- timestamps

**interactions**
- id (PK)
- device_id (FK → devices)
- person_id (FK → people)
- type (enum: 'like', 'dislike')
- timestamps
- UNIQUE(device_id, person_id)

## Authentication

The API uses JWT (JSON Web Tokens) for authentication:

1. Register your device using `/api/register-device` endpoint
2. Receive a JWT token in the response
3. Include the token in the `Authorization` header for all subsequent requests:
   ```
   Authorization: Bearer your-jwt-token
   ```

## Email Notifications

When a person receives 50 likes, an email notification is automatically sent to the admin email configured in `.env` (`ADMIN_EMAIL`).

For testing, configure Mailtrap credentials in `.env`.

## Postman Collection

Import `TinderKW-API.postman_collection.json` into Postman to test the API.

The collection includes:
- Environment variables for base URL and JWT token
- All API endpoints with sample requests
- Automatic token extraction from register-device response

## License

MIT
