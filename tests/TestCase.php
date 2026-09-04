<?php

declare(strict_types=1);

namespace RMRook\FilamentPeriodPicker\Tests;

use Filament\Forms\FormsServiceProvider;
use Filament\Panel;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use RMRook\FilamentPeriodPicker\PeriodPickerPlugin;
use RMRook\FilamentPeriodPicker\PeriodPickerServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $panel = Panel::make();
        PeriodPickerPlugin::make()->register($panel);
        $panel->registerAssets();
    }

    /** @return array<class-string> */
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            SchemasServiceProvider::class,
            FormsServiceProvider::class,
            PeriodPickerServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.locale', 'nl');
        $app['config']->set('app.timezone', 'Europe/Amsterdam');
    }
}
