<?php

declare(strict_types=1);

namespace RMRook\FilamentPeriodPicker;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;

final class PeriodPickerPlugin implements Plugin
{
    public static function make(): self
    {
        return new self;
    }

    public function getId(): string
    {
        return 'rmr-period-picker';
    }

    public function register(Panel $panel): void
    {
        $panel->assets([
            AlpineComponent::make(
                'period-picker',
                __DIR__.'/../resources/js/period-picker.js',
            )->loadedOnRequest(),
            Css::make(
                'period-picker',
                __DIR__.'/../resources/css/period-picker.css',
            )->loadedOnRequest(),
        ], package: PeriodPickerServiceProvider::PACKAGE);
    }

    public function boot(Panel $panel): void
    {
        // No panel-specific bootstrapping is required.
    }
}
