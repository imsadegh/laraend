# Laraend - Laravel Backend API

**Laraend** is the Laravel backend API for the HakimyarFusion Learning Management System (LMS).

## Project Information

- **Framework**: Laravel 12.x
- **PHP Version**: 8.4.13 (Development) / 8.3+ (Production)
- **Database**: PostgreSQL
- **Authentication**: JWT (tymon/jwt-auth)
- **API Domain**: api.ithdp.ir

## Related Projects

This backend serves:
- **Vueend**: Vue.js frontend application
- **HekmatSara**: Flutter mobile application

## Documentation

For complete deployment instructions, server setup, and project documentation, see **[laraend.md](./laraend.md)**.

## Quick Start (Development)

### Prerequisites
- PHP 8.3+
- Composer 2.8+
- PostgreSQL 16+
- Redis

### Local Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd laraend
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your local database credentials
   ```

4. **Generate keys**
   ```bash
   php artisan key:generate
   php artisan jwt:secret
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Start development server**
   ```bash
   php artisan serve
   ```

## Production Deployment

For production deployment to Ubuntu 24.04 VPS with Nginx, PostgreSQL, Redis, SSL, and Supervisor, follow the comprehensive guide in **[laraend.md](./laraend.md)**.

## API Documentation

API endpoints are documented in [laraend.md](./laraend.md#api-endpoints-overview).

## License

This project is proprietary software for HakimyarFusion LMS.
