export function timeToMinutes(value: string): number {
    const [hours, minutes] = value.split(':').map(Number);

    return hours * 60 + minutes;
}

export function durationInMinutes(start: string, end: string): number {
    return timeToMinutes(end) - timeToMinutes(start);
}

export function isValidTimeRange(start: string, end: string): boolean {
    return Boolean(start && end) && timeToMinutes(end) > timeToMinutes(start);
}

export function formatHours(minutes: number): string {
    const hours = Math.round((minutes / 60) * 10) / 10;

    return `${String(hours).replace('.', ',')} ч`;
}

export function compactTime(value: string): string {
    return value.endsWith(':00') ? value.slice(0, 2) : value;
}

export function formatCompactRange(start: string, end: string): string {
    return `${compactTime(start)} - ${compactTime(end)}`;
}

export function formatRange(start: string, end: string): string {
    return `${start} - ${end}`;
}
