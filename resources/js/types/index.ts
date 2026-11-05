export type ScheduleStatus = 'draft' | 'published';

export type ShiftColor = 'blue' | 'teal' | 'purple';

export type CalendarMode = 'week' | 'month';

export interface Employee {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    initials: string;
    position: string;
    avatar: string | null;
}

export interface ShiftTemplate {
    id: number;
    name: string;
    start_time: string;
    end_time: string;
    color: ShiftColor;
}

export interface Shift {
    id: number;
    employee_id: number;
    shift_template_id: number | null;
    date: string;
    start_time: string;
    end_time: string;
    duration_minutes: number;
}

export interface Schedule {
    id: number;
    name: string;
    address: string;
    status: ScheduleStatus;
    status_label: string;
    period_start: string;
    period_end: string;
    published_at: string | null;
    updated_at: string | null;
    employees_count: number;
    progress: number;
    employees?: Employee[];
}

export interface Cell {
    employee_id: number;
    date: string;
}

export interface ShiftTime {
    start_time: string;
    end_time: string;
    shift_template_id?: number | null;
}

export interface ScheduleFilters {
    search?: string;
    status?: ScheduleStatus | '';
}

export interface CreateSchedulePayload {
    name: string;
    address: string;
    month: string;
    employee_ids: number[];
}

export type ValidationErrors = Record<string, string[]>;
