<?php

namespace App\Helpers;

class FilamentHelper
{
    /**
     * Icon size standards for consistent sizing across the application
     */
    public const ICON_SIZES = [
        'xs' => ['class' => 'h-3 w-3 max-w-8', 'description' => 'Extra small - used in tight spaces'],
        'sm' => ['class' => 'h-4 w-4 max-w-8', 'description' => 'Small - used in table cells, modal bullet points'],
        'md' => ['class' => 'h-5 w-5 max-w-8', 'description' => 'Medium - default size for most actions, modal icons'],
        'lg' => ['class' => 'h-6 w-6 max-w-8', 'description' => 'Large - used for primary actions'],
        'xl' => ['class' => 'h-8 w-8 max-w-8', 'description' => 'Extra large - used for hero sections'],
    ];

    /**
     * Get icon size class with validation
     */
    public static function getIconSize(string $size = 'md'): string
    {
        return self::ICON_SIZES[$size]['class'] ?? self::ICON_SIZES['md']['class'];
    }

    /**
     * Modal size standards
     */
    public const MODAL_SIZES = [
        'sm' => ['class' => 'sm:max-w-md', 'description' => 'Small - for simple confirmations'],
        'md' => ['class' => 'sm:max-w-lg', 'description' => 'Medium - default modal size'],
        'lg' => ['class' => 'sm:max-w-3xl', 'description' => 'Large - for forms with many fields'],
        'xl' => ['class' => 'sm:max-w-4xl', 'description' => 'Extra large - for complex content'],
        'full' => ['class' => 'sm:max-w-6xl', 'description' => 'Full width - for maximum content'],
    ];

    /**
     * Get modal size class
     */
    public static function getModalSize(string $size = 'md'): string
    {
        return self::MODAL_SIZES[$size]['class'] ?? self::MODAL_SIZES['md']['class'];
    }

    /**
     * Standard icon sizes for different contexts
     */
    public static function getIconSizeForContext(string $context): string
    {
        return match ($context) {
            'table_action' => 'sm',     // h-4 w-4
            'button_small' => 'sm',     // h-4 w-4
            'button_normal' => 'md',    // h-5 w-5
            'button_large' => 'lg',     // h-6 w-6
            'navigation' => 'md',       // h-5 w-5
            'modal_icon' => 'xs',       // h-3 w-3 - smaller for modal content
            'modal_content' => 'xs',    // h-3 w-3 - for icons inside modal content
            'hero_section' => 'xl',     // h-8 w-8
            'alert_icon' => 'md',       // h-5 w-5
            'badge_icon' => 'xs',       // h-3 w-3
            'warning_icon' => 'xs',     // h-3 w-3 - for warning/notice icons
            default => 'md',            // h-5 w-5
        };
    }

    /**
     * Standard modal sizes for different contexts
     */
    public static function getModalSizeForContext(string $context): string
    {
        return match ($context) {
            'confirmation' => 'sm',    // sm:max-w-md
            'simple_form' => 'md',     // sm:max-w-lg
            'complex_form' => 'lg',    // sm:max-w-2xl
            'wide_content' => 'xl',    // sm:max-w-4xl
            'efaktura_modal' => 'lg',  // sm:max-w-2xl - for eFaktura confirmation
            default => 'md',           // sm:max-w-lg
        };
    }
}
