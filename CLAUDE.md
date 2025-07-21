# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 11+ application called "Pausalci" - a Serbian flat-tax entrepreneur management system. It uses Filament v4 Beta for the admin panel and helps Serbian entrepreneurs manage clients, invoices, income tracking, and tax obligations.

## Common Development Commands

### Development Environment
```bash
# Start development environment (includes server, queue, logs, and vite)
composer run dev

# Individual services:
php artisan serve          # Start Laravel development server
php artisan queue:listen   # Start queue worker
php artisan pail          # View logs
npm run dev               # Start Vite for asset compilation
```

### Testing & Quality
```bash
composer run test         # Run PHPUnit tests
./vendor/bin/pint        # Run Laravel Pint (code formatting)
php artisan test         # Alternative test command
```

### Database Operations
```bash
php artisan migrate              # Run database migrations
php artisan migrate:fresh       # Fresh migration (drops all tables)
php artisan migrate:rollback    # Rollback migrations
php artisan db:seed             # Seed database
```

### Filament Commands
```bash
php artisan make:filament-user   # Create admin user
php artisan filament:upgrade     # Upgrade Filament components
```

### Build Commands
```bash
npm run build    # Build assets for production
vite build       # Alternative build command
```

## Architecture Overview

### Core Technology Stack
- **Framework**: Laravel 11+ (PHP 8.2+)
- **Admin Panel**: Filament v4 Beta with Serbian language support
- **Database**: SQLite (default), supports MySQL/PostgreSQL
- **Frontend**: Vite + TailwindCSS v4
- **PDF Generation**: DomPDF (via barryvdh/laravel-dompdf)

### Project Structure

#### Filament Resources Pattern
All CRUD operations use Filament's resource pattern with separate classes:
- `Resources/[Entity]/[Entity]Resource.php` - Main resource configuration
- `Resources/[Entity]/Pages/` - Create, Edit, List pages
- `Resources/[Entity]/Schemas/` - Form schemas
- `Resources/[Entity]/Tables/` - Table configurations

#### Key Models and Relationships
- **User**: Main entrepreneur entity with company details
- **Client**: Client companies managed by the entrepreneur
- **Invoice**: Invoice management with payment tracking
- **Income**: Income records (linked to invoices or standalone)
- **Obligation**: Tax and contribution obligations
- **UserCompany**: Extended company information
- **CompanyOwner**: Company ownership details

#### Database
- Uses SQLite by default (`database/database.sqlite`)
- Migrations follow Laravel conventions in `database/migrations/`
- Includes seeder for sample data (`PausalaciSeeder`)

### Filament Configuration
- Admin panel accessible at `/admin`
- Uses Amber color scheme
- Auto-discovers resources in `app/Filament/Resources`
- Serbian language labels throughout (`navigationLabel`, `modelLabel`, etc.)

### Development Notes
- Application designed for Laravel Herd (accessible at `contractor.test`)
- Uses Filament v4 Beta features and schemas
- Implements Serbian business rules for flat-tax entrepreneurs
- All user-facing text should be in Serbian language