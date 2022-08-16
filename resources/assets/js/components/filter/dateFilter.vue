<template>
    <div class="calendar-root">
        <div class="form-control" @click="callToggle"> {{getDateString(dateRange.start)}} - {{getDateString(dateRange.end)}}</div>
        <div class="calendar" v-click-outside="nameOfCustomEventToCall" v-if="isOpen"
             :class="{'calendar-mobile ': isCompact, 'calendar-right-to-left': isRighttoLeft}">
            <div class="calendar-head" v-if="!isCompact">
                <br>
                <i class="la la-close hideDateRange close" @click="toggleCalendar"></i>
            </div>
            <div class="calendar-wrap">
                <div class="calendar_month_left" :class="{'calendar-left-mobile': isCompact}" v-if="showMonth">
                    <div class="months-text">
                        <i class="left" @click="goPrevMonth"></i>
                        <i class="right" @click="goNextMonth" v-if="isCompact"></i>
                        {{monthsLocale[activeMonthStart] +' '+ activeYearStart}}
                    </div>
                    <ul :class="s.daysWeeks">
                        <li v-for="item in shortDaysLocale" :key="item">{{item}}</li>
                    </ul>
                    <ul v-for="r in 6" :class="[s.days]" :key="r">
                        <li :class="[{[s.daysSelected]: isDateSelected(r, i, 'first', startMonthDay, endMonthDate),
              [s.daysInRange]: isDateInRange(r, i, 'first', startMonthDay, endMonthDate),
              [s.dateDisabled]: isDateDisabled(r, i, startMonthDay, endMonthDate)}]" v-for="i in numOfDays" :key="i"
                            v-html="getDayCell(r, i, startMonthDay, endMonthDate)"
                            @click="selectFirstItem(r, i)"></li>
                    </ul>
                </div>
                <div class="calendar_month_right" v-if="!isCompact">
                    <div class="months-text">
                        {{monthsLocale[startNextActiveMonth] +' '+ activeYearEnd}}
                        <i class="right" @click="goNextMonth"></i>
                    </div>
                    <ul :class="s.daysWeeks">
                        <li v-for="item in shortDaysLocale" :key="item">{{item}}</li>
                    </ul>
                    <ul v-for="r in 6" :class="[s.days]" :key="r">
                        <li :class="[{[s.daysSelected]: isDateSelected(r, i, 'second', startNextMonthDay, endNextMonthDate),
            [s.daysInRange]: isDateInRange(r, i, 'second', startNextMonthDay, endNextMonthDate),
            [s.dateDisabled]: isDateDisabled(r, i, startNextMonthDay, endNextMonthDate)}]"
                            v-for="i in numOfDays" :key="i"
                            v-html="getDayCell(r, i, startNextMonthDay, endNextMonthDate)"
                            @click="selectSecondItem(r, i)"></li>
                    </ul>
                </div>
            </div>
            <div class="calendar-range" :class="{'calendar-range-mobile ': isCompact}" v-if="!showMonth || !isCompact">
                <ul class="calendar_preset">
                    <li
                            class="calendar_preset-ranges"
                            v-for="(item, idx) in finalPresetRanges"
                            :key="idx"
                            @click="updatePreset(item)"
                            :class="{'active-preset': presetActive === item.label}">
                        {{item.label}}
                    </li>
                    <li>
                        <button class="btn calendar-btn-apply" @click="setDateValue()"
                                :disabled="dateRange.end == undefined">{{captions.ok_button}}
                        </button>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</template>
