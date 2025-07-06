# Pausalci - Serbian Flat-Tax Entrepreneur Management System

A comprehensive web application designed specifically for Serbian "pausalci" (flat-tax entrepreneurs) to manage their business operations, track income, calculate taxes, create invoices, and monitor payment obligations.

## Features

- **Dashboard**: Overview of annual income, tax limits, and upcoming obligations
- **Client Management**: CRUD operations for managing clients
- **Invoice Management**: Create, track, and manage invoices with PDF generation
- **Income Tracking**: Monitor annual income against pausalac limits
- **Tax & Contribution Calculations**: Track monthly obligations (tax, pension, health)
- **Serbian Language Support**: Full interface in Serbian language

## Technology Stack

- **Backend**: Laravel 11+
- **Admin Panel**: Filament v4 Beta
- **Database**: SQLite (default, can be changed to MySQL/PostgreSQL)
- **PDF Generation**: DomPDF
- **Authentication**: Filament Authentication

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy environment file:
   ```bash
   cp .env.example .env
   ```
4. Generate application key:
   ```bash
   php artisan key:generate
   ```
5. Run migrations:
   ```bash
   php artisan migrate
   ```
6. Create admin user:
   ```bash
   php artisan make:filament-user
   ```

## Usage

1. Access the admin panel at `/admin`
2. Login with your admin credentials
3. Start by adding clients in the "Klijenti" section
4. Create invoices and track payments
5. Monitor your annual income and tax obligations on the dashboard

## Database Models

- **Users**: Entrepreneur information with company details
- **Clients**: Client company information
- **Invoices**: Invoice management with payment tracking
- **Incomes**: Income records (linked to invoices or standalone)
- **Obligations**: Tax and contribution obligations
- **Notifications**: System notifications and reminders

## Development

This project is built with Laravel Herd for local development. Access your application at `http://contractor.test`.

## License

This project is open-sourced software licensed under the MIT license.
