# PHP & Laravel Code Style Guide

This document outlines PHP and Laravel coding standards for this project, following PSR-12 and Laravel conventions.

## 1. PHP Language Rules

### General
- **PHP Version:** Target PHP 8.2+ features
- **Strict Types:** Use `declare(strict_types=1);` in new files
- **Type Declarations:** Always use type hints for parameters and return types

### Namespacing & Imports
- One namespace per file
- Use statements should be alphabetically sorted
- Group imports: PHP core, packages, then application classes
- No unused imports

### Error Handling
- Use specific exception types
- Never suppress errors with `@`
- Always handle potential null values explicitly

## 2. Laravel Conventions

### Eloquent Models
- Use singular names for models (User, Invoice, not Users, Invoices)
- Define relationships explicitly with return types
- Use attribute casting in the `casts()` method
- Implement query scopes for reusable queries

### Controllers
- Keep controllers thin - business logic belongs in services
- Use resource controllers for CRUD operations
- Return consistent response formats
- Use Form Requests for validation

### Service Classes
- Place business logic in service classes
- Use dependency injection
- Keep services focused on a single responsibility
- Return data transfer objects for complex responses

## 3. Code Style Rules

### Formatting
- **Indentation:** 4 spaces (no tabs)
- **Line Length:** Soft limit 80 chars, hard limit 120 chars
- **Blank Lines:** One between methods, two between class sections
- **Braces:** Opening brace on same line for methods and control structures

### Naming Conventions
- **Classes:** PascalCase (UserController, InvoiceService)
- **Methods:** camelCase (getUserById, calculateTax)
- **Variables:** camelCase ($userName, $invoiceTotal)
- **Constants:** UPPER_SNAKE_CASE (MAX_RETRY_ATTEMPTS)
- **Database:** snake_case for tables and columns

### DocBlocks
- Required for all public methods
- Include parameter types and descriptions
- Document thrown exceptions
- Use `@deprecated` for methods to be removed

Example:
```php
/**
 * Calculate monthly tax obligations for a pausalac.
 *
 * @param User $user The entrepreneur user
 * @param Carbon $month The month to calculate for
 * @return TaxCalculation Calculated tax amounts
 * @throws InvalidPausalacException If user is not a valid pausalac
 */
public function calculateMonthlyTax(User $user, Carbon $month): TaxCalculation
{
    // Implementation
}
```

## 4. Database & Eloquent

### Migrations
- Use descriptive names with timestamps
- Always define both up() and down() methods
- Set appropriate indexes for foreign keys
- Use consistent column types

### Relationships
- Always define inverse relationships
- Use eager loading to prevent N+1 queries
- Type-hint relationship methods

Example:
```php
public function invoices(): HasMany
{
    return $this->hasMany(Invoice::class);
}
```

### Queries
- Use query builder over raw SQL
- Use scopes for reusable query logic
- Chunk large dataset operations
- Always validate user input used in queries

## 5. Filament Resources

### Resource Structure
- Separate form schemas and table configurations
- Use dedicated schema classes for complex forms
- Keep resource classes focused on configuration

### Forms
- Group related fields in sections
- Use appropriate field types
- Implement real-time validation
- Provide helpful placeholder text

### Tables
- Define searchable and sortable columns
- Use filters for data refinement
- Implement bulk actions thoughtfully
- Optimize queries with eager loading

## 6. Testing

### Test Organization
- Feature tests for user flows
- Unit tests for services and helpers
- Follow AAA pattern: Arrange, Act, Assert
- Use factories for test data

### Assertions
- Be specific with assertions
- Test both success and failure paths
- Verify database state changes
- Check for proper authorization

## 7. Security

### Input Validation
- Never trust user input
- Use Form Requests for validation
- Sanitize data before storage
- Validate file uploads thoroughly

### Authorization
- Use policies for model authorization
- Implement gates for general permissions
- Check permissions in controllers
- Log security-relevant actions

## 8. Performance

### Optimization
- Cache expensive operations
- Use database indexes strategically
- Implement pagination for lists
- Queue heavy operations

### Best Practices
- Avoid N+1 queries with eager loading
- Use select() to limit returned columns
- Cache configuration in production
- Optimize images and assets

## 9. Code Quality Tools

Run before committing:
```bash
# Format code
./vendor/bin/pint

# Run tests
php artisan test

# Clear caches
php artisan optimize:clear
```

## 10. Common Patterns

### Repository Pattern
Not needed with Eloquent - use models directly

### Service Pattern
Extract business logic to service classes:
```php
class InvoiceService
{
    public function createInvoice(User $user, array $data): Invoice
    {
        // Business logic here
    }
}
```

### Action Classes
For single-responsibility operations:
```php
class CalculateMonthlyTaxAction
{
    public function execute(User $user, Carbon $month): TaxCalculation
    {
        // Single focused action
    }
}
```

**BE CONSISTENT.** When editing code, match the existing style.