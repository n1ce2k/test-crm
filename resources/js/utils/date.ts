const WEEKDAYS_SHORT = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];

const MONTHS = [
    'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
    'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь',
];

const MONTHS_GENITIVE = [
    'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
    'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря',
];

export function parseDate(value: string): Date {
    const [year, month, day] = value.slice(0, 10).split('-').map(Number);

    return new Date(year, month - 1, day);
}

export function toDateString(date: Date): string {
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${date.getFullYear()}-${month}-${day}`;
}

export function addDays(date: Date, days: number): Date {
    const result = new Date(date);
    result.setDate(result.getDate() + days);

    return result;
}

export function addMonths(date: Date, months: number): Date {
    return new Date(date.getFullYear(), date.getMonth() + months, 1);
}

export function startOfWeek(date: Date): Date {
    const shift = (date.getDay() + 6) % 7;

    return addDays(new Date(date.getFullYear(), date.getMonth(), date.getDate()), -shift);
}

export function startOfMonth(date: Date): Date {
    return new Date(date.getFullYear(), date.getMonth(), 1);
}

export function endOfMonth(date: Date): Date {
    return new Date(date.getFullYear(), date.getMonth() + 1, 0);
}

export function eachDay(from: Date, to: Date): Date[] {
    const days: Date[] = [];

    for (let day = new Date(from); day <= to; day = addDays(day, 1)) {
        days.push(day);
    }

    return days;
}

export function maxDate(a: string, b: string): string {
    return a > b ? a : b;
}

export function minDate(a: string, b: string): string {
    return a < b ? a : b;
}

export function weekdayShort(date: Date): string {
    return WEEKDAYS_SHORT[date.getDay()];
}

export function isWeekend(date: Date): boolean {
    return date.getDay() === 0 || date.getDay() === 6;
}

export function monthLabel(date: Date): string {
    return `${MONTHS[date.getMonth()]} ${date.getFullYear()}`;
}

export function weekLabel(from: Date, to: Date): string {
    if (from.getMonth() === to.getMonth()) {
        return `${from.getDate()} – ${to.getDate()} ${MONTHS_GENITIVE[to.getMonth()]}`;
    }

    return `${from.getDate()} ${MONTHS_GENITIVE[from.getMonth()]} – ${to.getDate()} ${MONTHS_GENITIVE[to.getMonth()]}`;
}

export function fullDateLabel(value: string): string {
    const date = parseDate(value);

    return `${weekdayShort(date)}, ${date.getDate()} ${MONTHS_GENITIVE[date.getMonth()]}`;
}

export function shortDateLabel(value: string | null): string {
    if (!value) {
        return '—';
    }

    const date = new Date(value);

    return `${String(date.getDate()).padStart(2, '0')}.${String(date.getMonth() + 1).padStart(2, '0')}`;
}

export function currentMonthValue(): string {
    return toDateString(new Date()).slice(0, 7);
}
