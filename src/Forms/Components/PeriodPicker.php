<?php

declare(strict_types=1);

namespace Rmr\FilamentPeriodPicker\Forms\Components;

use Carbon\CarbonImmutable;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Field;
use Throwable;

class PeriodPicker extends Field
{
    /** @var view-string */
    protected string $view = 'filament-period-picker::forms.components.period-picker';

    protected int|Closure $firstDayOfWeek = 1;

    protected string|Closure|null $locale = null;

    protected CarbonImmutable|string|Closure|null $maxDate = null;

    protected CarbonImmutable|string|Closure|null $minDate = null;

    /**
     * @var array<int, array{key: string, label: string, start: string, end: string}>|Closure|null
     */
    protected array|Closure|null $presets = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(static function (PeriodPicker $component, mixed $state): void {
            $component->state($component->normalizeStateWithDraft($state));
        });

        $this->dehydrateStateUsing(fn (mixed $state): ?array => $this->normalizeState($state));

        $this->columns([
            'default' => 1,
            'sm' => 2,
        ]);

        $this->components(static fn (PeriodPicker $component): array => [
            DatePicker::make('draft_start')
                ->label(__('filament-period-picker::period-picker.start_date'))
                ->native(false)
                ->closeOnDateSelection()
                ->displayFormat('d M Y')
                ->format('Y-m-d')
                ->locale($component->getLocale())
                ->firstDayOfWeek($component->getFirstDayOfWeek())
                ->minDate($component->getMinDate())
                ->maxDate($component->getMaxDate())
                ->dehydrated(false),
            DatePicker::make('draft_end')
                ->label(__('filament-period-picker::period-picker.end_date'))
                ->native(false)
                ->closeOnDateSelection()
                ->displayFormat('d M Y')
                ->format('Y-m-d')
                ->locale($component->getLocale())
                ->firstDayOfWeek($component->getFirstDayOfWeek())
                ->minDate($component->getMinDate())
                ->maxDate($component->getMaxDate())
                ->dehydrated(false),
        ]);
    }

    public function firstDayOfWeek(int|Closure $day): static
    {
        $this->firstDayOfWeek = $day;

        return $this;
    }

    public function getFirstDayOfWeek(): int
    {
        $day = (int) $this->evaluate($this->firstDayOfWeek);

        return in_array($day, range(0, 6), true) ? $day : 1;
    }

    public function locale(string|Closure|null $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): string
    {
        return (string) ($this->evaluate($this->locale) ?: app()->getLocale());
    }

    public function maxDate(CarbonImmutable|string|Closure|null $date): static
    {
        $this->maxDate = $date;

        return $this;
    }

    public function getMaxDate(): ?string
    {
        return $this->normalizeDate($this->evaluate($this->maxDate));
    }

    public function minDate(CarbonImmutable|string|Closure|null $date): static
    {
        $this->minDate = $date;

        return $this;
    }

    public function getMinDate(): ?string
    {
        return $this->normalizeDate($this->evaluate($this->minDate));
    }

    /**
     * @param  array<int, array{key: string, label: string, start: string, end: string}>|Closure|null  $presets
     */
    public function presets(array|Closure|null $presets): static
    {
        $this->presets = $presets;

        return $this;
    }

    /**
     * @return array<int, array{key: string, label: string, start: string, end: string}>
     */
    public function getPresets(): array
    {
        $presets = $this->evaluate($this->presets) ?? $this->getDefaultPresets();

        return collect($presets)
            ->map(function (array $preset): ?array {
                $start = $this->normalizeDate($preset['start'] ?? null);
                $end = $this->normalizeDate($preset['end'] ?? null);

                if ((! $start) || (! $end) || ($start > $end)) {
                    return null;
                }

                return [
                    'key' => (string) ($preset['key'] ?? $start.'_'.$end),
                    'label' => (string) ($preset['label'] ?? ''),
                    'start' => $start,
                    'end' => $end,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{key: string, label: string, start: string, end: string}>
     */
    public function getDefaultPresets(): array
    {
        $now = CarbonImmutable::now(config('app.user_timezone', config('app.timezone')));
        $previousQuarter = $now->subQuarter();

        return [
            $this->preset('this_year', __('filament-period-picker::period-picker.presets.this_year'), $now->startOfYear(), $now->endOfYear()),
            $this->preset('last_year', __('filament-period-picker::period-picker.presets.last_year'), $now->subYear()->startOfYear(), $now->subYear()->endOfYear()),
            $this->preset('this_quarter', __('filament-period-picker::period-picker.presets.this_quarter'), $now->startOfQuarter(), $now->endOfQuarter()),
            $this->preset('last_quarter', __('filament-period-picker::period-picker.presets.last_quarter'), $previousQuarter->startOfQuarter(), $previousQuarter->endOfQuarter()),
            $this->preset('this_month', __('filament-period-picker::period-picker.presets.this_month'), $now->startOfMonth(), $now->endOfMonth()),
            $this->preset('last_month', __('filament-period-picker::period-picker.presets.last_month'), $now->subMonth()->startOfMonth(), $now->subMonth()->endOfMonth()),
            $this->preset('last_12_months', __('filament-period-picker::period-picker.presets.last_12_months'), $now->subYear()->addDay(), $now),
        ];
    }

    /**
     * @return array{key: string, label: string, start: string, end: string}
     */
    protected function preset(string $key, string $label, CarbonImmutable $start, CarbonImmutable $end): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
        ];
    }

    /** @return array{start: string, end: string}|null */
    protected function normalizeState(mixed $state): ?array
    {
        if (! is_array($state)) {
            return null;
        }

        $start = $this->normalizeDate($state['start'] ?? null);
        $end = $this->normalizeDate($state['end'] ?? null);

        if ((! $start) || (! $end) || ($start > $end)) {
            return null;
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    /** @return array{start: string, end: string, draft_start: string, draft_end: string}|null */
    protected function normalizeStateWithDraft(mixed $state): ?array
    {
        $normalizedState = $this->normalizeState($state);

        if (! $normalizedState) {
            return null;
        }

        return [
            ...$normalizedState,
            'draft_start' => $this->normalizeDate($state['draft_start'] ?? null) ?? $normalizedState['start'],
            'draft_end' => $this->normalizeDate($state['draft_end'] ?? null) ?? $normalizedState['end'],
        ];
    }

    protected function normalizeDate(mixed $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        try {
            return CarbonImmutable::parse($date)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
