export default function periodPickerFormComponent({
    state,
    statePath,
    locale = 'en',
    presets = [],
    firstDayOfWeek = 1,
    minDate = null,
    maxDate = null,
}) {
    return {
        state,
        statePath,
        locale: String(locale || 'en').replaceAll('_', '-'),
        presets,
        firstDayOfWeek,
        minDate,
        maxDate,
        open: false,
        draftStart: null,
        draftEnd: null,
        hoverDate: null,
        selectingEnd: false,
        visibleMonth: null,
        syncingDraftState: false,
        resizeHandler: null,

        init() {
            this.syncDraftFromState()
            this.visibleMonth = this.startOfMonth(this.draftStart || this.today())

            this.$watch('state', () => {
                if (!this.open) {
                    this.syncDraftFromState()
                }
            })

            this.$watch('state.draft_start', (value) => {
                if (!this.open || this.syncingDraftState) {
                    return
                }

                this.draftStart = this.normalizeDateValue(value)

                if (this.draftStart) {
                    this.visibleMonth = this.startOfMonth(this.draftStart)
                }

                if (this.draftStart && this.draftEnd && this.draftEnd < this.draftStart) {
                    this.draftEnd = null
                    this.state.draft_end = null
                }

                this.selectingEnd = Boolean(this.draftStart && !this.draftEnd)
            })

            this.$watch('state.draft_end', (value) => {
                if (!this.open || this.syncingDraftState) {
                    return
                }

                this.draftEnd = this.normalizeDateValue(value)

                if (this.draftStart && this.draftEnd && this.draftEnd < this.draftStart) {
                    this.draftEnd = null
                    this.state.draft_end = null
                }

                if (this.draftEnd && !this.isMonthVisible(this.draftEnd)) {
                    this.visibleMonth = this.addMonths(this.startOfMonth(this.draftEnd), -1)
                }

                this.selectingEnd = Boolean(this.draftStart && !this.draftEnd)
            })

            this.resizeHandler = () => this.syncPageScrollLock()
            window.addEventListener('resize', this.resizeHandler)
        },

        destroy() {
            window.removeEventListener('resize', this.resizeHandler)
            document.documentElement.classList.remove('rmr-period-picker-open')
        },

        show() {
            this.open = true
            this.syncDraftFromState()
            this.visibleMonth = this.startOfMonth(this.draftStart || this.today())
            this.syncDraftState()
            this.syncPageScrollLock()
            this.$nextTick(() => this.$refs.panel?.focus())
        },

        cancel() {
            this.syncDraftFromState()
            this.syncDraftState()
            this.hoverDate = null
            this.open = false
            this.syncPageScrollLock()
            this.$nextTick(() => this.$refs.trigger?.focus())
        },

        apply() {
            if (!this.canApply()) {
                return
            }

            const value = {
                start: this.draftStart,
                end: this.draftEnd,
                draft_start: this.draftStart,
                draft_end: this.draftEnd,
            }

            this.state = value
            this.open = false
            this.hoverDate = null
            this.syncPageScrollLock()
            this.$wire.$set(this.statePath, value, true)
            this.$nextTick(() => this.$refs.trigger?.focus())
        },

        syncPageScrollLock() {
            document.documentElement.classList.toggle(
                'rmr-period-picker-open',
                this.open && window.matchMedia('(max-width: 767px)').matches,
            )
        },

        syncDraftFromState() {
            const value = this.normalizedState()

            this.draftStart = value?.start || null
            this.draftEnd = value?.end || null
            this.selectingEnd = Boolean(this.draftStart && !this.draftEnd)
        },

        syncDraftState() {
            if (!this.state || typeof this.state !== 'object') {
                this.state = {}
            }

            this.syncingDraftState = true
            this.state.draft_start = this.draftStart
            this.state.draft_end = this.draftEnd
            this.$nextTick(() => (this.syncingDraftState = false))
        },

        normalizedState() {
            if (!this.state || typeof this.state !== 'object') {
                return null
            }

            const start = this.normalizeDateValue(this.state.start)
            const end = this.normalizeDateValue(this.state.end)

            return start && end && start <= end ? { start, end } : null
        },

        choosePreset(preset) {
            this.draftStart = this.normalizeDateValue(preset.start)
            this.draftEnd = this.normalizeDateValue(preset.end)
            this.visibleMonth = this.startOfMonth(this.draftStart || this.today())
            this.selectingEnd = false
            this.hoverDate = null
            this.syncDraftState()
        },

        chooseDate(value) {
            if (this.isDisabled(value)) {
                return
            }

            if (!this.draftStart || this.draftEnd || !this.selectingEnd) {
                this.draftStart = value
                this.draftEnd = null
                this.selectingEnd = true
                this.hoverDate = null
                this.syncDraftState()

                return
            }

            if (value < this.draftStart) {
                this.draftStart = value
                this.draftEnd = null
                this.hoverDate = null
                this.syncDraftState()

                return
            }

            this.draftEnd = value
            this.selectingEnd = false
            this.hoverDate = null
            this.syncDraftState()
        },

        canApply() {
            return this.validDateValue(this.draftStart)
                && this.validDateValue(this.draftEnd)
                && this.draftStart <= this.draftEnd
                && !this.isDisabled(this.draftStart)
                && !this.isDisabled(this.draftEnd)
        },

        isDisabled(value) {
            return !this.validDateValue(value)
                || (this.minDate && value < this.minDate)
                || (this.maxDate && value > this.maxDate)
        },

        isPresetSelected(preset) {
            return this.draftStart === preset.start && this.draftEnd === preset.end
        },

        isRangeStart(value) {
            return value === this.draftStart
        },

        isRangeEnd(value) {
            return value === this.draftEnd
        },

        isInRange(value) {
            const previewEnd = this.draftEnd
                || (this.selectingEnd && this.hoverDate >= this.draftStart ? this.hoverDate : null)

            return Boolean(this.draftStart && previewEnd && value > this.draftStart && value < previewEnd)
        },

        isToday(value) {
            return value === this.today()
        },

        shiftMonth(amount) {
            this.visibleMonth = this.addMonths(this.visibleMonth, amount)
        },

        secondMonth() {
            return this.addMonths(this.visibleMonth, 1)
        },

        isMonthVisible(value) {
            const month = this.startOfMonth(value)

            return month === this.visibleMonth || month === this.secondMonth()
        },

        calendarDays(monthValue) {
            const first = this.parseDate(monthValue)
            const offset = (first.getDay() - this.firstDayOfWeek + 7) % 7
            const gridStart = new Date(first.getFullYear(), first.getMonth(), 1 - offset, 12)

            return Array.from({ length: 42 }, (_, index) => {
                const date = new Date(gridStart)
                date.setDate(gridStart.getDate() + index)

                return {
                    value: this.toDateValue(date),
                    day: date.getDate(),
                    currentMonth: date.getMonth() === first.getMonth(),
                }
            })
        },

        weekdayLabels() {
            const sunday = new Date(2024, 0, 7, 12)

            return Array.from({ length: 7 }, (_, index) => {
                const date = new Date(sunday)
                date.setDate(sunday.getDate() + ((this.firstDayOfWeek + index) % 7))

                return new Intl.DateTimeFormat(this.locale, { weekday: 'narrow' }).format(date)
            })
        },

        monthLabel(value) {
            return new Intl.DateTimeFormat(this.locale, {
                month: 'long',
                year: 'numeric',
            }).format(this.parseDate(value))
        },

        displayText() {
            const value = this.normalizedState()

            if (!value) {
                return ''
            }

            const formatter = new Intl.DateTimeFormat(this.locale, {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
            })

            return `${formatter.format(this.parseDate(value.start))} – ${formatter.format(this.parseDate(value.end))}`
        },

        normalizeDateValue(value) {
            const dateValue = String(value || '').slice(0, 10)

            return this.validDateValue(dateValue) ? dateValue : null
        },

        today() {
            return this.toDateValue(new Date())
        },

        startOfMonth(value) {
            const date = this.parseDate(value)

            return this.toDateValue(new Date(date.getFullYear(), date.getMonth(), 1, 12))
        },

        addMonths(value, amount) {
            const date = this.parseDate(value || this.today())

            return this.toDateValue(new Date(date.getFullYear(), date.getMonth() + amount, 1, 12))
        },

        parseDate(value) {
            const [year, month, day] = String(value).split('-').map(Number)

            return new Date(year, month - 1, day, 12)
        },

        toDateValue(date) {
            const year = date.getFullYear()
            const month = String(date.getMonth() + 1).padStart(2, '0')
            const day = String(date.getDate()).padStart(2, '0')

            return `${year}-${month}-${day}`
        },

        validDateValue(value) {
            if (!/^\d{4}-\d{2}-\d{2}$/.test(value || '')) {
                return false
            }

            const date = this.parseDate(value)

            return !Number.isNaN(date.getTime()) && this.toDateValue(date) === value
        },
    }
}
