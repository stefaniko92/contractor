# Technology Stack

## Core Technologies

### Backend
- **Language**: PHP 8.2+
- **Framework**: Laravel 12.x
- **API Architecture**: RESTful with Laravel Resource Controllers
- **Authentication**: Laravel's built-in authentication with Filament guards

### Admin Panel
- **Framework**: Filament v4.0 Beta
- **Components**: Forms, Tables, Actions, Widgets, Notifications
- **Localization**: Full Serbian language support
- **Theme**: Custom Amber color scheme

### Database
- **Primary**: SQLite (development default)
- **Production Options**: MySQL 8.0+ / PostgreSQL 14+
- **ORM**: Eloquent ORM with relationships
- **Migrations**: Laravel Schema Builder
- **Seeding**: Database seeders for development/testing

### Frontend
- **Build Tool**: Vite 6.2.4
- **CSS Framework**: Tailwind CSS 4.0.0
- **JavaScript Framework**: Alpine.js (bundled with Livewire/Filament)
- **Component Framework**: Livewire v3 (integrated with Filament)

## Third-Party Integrations

### Document Generation
- **PDF Creation**: barryvdh/laravel-dompdf v3.1
- **QR Codes**: simplesoftwareio/simple-qrcode v4.2
- **Document Templates**: Blade templating engine

### Payment & Billing
- **Subscription Management**: Laravel Cashier v16.0
- **Payment Processor**: Stripe integration (via Cashier)

### File Management
- **Media Library**: spatie/laravel-medialibrary v11.13
- **Cloud Storage**: AWS S3 support via Flysystem
- **Local Storage**: Laravel's storage facade

### External APIs
- **Exchange Rates**: National Bank of Serbia (NBS) API
- **E-Invoice Integration**: SEF/eFaktura API (Serbian government)
- **Gotenberg**: gotenberg/gotenberg-php v2.14 for advanced PDF operations

## Development Tools

### Local Development
- **Environment**: Laravel Herd (macOS)
- **URL**: contractor.test (local domain)
- **Hot Reload**: Vite dev server
- **Database GUI**: TablePlus/HeidiSQL (recommended)

### Testing
- **Unit Testing**: PHPUnit 11.5.3
- **Feature Testing**: Laravel's testing helpers
- **Browser Testing**: Laravel Nightwatch v1.21
- **Mocking**: Mockery v1.6
- **Factories**: Laravel factories for test data

### Code Quality
- **Linting**: Laravel Pint v1.13 (PHP CS Fixer preset)
- **Debugging**: Laradumps v4.5 + Laravel Telescope v5.15
- **IDE Helper**: Laravel Boost v1.5
- **Git Hooks**: Pre-commit Pint formatting

### DevOps
- **Version Control**: Git
- **Package Management**: Composer 2.x for PHP, npm/yarn for JavaScript
- **Environment Config**: .env files with Laravel's config caching
- **Logging**: Laravel Pail v1.2.2 for real-time log viewing
- **Monitoring**: Laravel Telescope for request/query debugging

## Architecture Patterns

### Application Structure
- **Pattern**: Domain-driven design with Filament resources
- **Resources**: Filament resource pattern for CRUD operations
- **Services**: Service layer for business logic
- **Repositories**: Eloquent models as repositories
- **Helpers**: Custom helper classes for utilities

### File Organization
```
app/
├── Filament/
│   ├── Resources/       # Filament CRUD resources
│   │   └── [Entity]/
│   │       ├── Pages/   # List, Create, Edit pages
│   │       ├── Schemas/ # Form schemas
│   │       └── Tables/  # Table configurations
│   ├── Pages/          # Custom Filament pages
│   └── Widgets/        # Dashboard widgets
├── Models/             # Eloquent models
├── Services/           # Business logic services
├── Http/
│   ├── Controllers/    # API/Web controllers
│   └── Middleware/     # Custom middleware
├── Helpers/           # Utility classes
├── Livewire/          # Livewire components
└── Console/Commands/  # Artisan commands
```

### Database Design
- **Migrations**: Versioned, reversible schema changes
- **Relationships**: Properly defined Eloquent relationships
- **Indexes**: Strategic indexing for query performance
- **Soft Deletes**: Where applicable for data retention

## Performance Optimization

### Caching
- **Config Cache**: Laravel config caching for production
- **Route Cache**: Compiled routes for faster resolution
- **View Cache**: Compiled Blade templates
- **Query Cache**: Strategic use of remember() for expensive queries

### Asset Optimization
- **Bundling**: Vite for JavaScript/CSS bundling
- **Minification**: Automatic in production builds
- **Compression**: Gzip/Brotli via web server
- **Lazy Loading**: Images and components as needed

### Database Optimization
- **Eager Loading**: N+1 query prevention
- **Query Optimization**: Indexed columns, optimized joins
- **Database Connection Pooling**: For high-traffic scenarios

## Security Measures

### Application Security
- **CSRF Protection**: Laravel's built-in CSRF tokens
- **XSS Prevention**: Blade's automatic escaping
- **SQL Injection Prevention**: Eloquent ORM and query builder
- **Mass Assignment Protection**: Model fillable/guarded properties

### Authentication & Authorization
- **Password Hashing**: Bcrypt with Laravel's Hash facade
- **Session Management**: Secure session handling
- **Rate Limiting**: API and login throttling
- **2FA Support**: Ready for implementation

### Data Protection
- **Encryption**: Laravel's encryption for sensitive data
- **HTTPS**: Enforced in production
- **Environment Variables**: Sensitive config in .env files
- **Audit Logging**: User action tracking

## Deployment Requirements

### Server Requirements
- **PHP**: 8.2+ with required extensions
- **Web Server**: Nginx (recommended) or Apache
- **Process Manager**: PHP-FPM
- **Queue Worker**: Supervisor for Laravel queues
- **Cron**: For scheduled tasks

### Infrastructure
- **Hosting**: VPS or cloud (AWS, DigitalOcean, etc.)
- **CDN**: CloudFlare for static assets
- **Backup**: Automated database and file backups
- **Monitoring**: Server and application monitoring

## Future Considerations

### Scalability
- **Horizontal Scaling**: Load balancer ready
- **Queue System**: Redis/Database queues for background jobs
- **Cache Layer**: Redis/Memcached for session and cache storage
- **Database Replication**: Read/write splitting capability

### Upgrades Path
- **Laravel**: Keep within LTS versions
- **Filament**: Stable v4 release migration plan
- **PHP**: PHP 8.3+ compatibility maintained
- **Dependencies**: Regular security updates

### Potential Additions
- **API Gateway**: For mobile app support
- **WebSocket Server**: Real-time notifications
- **Elasticsearch**: Advanced search capabilities
- **Docker**: Containerization for deployment