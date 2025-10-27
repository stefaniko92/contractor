# Filament Design Standards

This document defines the consistent sizing and styling standards for Filament components in the Pausalci application.

## Icon Sizing Standards

Use `App\Helpers\FilamentHelper::getIconSizeForContext($context)` to get consistent icon sizes.

### Standard Icon Sizes

| Size | Class | Use Case | Context |
|------|-------|---------|----------|
| xs   | h-3 w-3 max-w-8 | Tight spaces, badges | `badge_icon` |
| sm   | h-4 w-4 max-w-8 | Table actions, small buttons | `table_action`, `button_small` |
| md   | h-5 w-5 max-w-8 | Default size, most actions | `button_normal`, `navigation`, `modal_icon`, `alert_icon` |
| lg   | h-6 w-6 max-w-8 | Primary actions, large buttons | `button_large` |
| xl   | h-8 w-8 max-w-8 | Hero sections, featured content | `hero_section` |

### Usage Examples

```php
// In table actions
Action::make('edit')
    ->icon('heroicon-o-pencil')  // Will use sm (h-4 w-4) for table_action context

// In buttons
Action::make('send')
    ->icon('heroicon-o-paper-airplane')  // Will use md (h-5 w-5) for button_normal context

// In modal icons
->modalIcon('heroicon-o-envelope')  // Will use md (h-5 w-5) for modal_icon context
```

## Modal Sizing Standards

Use `App\Helpers\FilamentHelper::getModalSizeForContext($context)` to get consistent modal sizes.

### Standard Modal Sizes

| Size | Class | Max Width | Use Case | Context |
|------|-------|-----------|----------|----------|
| sm   | sm:max-w-md | 28rem | Simple confirmations | `confirmation` |
| md   | sm:max-w-lg | 32rem | Default modal size | `simple_form` |
| lg   | sm:max-w-3xl | 48rem | Forms with many fields | `complex_form`, `efaktura_modal` |
| xl   | sm:max-w-4xl | 56rem | Complex content | `wide_content` |
| full | sm:max-w-6xl | 72rem | Maximum content | N/A |

### Usage Examples

```php
Action::make('confirm')
    ->requiresConfirmation()
    ->modalWidth(FilamentHelper::getModalSizeForContext('confirmation'))
    // Will use sm:max-w-md

Action::make('efaktura')
    ->requiresConfirmation()
    ->modalWidth(FilamentHelper::getModalSizeForContext('efaktura_modal'))
    // Will use sm:max-w-2xl (larger modal for eFaktura confirmation)
```

## Context Guidelines

### Table Actions
- Icon size: `sm` (h-4 w-4)
- Examples: Edit, Delete, Print, Download, Copy

### Button Actions
- Small buttons: `sm` (h-4 w-4)
- Normal buttons: `md` (h-5 w-5)
- Large buttons: `lg` (h-6 w-6)

### Navigation Icons
- Size: `md` (h-5 w-5)
- Examples: Sidebar navigation, top navigation

### Modal Icons
- Size: `xs` (h-3 w-3) for modal content icons
- Size: `md` (h-5 w-5) for modal header/action icons
- Examples: Confirmation modals, form modals, warning notices

### Alert Icons
- Size: `md` (h-5 w-5)
- Examples: Warning messages, success messages

### Badge Icons
- Size: `xs` (h-3 w-3)
- Examples: Status indicators, count badges

### Warning/Notice Icons
- Size: `xs` (h-3 w-3)
- Examples: Warning notices, information points, bullet points

## Implementation Rules

1. **Always use the helper methods** instead of hardcoding sizes
2. **Follow the context-based sizing** for consistency
3. **Document new contexts** in the helper class when needed
4. **Test different screen sizes** to ensure modals are responsive
5. **Use semantic context names** that describe the usage scenario
6. **All icons include max-width: 2rem (32px)** to prevent excessive width usage

## Adding New Contexts

When adding new icon or modal size contexts, update the `FilamentHelper` class:

```php
// In getIconSizeForContext method
'new_context' => 'sm',  // Choose appropriate size

// In getModalSizeForContext method
'new_modal_context' => 'lg',  // Choose appropriate size
```

Remember to document the new context in this file with use case examples.