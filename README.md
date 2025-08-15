# Unick Enterprises Inc. - Order, Inventory, and Production Tracking System

This repository contains a full-stack application:
- Backend: Laravel 12 (PHP 8.4) in `backend`
- Frontend: React + Vite in `frontend`
- Database: MySQL

## Quick Start (Local Dev)

Prerequisites: PHP 8.2+, Composer, Node 18+, MySQL 8+

1. Backend
```
cd backend
composer install --ignore-platform-req=ext-gd --ignore-platform-req=php-64bit (kapag hindi makapg install ng composer)
cp .env.example .env
php artisan key:generate
# Configure DB in .env (DB_CONNECTION=mysql, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
php artisan migrate --seed
php artisan serve --host=0.0.0.0 --port=8000
```

2. Frontend
```
cd ../frontend
npm install vite @vitejs/plugin-react --save-dev
npm start

```
Open http://localhost:5173

## Modules
- Inventory Management (MRP fields on products, movements, low-stock report, Excel/PDF exports)
- Production Tracking (batches, stages, status, metrics)
- Order Management (product catalog, order placement, feedback, tracking)

## Security
- Laravel Sanctum for API auth and CSRF. `EnsureFrontendRequestsAreStateful` is applied to API; tokens used for SPA.
- RBAC: roles `admin|staff|customer`, enforced via policies and `role` middleware.
- Password hashing with bcrypt/Argon2 via Laravel hashing.
- SQL injection prevention via Eloquent and prepared statements.
- XSS protection by FormRequest validation and output escaping. CSP, HSTS, X-Frame-Options, X-Content-Type-Options set in `SecurityHeaders` middleware.
- Rate limiting on login and API (`RateLimiter::for('api')`).
- HTTPS enforcement in production; trust proxies.
- Encryption at rest with Laravel encryption helpers; in transit via TLS (configure reverse proxy/cert).
- Audit logging: `spatie/laravel-activitylog` used for admin actions (e.g., product CRUD).

## Reports
- Excel exports using `maatwebsite/excel` (PhpSpreadsheet)
- PDF exports using `barryvdh/laravel-dompdf`

## ISO/IEC 25010
- Functionality: complete CRUD and reporting for core modules
- Reliability: migrations, DB constraints, transactions for order creation
- Security: measures above
- Usability: Bootstrap UI, responsive

## Deployment
- Set `APP_ENV=production`, `APP_DEBUG=false`
- Configure web server to force HTTPS and set security headers (Nginx/Apache)
- Run `php artisan migrate --force`
- Set queue and cache stores appropriately
- Harden server: firewall allow 80/443 only; fail2ban; regular updates

## Seed Admin
Create an admin user:
```
php artisan tinker
>>> \App\Models\User::create(['name'=>'Admin','email'=>'admin@example.com','password'=>bcrypt('password'),'role'=>'admin'])
```

## API
Base: `/api/v1`
- POST `/auth/register`, `/auth/login`, `/auth/logout`
- GET `/products`, GET `/products/{id}`
- Auth: GET `/me`, CRUD for products (staff/admin), orders, production batches, inventory, reports

## Notes
- Configure `SANCTUM_STATEFUL_DOMAINS` to include your frontend domain
- Vite dev server proxies `/api` to `http://localhost:8000`
