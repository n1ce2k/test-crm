import { http } from './http';
import type { CreateSchedulePayload, Schedule, ScheduleFilters } from '@/types';

export async function fetchSchedules(filters: ScheduleFilters = {}, signal?: AbortSignal): Promise<Schedule[]> {
    const { data } = await http.get<{ data: Schedule[] }>('/schedules', {
        params: {
            search: filters.search || undefined,
            status: filters.status || undefined,
        },
        signal,
    });

    return data.data;
}

export async function fetchSchedule(id: number): Promise<Schedule> {
    const { data } = await http.get<{ data: Schedule }>(`/schedules/${id}`);

    return data.data;
}

export async function createSchedule(payload: CreateSchedulePayload): Promise<Schedule> {
    const { data } = await http.post<{ data: Schedule }>('/schedules', payload);

    return data.data;
}

export async function saveSchedule(id: number): Promise<Schedule> {
    const { data } = await http.post<{ data: Schedule }>(`/schedules/${id}/save`);

    return data.data;
}

export async function publishSchedule(id: number): Promise<Schedule> {
    const { data } = await http.post<{ data: Schedule }>(`/schedules/${id}/publish`);

    return data.data;
}
