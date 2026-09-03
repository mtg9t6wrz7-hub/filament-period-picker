# Filament Period Picker

A reusable Filament 4 form field for selecting a date range. It combines quick presets, two synchronized Filament date inputs, and a shared range calendar in one responsive picker.

The package ships its own lazy-loaded JavaScript, CSS, views, and English and Dutch translations. A consuming application does not need to add Tailwind `@source` directives or register assets manually.

## Requirements

- PHP 8.2 or newer
- Laravel 11.28, 12, or 13
- Filament 4

## Installation

Install the Composer package:

```bash
composer require rmr/period-picker
```

Register the plugin in every Filament panel where the field is used:

```php
use Rmr\FilamentPeriodPicker\PeriodPickerPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(PeriodPickerPlugin::make());
}
```

Publish Filament's registered assets after installing or updating the package:

```bash
php artisan filament:assets
```

## Basic usage

```php
use Rmr\FilamentPeriodPicker\Forms\Components\PeriodPicker;

PeriodPicker::make('period')
    ->label('Periode')
    ->default([
        'start' => now()->startOfYear()->toDateString(),
        'end' => now()->endOfYear()->toDateString(),
    ]);
```

The dehydrated value is an array containing ISO dates:

```php
[
    'start' => '2026-01-01',
    'end' => '2026-12-31',
]
```

## Options

```php
PeriodPicker::make('period')
    ->locale('nl')
    ->firstDayOfWeek(1)
    ->minDate('2025-01-01')
    ->maxDate('2027-12-31')
    ->presets([
        [
            'key' => 'campaign',
            'label' => 'Campagneperiode',
            'start' => '2026-09-01',
            'end' => '2026-10-15',
        ],
    ]);
```

`locale()`, `firstDayOfWeek()`, `minDate()`, `maxDate()`, and `presets()` also accept closures. Invalid preset ranges are ignored.

Without custom presets, the picker provides:

- This year and last year
- This quarter and last quarter
- This month and last month
- Last 12 months

## Translations

The picker follows the Laravel application locale and includes English (`en`), Dutch (`nl`), French (`fr`), Italian (`it`), German (`de`), Spanish (`es`), Greek (`el`), and Japanese (`ja`). To customize its copy, publish the package translations:

```bash
php artisan vendor:publish --tag=filament-period-picker-translations
```

## Testing

```bash
composer test
```

## License

MIT
