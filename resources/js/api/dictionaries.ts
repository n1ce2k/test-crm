import { http } from './http';
import type { Employee, ShiftTemplate } from '@/types';

export async function fetchShiftTemplates(): Promise<ShiftTemplate[]> {
    const { data } = await http.get<{ data: ShiftTemplate[] }>('/shift-templates');

    return data.data;
}

export async function fetchEmployees(): Promise<Employee[]> {
    const { data } = await http.get<{ data: Employee[] }>('/employees');

    return data.data;
}
