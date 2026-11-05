import { computed, ref } from 'vue';
import type { Cell } from '@/types';

export function cellKey(cell: Cell): string {
    return `${cell.employee_id}|${cell.date}`;
}

function parseKey(key: string): Cell {
    const [employeeId, date] = key.split('|');

    return { employee_id: Number(employeeId), date };
}

export function useCellSelection() {
    const keys = ref<Set<string>>(new Set());

    const cells = computed<Cell[]>(() => [...keys.value].map(parseKey));
    const size = computed(() => keys.value.size);
    const employeesCount = computed(() => new Set(cells.value.map((cell) => cell.employee_id)).size);
    const daysCount = computed(() => new Set(cells.value.map((cell) => cell.date)).size);

    function isSelected(cell: Cell): boolean {
        return keys.value.has(cellKey(cell));
    }

    function toggle(cell: Cell): void {
        const next = new Set(keys.value);
        const key = cellKey(cell);

        if (next.has(key)) {
            next.delete(key);
        } else {
            next.add(key);
        }

        keys.value = next;
    }

    function add(list: Cell[]): void {
        keys.value = new Set([...keys.value, ...list.map(cellKey)]);
    }

    function replace(list: Cell[]): void {
        keys.value = new Set(list.map(cellKey));
    }

    function retain(predicate: (cell: Cell) => boolean): void {
        keys.value = new Set([...keys.value].filter((key) => predicate(parseKey(key))));
    }

    function clear(): void {
        keys.value = new Set();
    }

    return { cells, size, employeesCount, daysCount, isSelected, toggle, add, replace, retain, clear };
}

export type CellSelection = ReturnType<typeof useCellSelection>;
