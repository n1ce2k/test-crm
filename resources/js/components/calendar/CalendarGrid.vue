<template>
    <div
        class="calendar-grid"
        :class="[`calendar-grid--${mode}`, { 'calendar-grid--dragging': dragState.payload.value }]"
        :style="{ '--days': days.length }"
    >
        <div class="calendar-grid__row calendar-grid__row--head">
            <div class="calendar-grid__corner">Сотрудник</div>
            <button
                v-for="day in days"
                :key="day.date"
                class="calendar-grid__day"
                :class="{
                    'calendar-grid__day--weekend': day.isWeekend,
                    'calendar-grid__day--today': day.isToday,
                    'calendar-grid__day--disabled': !day.isAvailable,
                }"
                type="button"
                :disabled="!day.isAvailable"
                :title="day.isAvailable ? 'Быстрое назначение на день' : 'Дата вне периода графика'"
                @click="$emit('day-click', day, ($event.currentTarget as HTMLElement).getBoundingClientRect())"
            >
                <span class="calendar-grid__weekday">{{ day.weekday }}</span>
                <span class="calendar-grid__date">{{ day.day }}</span>
            </button>
        </div>

        <div v-for="(employee, employeeIndex) in employees" :key="employee.id" class="calendar-grid__row">
            <div class="calendar-grid__employee">
                <EmployeeInfo :employee="employee" :hours="hoursByEmployee[employee.id] ?? '0 ч'" />
            </div>
            <ShiftCell
                v-for="(day, dayIndex) in days"
                :key="day.date"
                :shift="shiftAt({ employee_id: employee.id, date: day.date })"
                :color="colorOf(shiftAt({ employee_id: employee.id, date: day.date }))"
                :compact="mode === 'month'"
                :selected="selection.isSelected({ employee_id: employee.id, date: day.date })"
                :available="day.isAvailable"
                :drop-target="dropKey === `${employee.id}|${day.date}`"
                @mousedown="onCellMouseDown($event, employeeIndex, dayIndex)"
                @mouseenter="onCellMouseEnter(employeeIndex, dayIndex)"
                @dragover="onDragOver($event, employee.id, day)"
                @dragleave="onDragLeave(employee.id, day.date)"
                @drop="onDrop($event, employee.id, day)"
                @chip-dragstart="onChipDragStart($event, employee.id, day.date)"
                @chip-dragend="onDragEnd"
            />
        </div>

        <div v-if="!employees.length" class="calendar-grid__empty">
            <slot name="empty">Сотрудники не найдены</slot>
        </div>

        <div class="calendar-grid__row calendar-grid__row--coverage">
            <div class="calendar-grid__coverage-label">Покрытие</div>
            <div
                v-for="day in days"
                :key="day.date"
                class="calendar-grid__coverage"
                :class="{
                    'calendar-grid__coverage--filled': (coverage[day.date] ?? 0) > 0,
                    'calendar-grid__coverage--disabled': !day.isAvailable,
                }"
            >
                {{ day.isAvailable ? coverage[day.date] ?? 0 : '—' }}
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import EmployeeInfo from './EmployeeInfo.vue';
import ShiftCell from './ShiftCell.vue';
import { useDragState } from '@/composables/useDragState';
import type { CalendarDay } from '@/composables/useCalendarPeriod';
import type { CellSelection } from '@/composables/useCellSelection';
import type { CalendarMode, Cell, Employee, Shift, ShiftColor } from '@/types';

const props = defineProps<{
    mode: CalendarMode;
    days: CalendarDay[];
    employees: Employee[];
    selection: CellSelection;
    shiftAt: (cell: Cell) => Shift | undefined;
    colorOf: (shift?: Shift) => ShiftColor;
    hoursByEmployee: Record<number, string>;
    coverage: Record<string, number>;
}>();

const emit = defineEmits<{
    'cell-click': [cell: Cell, rect: DOMRect, event: MouseEvent];
    'day-click': [day: CalendarDay, rect: DOMRect];
    drop: [cell: Cell];
}>();

const dragState = useDragState();
const dropKey = ref<string | null>(null);

interface PointerState {
    startEmployee: number;
    startDay: number;
    moved: boolean;
    base: Cell[];
    target: HTMLElement;
    event: MouseEvent;
}

let pointer: PointerState | null = null;

function cellAt(employeeIndex: number, dayIndex: number): Cell {
    return { employee_id: props.employees[employeeIndex].id, date: props.days[dayIndex].date };
}

function onCellMouseDown(event: MouseEvent, employeeIndex: number, dayIndex: number): void {
    if (event.button !== 0 || !props.days[dayIndex].isAvailable) {
        return;
    }

    pointer = {
        startEmployee: employeeIndex,
        startDay: dayIndex,
        moved: false,
        base: props.selection.cells.value,
        target: event.currentTarget as HTMLElement,
        event,
    };
}