<!--v-click-outside="nameOfCustomEventToCall"-->
<script>
    import fecha from 'fecha';

    const defaultConfig = {}
    const defaultI18n = 'EN'
    const availableMonths = {
        EN: [window.i18n.lang.january, window.i18n.lang.february, window.i18n.lang.march, window.i18n.lang.april, window.i18n.lang.may, window.i18n.lang.june, window.i18n.lang.july, window.i18n.lang.august, window.i18n.lang.september, window.i18n.lang.october, window.i18n.lang.november,
            window.i18n.lang.december]
    }

    const availableShortDays = {
        EN: [window.i18n.lang.sun, window.i18n.lang.mon, window.i18n.lang.tue, window.i18n.lang.wed, window.i18n.lang.thu, window.i18n.lang.fri, window.i18n.lang.sat]
    }

    const presetRangeLabel = {
        EN: {
            today: window.i18n.lang.today,
            thisMonth: window.i18n.lang.this_month,
            lastMonth: window.i18n.lang.last_month,
            lastSevenDays: window.i18n.lang.last_7_days,
            lastThirtyDays: window.i18n.lang.last_30_days
        }
    }

    const defaultCaptions = {
        'title': window.i18n.lang.choose_date,
        'ok_button': window.i18n.lang.apply
    }

    const defaultStyle = {
        daysWeeks: 'calendar_weeks',
        days: 'calendar_days',
        daysSelected: 'calendar_days_selected',
        daysInRange: 'calendar_days_in-range',
        firstDate: 'calendar_month_left',
        secondDate: 'calendar_month_right',
        presetRanges: 'calendar_preset-ranges',
        dateDisabled: 'calendar_days--disabled'
    }

    const defaultPresets = function (i18n = defaultI18n) {
        return {
            today: function () {
                const n = new Date()
                const startToday = new Date(n.getFullYear(), n.getMonth(), n.getDate() + 1, 0, 0)
                const endToday = new Date(n.getFullYear(), n.getMonth(), n.getDate() + 1, 23, 59)
                return {
                    label: presetRangeLabel[i18n].today,
                    active: false,
                    dateRange: {
                        start: startToday,
                        end: endToday
                    }
                }
            },
            thisMonth: function () {
                const n = new Date()
                const startMonth = new Date(n.getFullYear(), n.getMonth(), 2)
                const endMonth = new Date(n.getFullYear(), n.getMonth() + 1, 1)
                return {
                    label: presetRangeLabel[i18n].thisMonth,
                    active: false,
                    dateRange: {
                        start: startMonth,
                        end: endMonth
                    }
                }
            },
            lastMonth: function () {
                const n = new Date()
                const startMonth = new Date(n.getFullYear(), n.getMonth() - 1, 2)
                const endMonth = new Date(n.getFullYear(), n.getMonth(), 1)
                return {
                    label: presetRangeLabel[i18n].lastMonth,
                    active: false,
                    dateRange: {
                        start: startMonth,
                        end: endMonth
                    }
                }
            },
            last7days: function () {
                const n = new Date()
                const start = new Date(n.getFullYear(), n.getMonth(), n.getDate() - 5)
                const end = new Date(n.getFullYear(), n.getMonth(), n.getDate() + 1)
                return {
                    label: presetRangeLabel[i18n].lastSevenDays,
                    active: false,
                    dateRange: {
                        start: start,
                        end: end
                    }
                }
            },
            last30days: function () {
                const n = new Date()
                const start = new Date(n.getFullYear(), n.getMonth(), n.getDate() - 29)
                const end = new Date(n.getFullYear(), n.getMonth(), n.getDate() + 1)
                return {
                    label: presetRangeLabel[i18n].lastThirtyDays,
                    active: false,
                    dateRange: {
                        start: start,
                        end: end
                    }
                }
            }
        }
    }

    export default {
        name: 'vue-rangedate-picker',
        props: {
            configs: {
                type: Object,
                default: () => defaultConfig
            },
            i18n: {
                type: String,
                default: defaultI18n
            },
            months: {
                type: Array,
                default: () => null
            },
            shortDays: {
                type: Array,
                default: () => null
            },
            captions: {
                type: Object,
                default: () => defaultCaptions
            },
            format: {
                type: String,
                default: 'YYYY-MM-DD'
            },
            styles: {
                type: Object,
                default: () => {
                }
            },
            initRange: {
                type: Object,
                default: () => null
            },
            startActiveMonth: {
                type: Number,
                default: new Date().getMonth()
            },
            startActiveYear: {
                type: Number,
                default: new Date().getFullYear()
            },
            presetRanges: {
                type: Object,
                default: () => null
            },
            compact: {
                type: String,
                default: 'false'
            },
            righttoleft: {
                type: String,
                default: 'false'
            },
            dateClear: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                dateRange: {},
                finalDateRange: {},
                numOfDays: 7,
                isFirstChoice: true,
                isOpen: false,
                presetActive: '',
                showMonth: false,
                activeMonthStart: this.startActiveMonth,
                activeYearStart: this.startActiveYear,
                activeYearEnd: this.startActiveYear,
                cancelled : true
            }
        },
        created() {
            if (this.isCompact) {
                this.isOpen = true
            }
            if (this.activeMonthStart === 11) this.activeYearEnd = this.activeYearStart + 1;
        },
        mounted() {
        },
        watch: {
            startNextActiveMonth: function (value) {
                if (value === 0) this.activeYearEnd = this.activeYearStart + 1
            },
            dateClear: function (newVal) {
                if (newVal) {
                    this.clearDateRange();
                }
                this.$emit('resetdateClears', false);
            },
        },
        computed: {
            monthsLocale: function () {
                return this.months || availableMonths[this.i18n]
            },
            shortDaysLocale: function () {
                return this.shortDays || availableShortDays[this.i18n]
            },
            s: function () {
                return Object.assign({}, defaultStyle, this.style)
            },
            startMonthDay: function () {
                return new Date(this.activeYearStart, this.activeMonthStart, 1).getDay()
            },
            startNextMonthDay: function () {
                return new Date(this.activeYearStart, this.startNextActiveMonth, 1).getDay()
            },
            endMonthDate: function () {
                return new Date(this.activeYearEnd, this.startNextActiveMonth, 0).getDate()
            },
            endNextMonthDate: function () {
                return new Date(this.activeYearEnd, this.activeMonthStart + 2, 0).getDate()
            },
            startNextActiveMonth: function () {
                return this.activeMonthStart >= 11 ? 0 : this.activeMonthStart + 1
            },
            finalPresetRanges: function () {
                const tmp = {}
                const presets = this.presetRanges || defaultPresets(this.i18n)
                for (const i in presets) {
                    const item = presets[i]
                    let plainItem = item
                    if (typeof item === 'function') {
                        plainItem = item()
                    }
                    tmp[i] = plainItem
                }
                return tmp
            },
            isCompact: function () {
                return this.compact === 'true'
            },
            isRighttoLeft: function () {
                return this.righttoleft === 'true'
            }
        },
        methods: {
            clearDateRange() {
                this.dateRange = {};
            },
            nameOfCustomEventToCall() {
                if(this.cancelled){
                    this.toggleCalendar();
                }
                this.cancelled = true;
            },
            callToggle(){
                this.cancelled = false;
                this.toggleCalendar();
            },
            toggleCalendar: function () {
                if (this.isCompact) {
                    this.showMonth = !this.showMonth
                    return
                }
                this.isOpen = !this.isOpen
                this.showMonth = !this.showMonth
                return
            },
            getDateString: function (date, format = this.format,) {
                if (!date) {
                    return null
                }
                const dateparse = new Date(Date.parse(date));
                return fecha.format(new Date(dateparse.getFullYear(), dateparse.getMonth(), dateparse.getDate() - 1), format)
            },
            getDayIndexInMonth: function (r, i, startMonthDay) {
                const date = (this.numOfDays * (r - 1)) + i
                return date - startMonthDay
            },
            getDayCell(r, i, startMonthDay, endMonthDate) {
                const result = this.getDayIndexInMonth(r, i, startMonthDay)
                return result > 0 && result <= endMonthDate ? result : '&nbsp;'
            },
            getNewDateRange(result, activeMonth, activeYear) {
                const newData = {}
                let key = 'start'
                if (!this.isFirstChoice) {
                    key = 'end'
                } else {
                    newData['end'] = null
                }
                const resultDate = new Date(activeYear, activeMonth, result)
                if (!this.isFirstChoice && resultDate < this.dateRange.start) {
                    this.isFirstChoice = false
                    return {start: resultDate}
                }
                // toggle first choice
                this.isFirstChoice = !this.isFirstChoice
                newData[key] = resultDate
                return newData
            },
            selectFirstItem(r, i) {
                const result = this.getDayIndexInMonth(r, i, this.startMonthDay) + 1
                this.dateRange = Object.assign({}, this.dateRange, this.getNewDateRange(result, this.activeMonthStart,
                    this.activeYearStart))
                if (this.dateRange.start && this.dateRange.end) {
                    this.presetActive = ''
                    if (this.isCompact) {
                        this.showMonth = false
                    }
                }
            },
            selectSecondItem(r, i) {
                const result = this.getDayIndexInMonth(r, i, this.startNextMonthDay) + 1
                this.dateRange = Object.assign({}, this.dateRange, this.getNewDateRange(result, this.startNextActiveMonth,
                    this.activeYearEnd))
                if (this.dateRange.start && this.dateRange.end) {
                    this.presetActive = ''
                }
            },
            isDateSelected(r, i, key, startMonthDay, endMonthDate) {
                const result = this.getDayIndexInMonth(r, i, startMonthDay) + 1
                if (result < 2 || result > endMonthDate + 1) return false

                let currDate = null
                if (key === 'first') {
                    currDate = new Date(this.activeYearStart, this.activeMonthStart, result)
                } else {
                    currDate = new Date(this.activeYearEnd, this.startNextActiveMonth, result)
                }
                return (this.dateRange.start && this.dateRange.start.getTime() === currDate.getTime()) ||
                    (this.dateRange.end && this.dateRange.end.getTime() === currDate.getTime())
            },
            isDateInRange(r, i, key, startMonthDay, endMonthDate) {
                const result = this.getDayIndexInMonth(r, i, startMonthDay) + 1
                if (result < 2 || result > endMonthDate + 1) return false

                let currDate = null
                if (key === 'first') {
                    currDate = new Date(this.activeYearStart, this.activeMonthStart, result)
                } else {
                    currDate = new Date(this.activeYearEnd, this.startNextActiveMonth, result)
                }
                return (this.dateRange.start && this.dateRange.start.getTime() < currDate.getTime()) &&
                    (this.dateRange.end && this.dateRange.end.getTime() > currDate.getTime())
            },
            isDateDisabled(r, i, startMonthDay, endMonthDate) {
                const result = this.getDayIndexInMonth(r, i, startMonthDay)
                // bound by > 0 and < last day of month
                return !(result > 0 && result <= endMonthDate)
            },
            goPrevMonth() {
                const prevMonth = new Date(this.activeYearStart, this.activeMonthStart, 0)
                this.activeMonthStart = prevMonth.getMonth()
                this.activeYearStart = prevMonth.getFullYear()
                this.activeYearEnd = prevMonth.getFullYear()
            },
            goNextMonth() {
                const nextMonth = new Date(this.activeYearEnd, this.startNextActiveMonth, 1)
                this.activeMonthStart = nextMonth.getMonth()
                this.activeYearStart = nextMonth.getFullYear()
                this.activeYearEnd = nextMonth.getFullYear()
            },
            updatePreset(item) {
                this.presetActive = item.label
                this.dateRange = item.dateRange
                // update start active month
                this.activeMonthStart = this.dateRange.start.getMonth()
                this.activeYearStart = this.dateRange.start.getFullYear()
                this.activeYearEnd = this.dateRange.end.getFullYear()
            },
            setDateValue: function () {
                this.finalDateRange.start = new Date(this.dateRange.start);
                this.finalDateRange.start.setDate(this.dateRange.start.getDate() - 1);
                this.finalDateRange.end = new Date(this.dateRange.end);
                this.finalDateRange.end.setDate(this.dateRange.end.getDate() - 1);
                this.finalDateRange.start = moment(this.finalDateRange.start, 'DD-MM-YYYY').format('YYYY-MM-DD');
                this.finalDateRange.end = moment(this.finalDateRange.end, 'DD-MM-YYYY').format('YYYY-MM-DD');

                this.$emit('setDatefilterValue', this.finalDateRange)
                if (!this.isCompact) {
                    this.toggleCalendar()
                }
            }
        }, directives: {
            'click-outside': {
                bind: function (el, binding, vNode) {
                    if (typeof binding.value !== 'function') {
                        const compName = vNode.context.name
                        let warn = `[Vue-click-outside:] provided expression '${binding.expression}' is not a function, but has to be`
                        if (compName) {
                            warn += `Found in component '${compName}'`
                        }

                        console.warn(warn)
                    }
                    const bubble = binding.modifiers.bubble
                    const handler = (e) => {
                        if (bubble || (!el.contains(e.target) && el !== e.target)) {
                            binding.value(e)
                        }
                    }
                    el.__vueClickOutside__ = handler
                    document.addEventListener('click', handler)
                },

                unbind: function (el, binding) {
                    document.removeEventListener('click', el.__vueClickOutside__)
                    el.__vueClickOutside__ = null

                }
            }
        }

    }

</script>

