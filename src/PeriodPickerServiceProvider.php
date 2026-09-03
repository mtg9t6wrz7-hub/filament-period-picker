<?php

declare(strict_types=1);

namespace Rmr\FilamentPeriodPicker;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class PeriodPickerServiceProvider extends PackageServiceProvider
{
    public const PACKAGE = 'rmr/period-picker';

    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-period-picker')
            ->hasViews()
            ->hasTranslations();
    }
}
