import { http } from './http';
import type { Cell, Shift, ShiftTime } from '@/types';

export async function fetchShifts(scheduleId: number, from: string, to: string): Promise<Shift[]> {
    const { data } = await http.get<{ data: Shift[] }>(`/schedules/${scheduleId}/shifts`, {
        params: { from, to },
    });

    return data.data;
}

export async function createShift(scheduleId: number, cell: Cell, time: ShiftTime): Promise<Shift> {
    const { data } = await http.post<{ data: Shift }>(`/schedules/${scheduleId}/shifts`, { ...cell, ...time });

    return data.data;
}

export async function updateShift(scheduleId: number, shiftId: number, payload: ShiftTime & Partial<Cell>): Promise<Shift> {
    const { data } = await http.patch<{ data: Shift }>(`/schedules/${scheduleId}/shifts/${shiftId}`, payload);

    return data.data;
}

export async function deleteShift(scheduleId: number, shiftId: number): Promise<void> {
    await http.delete(`/schedules/${scheduleId}/shifts/${shiftId}`);
}

export async function bulkCreateShifts(scheduleId: number, cells: Cell[], time: ShiftTime): Promise<Shift[]> {
    const { data } = await http.post<{ data: Shift[] }>(`/schedules/${scheduleId}/shifts/bulk`, { cells, ...time });

    return data.data;
}

export async function bulkDeleteShifts(scheduleId: number, cells: Cell[]): Promise<number> {
    const { data } = await http.delete<{ deleted: number }>(`/schedules/${scheduleId}/shifts/bulk`, {
        data: { cells },
    });

    return data.deleted;
}