function onCellMouseEnter(employeeIndex: number, dayIndex: number): void {
    if (!pointer) {
        return;
    }

    pointer.moved = true;

    const rect: Cell[] = [];
    const [fromEmployee, toEmployee] = [pointer.startEmployee, employeeIndex].sort((a, b) => a - b);
    const [fromDay, toDay] = [pointer.startDay, dayIndex].sort((a, b) => a - b);

    for (let e = fromEmployee; e <= toEmployee; e++) {
        for (let d = fromDay; d <= toDay; d++) {
            if (props.days[d].isAvailable) {
                rect.push(cellAt(e, d));
            }
        }
    }

    props.selection.replace([...pointer.base, ...rect]);
}

function onDocumentMouseUp(): void {
    if (pointer && !pointer.moved) {
        const cell = cellAt(pointer.startEmployee, pointer.startDay);
        emit('cell-click', cell, pointer.target.getBoundingClientRect(), pointer.event);
    }

    pointer = null;
}

function onChipDragStart(event: DragEvent, employeeId: number, date: string): void {
    pointer = null;
    const shift = props.shiftAt({ employee_id: employeeId, date });

    if (shift) {
        dragState.start(event, { kind: 'shift', shiftId: shift.id });
    }
}

function onDragOver(event: DragEvent, employeeId: number, day: CalendarDay): void {
    if (!dragState.payload.value || !day.isAvailable) {
        return;
    }

    event.preventDefault();
    dropKey.value = `${employeeId}|${day.date}`;

    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = dragState.payload.value.kind === 'shift' ? 'move' : 'copy';
    }
}

function onDragLeave(employeeId: number, date: string): void {
    if (dropKey.value === `${employeeId}|${date}`) {
        dropKey.value = null;
    }
}

function onDrop(event: DragEvent, employeeId: number, day: CalendarDay): void {
    event.preventDefault();
    dropKey.value = null;

    if (day.isAvailable && dragState.payload.value) {
        emit('drop', { employee_id: employeeId, date: day.date });
    }
}

function onDragEnd(): void {
    dropKey.value = null;
    dragState.end();
}

onMounted(() => document.addEventListener('mouseup', onDocumentMouseUp));
onBeforeUnmount(() => document.removeEventListener('mouseup', onDocumentMouseUp));
</script>

<style lang="scss">
.calendar-grid {
    --employee-column: 220px;
    --cell-min: 76px;
    --row-height:72px;

    min-width: max-content;
    user-select: none;

    &--month {
        --cell-min: 46px;
        --row-height: 48px;
    }

    &__row {
        display: grid;
        grid-template-columns: var(--employee-column) repeat(var(--days), minmax(var(--cell-min), 1fr));
        height: var(--row-height);

        &:nth-child(even):not(&--head):not(&--coverage) {
            background: rgba($color-surface-muted, 0.7);

            .calendar-grid__employee {
                background: #f9fafb;
            }
        }

        &--head {
            position: sticky;
            top: 0;
            z-index: 3;
            height: 44px;
            background: $color-surface;
        }

        &--coverage {
            position: sticky;
            bottom: 0;
            z-index: 2;
            height: 40px;
            margin-top: 4px;
            background: $color-surface;
        }
    }

    &__corner,
    &__employee,
    &__coverage-label {
        position: sticky;
        left: 0;
        z-index: 1;
        display: flex;
        align-items: center;
        padding: 0 12px;
        background: $color-surface;
    }

    &__corner {
        font-size: 12px;
        color: $color-text-secondary;
    }

    &__day {
        @include reset-button;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        margin: 4px 2px;
        border-radius: $radius-sm;
        font-size: 12px;
        color: $color-text-secondary;
        transition: background-color $transition, color $transition;

        &:not(:disabled):hover {
            background: $color-surface-muted;
            color: $color-text;
        }

        &--weekend {
            color: $color-text-muted;
        }

        &--today .calendar-grid__date {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 22px;
            padding: 0 4px;
            border-radius: 11px;
            background: $color-accent;
            color: #fff;
        }

        &--disabled {
            opacity: 0.5;
            cursor: default;
        }
    }

    &--month &__day {
        flex-direction: column;
        gap: 0;
        line-height: 1.2;
    }

    &__date {
        font-weight: 600;
        color: $color-text;
    }

    &__day--weekend &__date {
        color: $color-text-secondary;
    }

    &__employee {
        min-width: 0;
    }

    &__empty {
        padding: 40px 12px;
        text-align: center;
        color: $color-text-secondary;
    }

    &__coverage-label {
        font-size: 13px;
        color: $color-text-secondary;
    }

    &__coverage {
        display: flex;
        align-items: center;
        justify-content: center;
        background: $color-danger-light;
        font-size: 13px;
        font-weight: 500;
        color: $color-danger;
        transition: background-color 0.3s ease, color 0.3s ease;

        &--filled {
            background: $color-accent-light;
            color: $color-accent;
        }

        &--disabled {
            background: $color-surface-muted;
            color: $color-text-muted;
        }
    }

    &--dragging .shift-cell:not(.shift-cell--disabled) .shift-cell__empty {
        border-color: rgba($color-accent, 0.45);
    }
}
</style>
