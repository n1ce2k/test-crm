import { computed, ref } from 'vue';
import { fetchSchedule, publishSchedule, saveSchedule } from '@/api/schedules';
import { fetchShiftTemplates } from '@/api/dictionaries';
import { bulkCreateShifts, bulkDeleteShifts, createShift, deleteShift, fetchShifts, updateShift } from '@/api/shifts';
import { getErrorMessage } from '@/api/http';
import { useToast } from './useToast';
import { cellKey } from './useCellSelection';
import { maxDate, minDate } from '@/utils/date';
import type { Cell, Schedule, Shift, ShiftTemplate, ShiftTime } from '@/types';

export function useScheduleEditor(scheduleId: number) {
    const toast = useToast();

    const schedule = ref<Schedule | null>(null);
    const templates = ref<ShiftTemplate[]>([]);
    const shifts = ref<Record<string, Shift>>({});
    const isLoading = ref(true);
    const loadError = ref('');
    const isShiftsLoading = ref(false);
    const pendingRequests = ref(0);
    const isSaving = ref(false);
    const isPublishing = ref(false);

    const employees = computed(() => schedule.value?.employees ?? []);
    const isBusy = computed(() => pendingRequests.value > 0);

    const templatesById = computed(() => new Map(templates.value.map((template) => [template.id, template])));

    let shiftsRequestId = 0;

    async function load(): Promise<void> {
        isLoading.value = true;
        loadError.value = '';

        try {
            const [loadedSchedule, loadedTemplates] = await Promise.all([fetchSchedule(scheduleId), fetchShiftTemplates()]);
            schedule.value = loadedSchedule;
            templates.value = loadedTemplates;
        } catch (error) {
            loadError.value = getErrorMessage(error, 'Не удалось загрузить график');
        } finally {
            isLoading.value = false;
        }
    }

    async function loadShifts(from: string, to: string): Promise<void> {
        if (!schedule.value) {
            return;
        }

        const rangeFrom = maxDate(from, schedule.value.period_start);
        const rangeTo = minDate(to, schedule.value.period_end);

        if (rangeFrom > rangeTo) {
            return;
        }

        const requestId = ++shiftsRequestId;
        isShiftsLoading.value = true;

        try {
            const loaded = await fetchShifts(scheduleId, rangeFrom, rangeTo);

            if (requestId !== shiftsRequestId) {
                return;
            }

            const next: Record<string, Shift> = {};

            for (const [key, shift] of Object.entries(shifts.value)) {
                if (shift.date < rangeFrom || shift.date > rangeTo) {
                    next[key] = shift;
                }
            }

            for (const shift of loaded) {
                next[cellKey(shift)] = shift;
            }

            shifts.value = next;
        } catch (error) {
            toast.error(getErrorMessage(error, 'Не удалось загрузить смены'));
        } finally {
            if (requestId === shiftsRequestId) {
                isShiftsLoading.value = false;
            }
        }
    }

    function shiftAt(cell: Cell): Shift | undefined {
        return shifts.value[cellKey(cell)];
    }

    function putShifts(list: Shift[]): void {
        const next = { ...shifts.value };

        for (const shift of list) {
            for (const [key, existing] of Object.entries(next)) {
                if (existing.id === shift.id) {
                    delete next[key];
                }
            }

            next[cellKey(shift)] = shift;
        }

        shifts.value = next;
    }

    function removeCells(cells: Cell[]): void {
        const next = { ...shifts.value };

        for (const cell of cells) {
            delete next[cellKey(cell)];
        }

        shifts.value = next;
    }

    function markChanged(): void {
        if (schedule.value) {
            schedule.value = { ...schedule.value, status: 'draft', status_label: 'Черновик' };
        }
    }

    async function mutate<T>(action: () => Promise<T>, fallbackMessage: string): Promise<T | null> {
        pendingRequests.value++;

        try {
            const result = await action();
            markChanged();

            return result;
        } catch (error) {
            toast.error(getErrorMessage(error, fallbackMessage));

            return null;
        } finally {
            pendingRequests.value--;
        }
    }

    async function assign(cells: Cell[], time: ShiftTime): Promise<boolean> {
        if (!cells.length) {
            return false;
        }

        const result = await mutate(
            () => (cells.length === 1 ? createShift(scheduleId, cells[0], time).then((shift) => [shift]) : bulkCreateShifts(scheduleId, cells, time)),
            'Не удалось назначить смену',
        );

        if (result) {
            putShifts(result);
        }

        return Boolean(result);
    }

    async function assignTemplate(cells: Cell[], template: ShiftTemplate): Promise<boolean> {
        return assign(cells, {
            start_time: template.start_time,
            end_time: template.end_time,
            shift_template_id: template.id,
        });
    }

    async function update(shift: Shift, time: ShiftTime): Promise<boolean> {
        const result = await mutate(() => updateShift(scheduleId, shift.id, time), 'Не удалось изменить смену');

        if (result) {
            putShifts([result]);
        }

        return Boolean(result);
    }

    async function move(shift: Shift, target: Cell): Promise<boolean> {
        if (cellKey(shift) === cellKey(target)) {
            return false;
        }

        const result = await mutate(
            () => updateShift(scheduleId, shift.id, {
                ...target,
                start_time: shift.start_time,
                end_time: shift.end_time,
                shift_template_id: shift.shift_template_id,
            }),
            'Не удалось перенести смену',
        );

        if (result) {
            removeCells([target]);
            putShifts([result]);
        }

        return Boolean(result);
    }

    async function remove(shift: Shift): Promise<boolean> {
        const result = await mutate(() => deleteShift(scheduleId, shift.id).then(() => true), 'Не удалось удалить смену');

        if (result) {
            removeCells([shift]);
        }

        return Boolean(result);
    }

    async function clear(cells: Cell[]): Promise<boolean> {
        const filled = cells.filter((cell) => shiftAt(cell));

        if (!filled.length) {
            return true;
        }

        const result = await mutate(() => bulkDeleteShifts(scheduleId, filled).then(() => true), 'Не удалось очистить ячейки');

        if (result) {
            removeCells(filled);
        }

        return Boolean(result);
    }

    async function changeStatus(action: 'save' | 'publish'): Promise<void> {
        const flag = action === 'save' ? isSaving : isPublishing;
        flag.value = true;

        try {
            const updated = await (action === 'save' ? saveSchedule(scheduleId) : publishSchedule(scheduleId));
            schedule.value = { ...updated, employees: schedule.value?.employees ?? updated.employees };
            toast.success(action === 'save' ? 'График сохранён как черновик' : 'График опубликован');
        } catch (error) {
            toast.error(getErrorMessage(error, action === 'save' ? 'Не удалось сохранить график' : 'Не удалось опубликовать график'));
        } finally {
            flag.value = false;
        }
    }

    return {
        schedule,
        templates,
        templatesById,
        employees,
        shifts,
        isLoading,
        loadError,
        isShiftsLoading,
        isBusy,
        isSaving,
        isPublishing,
        load,
        loadShifts,
        shiftAt,
        assign,
        assignTemplate,
        update,
        move,
        remove,
        clear,
        save: () => changeStatus('save'),
        publish: () => changeStatus('publish'),
    };
}

export type ScheduleEditor = ReturnType<typeof useScheduleEditor>;
