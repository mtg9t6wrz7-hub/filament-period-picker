<?php

declare(strict_types=1);

namespace Rmr\FilamentPeriodPicker\Tests;

use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Rmr\FilamentPeriodPicker\Forms\Components\PeriodPicker;
use Rmr\FilamentPeriodPicker\PeriodPickerPlugin;

final class PeriodPickerTest extends TestCase
{
    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_it_is_a_filament_plugin(): void
    {
        $this->assertSame('rmr-period-picker', PeriodPickerPlugin::make()->getId());
    }

    public function test_it_provides_default_period_presets(): void
    {
        CarbonImmutable::setTestNow('2026-09-03 12:00:00');

        $presets = collect(PeriodPicker::make('period')->getDefaultPresets())->keyBy('key');

        $this->assertSame('2026-01-01', $presets['this_year']['start']);
        $this->assertSame('2026-12-31', $presets['this_year']['end']);
        $this->assertSame('2025-01-01', $presets['last_year']['start']);
        $this->assertSame('2025-12-31', $presets['last_year']['end']);
        $this->assertSame('2026-07-01', $presets['this_quarter']['start']);
        $this->assertSame('2026-09-30', $presets['this_quarter']['end']);
        $this->assertSame('2026-06-30', $presets['last_quarter']['end']);
        $this->assertSame('2026-08-01', $presets['last_month']['start']);
        $this->assertSame('2026-08-31', $presets['last_month']['end']);
        $this->assertSame('2025-09-04', $presets['last_12_months']['start']);
        $this->assertSame('2026-09-03', $presets['last_12_months']['end']);
    }

    public function test_it_accepts_custom_presets_and_rejects_invalid_ranges(): void
    {
        $presets = PeriodPicker::make('period')
            ->presets([
                [
                    'key' => 'valid',
                    'label' => 'Valid',
                    'start' => '2026-01-01',
                    'end' => '2026-01-31',
                ],
                [
                    'key' => 'invalid',
                    'label' => 'Invalid',
                    'start' => '2026-02-01',
                    'end' => '2026-01-01',
                ],
            ])
            ->getPresets();

        $this->assertCount(1, $presets);
        $this->assertSame('valid', $presets[0]['key']);
    }

    public function test_all_translations_contain_the_english_translation_keys(): void
    {
        $languageDirectory = __DIR__.'/../resources/lang';
        $englishKeys = array_keys(Arr::dot(require $languageDirectory.'/en/period-picker.php'));

        foreach (['de', 'el', 'es', 'fr', 'it', 'ja', 'nl'] as $locale) {
            $translationKeys = array_keys(Arr::dot(require "{$languageDirectory}/{$locale}/period-picker.php"));

            $this->assertSame($englishKeys, $translationKeys, "The [{$locale}] translations do not match the English translation keys.");
        }
    }

    public function test_it_ships_a_reusable_filament_field_view(): void
    {
        $component = PeriodPicker::make('period');
        $view = file_get_contents(__DIR__.'/../resources/views/forms/components/period-picker.blade.php');

        $this->assertSame('filament-period-picker::forms.components.period-picker', $component->getView());
        $this->assertTrue(view()->exists($component->getView()));
        $this->assertIsString($view);
        $this->assertStringContainsString('periodPickerFormComponent', $view);
        $this->assertStringContainsString('rmr-period-picker__panel', $view);
        $this->assertStringContainsString('rmr-period-picker__date-inputs', $view);
        $this->assertStringContainsString('rmr-period-picker__calendars', $view);
        $this->assertStringContainsString('rmr-period-picker__footer', $view);
        $this->assertStringContainsString('aria-modal="true"', $view);
        $this->assertStringContainsString('x-trap="open"', $view);
        $this->assertStringContainsString('calendarDays(visibleMonth)', $view);
        $this->assertStringContainsString('chooseDate(day.value)', $view);
        $this->assertStringNotContainsString('type="date"', $view);

        $script = file_get_contents(__DIR__.'/../resources/js/period-picker.js');

        $this->assertIsString($script);
        $this->assertStringContainsString("replaceAll('_', '-')", $script);
    }

    public function test_it_ships_a_mobile_viewport_safe_layout(): void
    {
        $styles = file_get_contents(__DIR__.'/../resources/css/period-picker.css');

        $this->assertIsString($styles);
        $this->assertStringContainsString('@media (max-width: 767px)', $styles);
        $this->assertStringContainsString('position: fixed !important', $styles);
        $this->assertStringContainsString('height: 100dvh', $styles);
        $this->assertStringContainsString('overflow-x: hidden', $styles);
        $this->assertStringContainsString('overflow-y: auto', $styles);
        $this->assertStringContainsString('safe-area-inset-bottom', $styles);
        $this->assertStringContainsString('@media (max-width: 359px)', $styles);
    }
}
