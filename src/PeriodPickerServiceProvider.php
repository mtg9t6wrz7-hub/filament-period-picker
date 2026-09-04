<?php

declare(strict_types=1);

namespace RMRook\FilamentPeriodPicker;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class PeriodPickerServiceProvider extends PackageServiceProvider
{
    public const PACKAGE = 'rmrook/period-picker';

    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-period-picker')
            ->hasViews()
            ->hasTranslations();
    }
}
