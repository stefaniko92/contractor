<?php

namespace App\Filament\Forms\Components;
use Closure;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Concerns;
use Filament\Forms\Components\Select;

class TextInputSelectAffix extends Field
{
    use Concerns\CanBeAutocapitalized;
    use Concerns\CanBeAutocompleted;
    use Concerns\HasExtraInputAttributes;
    use Concerns\HasInputMode;
    use Concerns\HasPlaceholder;

//    protected string $view = 'filament-input-select-affix::components.text-input-select-affix';
    protected string $view = 'forms.components.combined-input-select';

    protected string | Closure | null $type = 'text';

    protected Field | Closure | null $selectComponent = null;

    protected string | Closure | null $selectPosition = 'after';

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule('string');
    }

    public function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'isSelectBefore' => $this->isSelectBefore(),
            'isSelectAfter' => $this->isSelectAfter(),
            'getSelectComponent' => $this->getSelectComponent(),
        ]);
    }

    public function email(): static
    {
        $this->type('email');
        $this->rule('email');

        return $this;
    }

    public function numeric(): static
    {
        $this->type('number');
        $this->rule('numeric');

        return $this;
    }

    public function password(): static
    {
        $this->type('password');

        return $this;
    }

    public function tel(): static
    {
        $this->type('tel');

        return $this;
    }

    public function url(): static
    {
        $this->type('url');
        $this->rule('url');

        return $this;
    }

    public function type(string | Closure | null $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function select(Field | Closure $component): static
    {
        $this->selectComponent = $component;

        return $this;
    }

    public function selectPosition(string | Closure $position): static
    {
        $this->selectPosition = $position;

        return $this;
    }

    public function getType(): string
    {
        return $this->evaluate($this->type) ?? 'text';
    }

    public function getSelectComponent(): ?Field
    {
        if (!$this->selectComponent) {
            return null;
        }

        $component = $this->evaluate($this->selectComponent);

        if ($component instanceof Closure) {
            $component = $component();
        }

        if ($component instanceof Field) {
            // Ensure the select component has a proper name/statePath
            $selectName = $this->getSelectName();
            
            // Configure the component properly
            $component->statePath($selectName);
            $component->container($this->getContainer());
            
            // Add CSS classes for seamless integration
            $component->extraAttributes([
                'class' => 'border-l-0 rounded-l-none focus:z-10'
            ], merge: true);
        }

        return $component;
    }

    public function getSelectName(): string
    {
        return $this->getName() . '_unit';
    }

    public function getSelectPosition(): string
    {
        return $this->evaluate($this->selectPosition) ?? 'after';
    }

    public function isSelectBefore(): bool
    {
        return $this->getSelectPosition() === 'before';
    }

    public function isSelectAfter(): bool
    {
        return $this->getSelectPosition() === 'after';
    }

    // Convenience methods for quick setup
    public static function makeWithSelect(string $name, string $selectName = null): static
    {
        $selectName = $selectName ?? $name . '_type';
        
        return static::make($name)
            ->select(
                Select::make($selectName)
                    ->hiddenLabel()
                    ->options([
                        'percent' => '%',
                        'fixed' => 'RSD',
                    ])
                    ->default('percent')
            );
    }

    public function withDiscountOptions(array $options = null): static
    {
        $options = $options ?? [
            'percent' => '%',
            'fixed' => '$',
        ];

        $selectComponent = Select::make($this->getSelectName())
            ->hiddenLabel()
            ->options($options)
            ->default(array_key_first($options))
            ->live();

        return $this->select($selectComponent);
    }

    public function step(float | string | Closure | null $step): static
    {
        return $this->extraInputAttributes(['step' => $step]);
    }

    public function isConcealed(): bool
    {
        return false; // This component is never concealed like a password field
    }

    // Helper methods for the view
    public function getSelectOptions(): array
    {
        $component = $this->getSelectComponent();
        return $component ? $component->getOptions() : [];
    }

    public function getSelectState()
    {
        $component = $this->getSelectComponent();
        return $component ? $component->getState() : null;
    }

    public function getSelectStatePath(): string
    {
        $component = $this->getSelectComponent();
        return $component ? $component->getStatePath() : '';
    }
}