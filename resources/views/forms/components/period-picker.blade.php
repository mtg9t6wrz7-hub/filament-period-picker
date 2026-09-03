@php
    $fieldWrapperView = $getFieldWrapperView();
    $id = $getId();
    $isDisabled = $isDisabled();
    $statePath = $getStatePath();
    $livewireKey = $getLivewireKey();
    $translation = 'filament-period-picker::period-picker.';
@endphp

<x-dynamic-component
    :component="$fieldWrapperView"
    :field="$field"
>
    <div
        x-load
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('period-picker', package: \Rmr\FilamentPeriodPicker\PeriodPickerServiceProvider::PACKAGE) }}"
        x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('period-picker', package: \Rmr\FilamentPeriodPicker\PeriodPickerServiceProvider::PACKAGE))]"
        x-data="periodPickerFormComponent({
            state: $wire.$entangle(@js($statePath)),
            statePath: @js($statePath),
            locale: @js($getLocale()),
            presets: @js($getPresets()),
            firstDayOfWeek: @js($getFirstDayOfWeek()),
            minDate: @js($getMinDate()),
            maxDate: @js($getMaxDate()),
        })"
        x-on:keydown.escape.window="open && cancel()"
        x-on:click.outside="open && cancel()"
        wire:key="{{ $livewireKey }}"
        class="rmr-period-picker"
    >
        <x-filament::input.wrapper
            :disabled="$isDisabled"
            :valid="! $errors->has($statePath)"
            :attributes="\Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())->class(['rmr-period-picker__input-wrapper'])"
        >
            <button
                x-ref="trigger"
                x-on:click="open ? cancel() : show()"
                x-bind:aria-expanded="open"
                aria-controls="{{ $id }}-panel"
                id="{{ $id }}"
                type="button"
                @disabled($isDisabled)
                class="fi-input rmr-period-picker__trigger"
            >
                <span class="rmr-period-picker__trigger-icon">
                    <x-filament::icon icon="heroicon-m-calendar-days" />
                </span>
                <span
                    x-text="displayText() || @js(__($translation.'placeholder'))"
                    x-bind:class="displayText() ? 'has-value' : 'is-placeholder'"
                    class="rmr-period-picker__trigger-text"
                ></span>
                <span class="rmr-period-picker__trigger-chevron">
                    <x-filament::icon
                        icon="heroicon-m-chevron-down"
                        x-bind:class="{ 'is-open': open }"
                    />
                </span>
            </button>
        </x-filament::input.wrapper>

        <div
            x-ref="panel"
            x-show="open"
            x-cloak
            x-float.placement.bottom-start.offset.flip.shift="{ offset: 8 }"
            x-trap="open"
            x-transition.opacity.duration.150ms
            id="{{ $id }}-panel"
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $id }}-panel-title"
            tabindex="-1"
            class="rmr-period-picker__panel"
        >
            <h2 id="{{ $id }}-panel-title" class="rmr-period-picker__sr-only">
                {{ __($translation.'custom_period') }}
            </h2>

            <div class="rmr-period-picker__mobile-header">
                <p>{{ __($translation.'custom_period') }}</p>
                <button
                    x-on:click="cancel()"
                    type="button"
                    aria-label="{{ __($translation.'cancel') }}"
                    class="rmr-period-picker__icon-button rmr-period-picker__mobile-close"
                >
                    <x-filament::icon icon="heroicon-m-x-mark" />
                </button>
            </div>

            <div class="rmr-period-picker__layout">
                <aside class="rmr-period-picker__sidebar">
                    <p class="rmr-period-picker__eyebrow">
                        {{ __($translation.'quick_selection') }}
                    </p>
                    <div class="rmr-period-picker__presets">
                        <template x-for="preset in presets" x-bind:key="preset.key">
                            <button
                                x-on:click="choosePreset(preset)"
                                x-bind:class="{ 'is-selected': isPresetSelected(preset) }"
                                type="button"
                                class="rmr-period-picker__preset"
                                x-text="preset.label"
                            ></button>
                        </template>
                    </div>
                </aside>

                <section class="rmr-period-picker__content">
                    <header class="rmr-period-picker__intro">
                        <p class="rmr-period-picker__title">
                            {{ __($translation.'custom_period') }}
                        </p>
                        <p class="rmr-period-picker__description">
                            {{ __($translation.'custom_period_description') }}
                        </p>
                    </header>

                    <div class="rmr-period-picker__date-inputs">
                        {{ $getChildSchema() }}
                    </div>

                    <div class="rmr-period-picker__calendars">
                        <div class="rmr-period-picker__calendar">
                            <div class="rmr-period-picker__calendar-header">
                                <button
                                    x-on:click="shiftMonth(-1)"
                                    type="button"
                                    aria-label="{{ __($translation.'previous_month') }}"
                                    class="rmr-period-picker__icon-button"
                                >
                                    <x-filament::icon icon="heroicon-m-chevron-left" />
                                </button>
                                <p x-text="monthLabel(visibleMonth)"></p>
                                <button
                                    x-on:click="shiftMonth(1)"
                                    type="button"
                                    aria-label="{{ __($translation.'next_month') }}"
                                    class="rmr-period-picker__icon-button rmr-period-picker__next-mobile"
                                >
                                    <x-filament::icon icon="heroicon-m-chevron-right" />
                                </button>
                            </div>

                            <div class="rmr-period-picker__weekdays" aria-hidden="true">
                                <template x-for="(label, index) in weekdayLabels()" x-bind:key="index">
                                    <span x-text="label"></span>
                                </template>
                            </div>
                            <div class="rmr-period-picker__days" role="grid">
                                <template x-for="day in calendarDays(visibleMonth)" x-bind:key="day.value">
                                    <button
                                        x-on:click="chooseDate(day.value)"
                                        x-on:mouseenter="hoverDate = day.value"
                                        x-on:mouseleave="hoverDate = null"
                                        x-bind:disabled="isDisabled(day.value)"
                                        x-bind:aria-label="day.value"
                                        x-bind:aria-selected="isRangeStart(day.value) || isRangeEnd(day.value)"
                                        x-bind:class="{
                                            'is-range-edge': isRangeStart(day.value) || isRangeEnd(day.value),
                                            'is-in-range': isInRange(day.value),
                                            'is-current-month': day.currentMonth,
                                            'is-outside-month': !day.currentMonth,
                                            'is-today': isToday(day.value),
                                            'is-disabled': isDisabled(day.value),
                                        }"
                                        type="button"
                                        role="gridcell"
                                        class="rmr-period-picker__day"
                                        x-text="day.day"
                                    ></button>
                                </template>
                            </div>
                        </div>

                        <div class="rmr-period-picker__calendar rmr-period-picker__calendar--second">
                            <div class="rmr-period-picker__calendar-header">
                                <span class="rmr-period-picker__nav-placeholder"></span>
                                <p x-text="monthLabel(secondMonth())"></p>
                                <button
                                    x-on:click="shiftMonth(1)"
                                    type="button"
                                    aria-label="{{ __($translation.'next_month') }}"
                                    class="rmr-period-picker__icon-button"
                                >
                                    <x-filament::icon icon="heroicon-m-chevron-right" />
                                </button>
                            </div>

                            <div class="rmr-period-picker__weekdays" aria-hidden="true">
                                <template x-for="(label, index) in weekdayLabels()" x-bind:key="index">
                                    <span x-text="label"></span>
                                </template>
                            </div>
                            <div class="rmr-period-picker__days" role="grid">
                                <template x-for="day in calendarDays(secondMonth())" x-bind:key="day.value">
                                    <button
                                        x-on:click="chooseDate(day.value)"
                                        x-on:mouseenter="hoverDate = day.value"
                                        x-on:mouseleave="hoverDate = null"
                                        x-bind:disabled="isDisabled(day.value)"
                                        x-bind:aria-label="day.value"
                                        x-bind:aria-selected="isRangeStart(day.value) || isRangeEnd(day.value)"
                                        x-bind:class="{
                                            'is-range-edge': isRangeStart(day.value) || isRangeEnd(day.value),
                                            'is-in-range': isInRange(day.value),
                                            'is-current-month': day.currentMonth,
                                            'is-outside-month': !day.currentMonth,
                                            'is-today': isToday(day.value),
                                            'is-disabled': isDisabled(day.value),
                                        }"
                                        type="button"
                                        role="gridcell"
                                        class="rmr-period-picker__day"
                                        x-text="day.day"
                                    ></button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <footer class="rmr-period-picker__footer">
                        <p>{{ __($translation.'selection_hint') }}</p>
                        <div class="rmr-period-picker__actions">
                            <x-filament::button x-on:click="cancel()" type="button" color="gray" size="sm" outlined>
                                {{ __($translation.'cancel') }}
                            </x-filament::button>
                            <x-filament::button x-on:click="apply()" x-bind:disabled="!canApply()" type="button" size="sm">
                                {{ __($translation.'apply') }}
                            </x-filament::button>
                        </div>
                    </footer>
                </section>
            </div>
        </div>
    </div>
</x-dynamic-component>
