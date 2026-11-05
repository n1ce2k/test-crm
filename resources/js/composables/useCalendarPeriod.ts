import { computed, ref, type Ref } from 'vue';
import {
    addDays,
    addMonths,
    eachDay,
    endOfMonth,
    isWeekend,
    monthLabel,
    parseDate,
    startOfMonth,
    startOfWeek,
    toDateString,
    weekLabel,
    weekdayShort,
} from '@/utils/date';
import type { CalendarMode } from '@/types';

export interface CalendarDay {
    date: string;
    day: number;
    weekday: string;
    isWeekend: boolean;
    isToday: boolean;
    isAvailable: boolean;
}

export function useCalendarPeriod(periodStart: Ref<string>, periodEnd: Ref<string>) {
    const mode = ref<CalendarMode>('week');
    const anchor = ref<Date>(initialAnchor());

    function initialAnchor(): Date {
        const today = toDateString(new Date());

        if (periodStart.value && today >= periodStart.value && today <= periodEnd.value) {
            return parseDate(today);
        }

        return periodStart.value ? parseDate(periodStart.value) : new Date();
    }

    const range = computed(() => {
        if (mode.value === 'month') {
            return { from: startOfMonth(anchor.value), to: endOfMonth(anchor.value) };
        }

        const from = startOfWeek(anchor.value);

        return { from, to: addDays(from, 6) };
    });

    const from = computed(() => toDateString(range.value.from));
    const to = computed(() => toDateString(range.value.to));

    const days = computed<CalendarDay[]>(() => {
        const today = toDateString(new Date());

        return eachDay(range.value.from, range.value.to).map((date) => {
            const value = toDateString(date);

            return {
                date: value,
                day: date.getDate(),
                weekday: weekdayShort(date),
                isWeekend: isWeekend(date),
                isToday: value === today,
                isAvailable: value >= periodStart.value && value <= periodEnd.value,
            };
        });
    });

    const label = computed(() =>
        mode.value === 'month' ? monthLabel(range.value.from) : weekLabel(range.value.from, range.value.to),
    );

    const hasAvailableDays = computed(() => days.value.some((day) => day.isAvailable));

    function shift(direction: 1 | -1): void {
        anchor.value = mode.value === 'month'
            ? addMonths(anchor.value, direction)
            : addDays(startOfWeek(anchor.value), direction * 7);
    }

    function setMode(value: CalendarMode): void {
        if (value === mode.value) {
            return;
        }

        if (value === 'week' && mode.value === 'month') {
            const firstAvailable = days.value.find((day) => day.isAvailable);
            anchor.value = firstAvailable ? parseDate(firstAvailable.date) : range.value.from;
        }

        mode.value = value;
    }

    function goToPeriod(): void {
        anchor.value = initialAnchor();
    }

    return {
        mode,
        from,
        to,
        days,
        label,
        hasAvailableDays,
        next: () => shift(1),
        prev: () => shift(-1),
        setMode,
        goToPeriod,
    };
}
