# Database Setup & Verification Guide

This guide helps verify that the database is properly configured and has test users for login testing.

## Prerequisites

- PostgreSQL running and configured in `.env`
- Laravel application installed with `composer install`

## Database Setup Steps

### 1. Run Migrations

```bash
php artisan migrate
```

This creates all necessary database tables including:
- `users` - User accounts
- `roles` - User roles (admin, student, instructor, etc.)
- `courses` - Course information
- `course_modules` - Course content/modules
- `assignments` - Student assignments
- `enrollments` - Course enrollments

### 2. Run Seeders

```bash
php artisan db:seed
```

This populates the database with:
- **Default roles** (admin, student, instructor, assistant)
- **Test users** with credentials for login testing
- **Sample courses** and course modules

## Creating Test Users

### Via Artisan Tinker

```bash
php artisan tinker
```

Then in the interactive shell:

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Create a student user
User::create([
    'phone_number' => '09121234567',
    'password' => Hash::make('Sadegh@123'),
    'first_name' => 'John',
    'last_name' => 'Doe',
    'full_name' => 'John Doe',
    'username' => 'john.doe',
    'email' => 'john@example.com',
    'role_id' => 1,  // Student role
]);

// Create an instructor user
User::create([
    'phone_number' => '09111234567',
    'password' => Hash::make('Password@123'),
    'first_name' => 'Jane',
    'last_name' => 'Smith',
    'full_name' => 'Jane Smith',
    'username' => 'jane.smith',
    'email' => 'jane@example.com',
    'role_id' => 2,  // Instructor role
]);

// Create an admin user
User::create([
    'phone_number' => '09101234567',
    'password' => Hash::make('Admin@123'),
    'first_name' => 'Admin',
    'last_name' => 'User',
    'full_name' => 'Admin User',
    'username' => 'admin',
    'email' => 'admin@example.com',
    'role_id' => 5,  // Admin role
]);
```

## Verification

### Check Users Exist

```bash
php artisan tinker
```

```php
use App\Models\User;

// Count total users
User::count();

// List all users
User::all();

// Check specific user
User::where('phone_number', '09121234567')->first();
```

### Check Roles Exist

```php
use App\Models\Role;

Role::all();
// Should return: admin, student, instructor, assistant
```

### Test Login Endpoint

```bash
curl -X POST https://api.ithdp.ir/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "09121234567",
    "password": "Sadegh@123"
  }'
```

**Expected Response (200 OK):**
```json
{
  "message": "Login successful.",
  "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "userData": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "full_name": "John Doe",
    "phone_number": "09121234567",
    "email": "john@example.com",
    "role": "student"
  },
  "userAbilityRules": [
    {"action": "read", "subject": "Course"},
    {"action": "submit", "subject": "Assignment"}
  ]
}
```

## Role IDs Reference

| ID | Role | Permissions |
|----|------|------------|
| 1 | Student | Read courses, submit assignments |
| 2 | Instructor | Manage courses, grade assignments |
| 3 | Assistant | Assist courses, manage grades |
| 5 | Admin | Full access to all resources |

## Production Database

For production (`api.ithdp.ir`):

1. The database is PostgreSQL 16+
2. Database name: `imgufoyb_hakimyar_db_prod`
3. User credentials are in `.env.production`
4. Migrations have already been run
5. Test users should exist in the database

To add new test users on production:

```bash
ssh your-vps-user@api.ithdp.ir
cd /path/to/laraend
php artisan tinker
```

Then use the commands above to create users.

## Troubleshooting

### 500 Error on Login

Check logs:
```bash
tail -50 storage/logs/laravel.log
```

Common issues:
- Invalid JWT_SECRET in `.env`
- Database connection error
- Missing role for user

### CORS Preflight Error

If you see "Status code: 500" on OPTIONS request:
- Check that CORS middleware is enabled
- Verify `FRONTEND_URL` environment variable is set correctly
- Check Laravel logs for middleware errors

### Users Not Loading

```bash
php artisan tinker
User::with('role')->first();
```

If role is null, the user's `role_id` doesn't match any existing role. Fix with:

```php
User::where('role_id', 0)->update(['role_id' => 1]); // Set to student
```

## Database Schema

### Users Table

```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    phone_number VARCHAR(20) UNIQUE,
    email VARCHAR(255),
    password VARCHAR(255),
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    full_name VARCHAR(100),
    username VARCHAR(255) UNIQUE,
    avatar VARCHAR(255),
    role_id BIGINT,
    email_verified_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP  -- Soft deletes
);
```

### Roles Table

```sql
CREATE TABLE roles (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) UNIQUE,
    display_name VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Next Steps

1. Run migrations: `php artisan migrate`
2. Create test users using Tinker
3. Test login endpoint with curl or Postman
4. Verify frontend can login and see landing page → dashboard
