<template>
    <div class="workspace">
        <div class="workspace__breadcrumbs">
            <RouterLink class="workspace__back" :to="{ name: 'schedules' }" aria-label="Назад к графикам">
                <AppIcon name="chevron-left" :size="16" />
            </RouterLink>
            <RouterLink class="workspace__crumb" :to="{ name: 'schedules' }">Графики смен</RouterLink>
        </div>

        <header class="page-header">
            <div class="page-header__info">
                <div class="workspace__title-row">
                    <h1 class="page-header__title">График</h1>
                    <StatusBadge :status="schedule.status" />
                </div>
                <p class="page-header__subtitle">{{ schedule.name }} · {{ schedule.address }} · {{ periodLabel }}</p>
            </div>
            <div class="workspace__actions">
                <span v-if="editor.isBusy.value" class="workspace__sync">Сохраняем изменения…</span>
                <BaseButton variant="accent-light" :loading="editor.isPublishing.value" :disabled="editor.isBusy.value" @click="editor.publish">
                    Опубликовать
                </BaseButton>
                <BaseButton variant="accent" :loading="editor.isSaving.value" :disabled="editor.isBusy.value" @click="editor.save">
                    Сохранить
                </BaseButton>
            </div>
        </header>

        <div class="workspace__body">
            <section class="calendar">
                <div class="calendar__top">
                    <SelectionBar
                        v-if="selection.size.value"
                        :size="selection.size.value"
                        :employees-count="selection.employeesCount.value"
                        :days-count="selection.daysCount.value"
                        :filled-count="filledSelectedCount"
                        :templates="editor.templates.value"
                        @apply-template="applyTemplateToSelection"
                        @edit="openBulkEditor"
                        @clear="clearSelected"
                        @reset="selection.clear"
                    />
                    <div v-else class="calendar__toolbar">
                        <SearchField v-model="search" class="calendar__search" placeholder="Поиск по ФИО" />
                        <SegmentedTabs :model-value="period.mode.value" :options="modeOptions" @update:model-value="period.setMode" />
                        <div class="calendar__period">
                            <BaseButton variant="ghost" size="sm" icon="chevron-left" icon-only aria-label="Предыдущий период" @click="period.prev" />
                            <span class="calendar__period-label">{{ period.label.value }}</span>
                            <BaseButton variant="ghost" size="sm" icon="chevron-right" icon-only aria-label="Следующий период" @click="period.next" />
                        </div>
                    </div>
                </div>

                <div class="calendar__scroll" :class="{ 'calendar__scroll--loading': editor.isShiftsLoading.value }">
                    <CalendarGrid
                        :mode="period.mode.value"
                        :days="period.days.value"
                        :employees="filteredEmployees"
                        :selection="selection"
                        :shift-at="editor.shiftAt"
                        :color-of="colorOf"
                        :hours-by-employee="hoursByEmployee"
                        :coverage="coverage"
                        @cell-click="onCellClick"
                        @day-click="openDayMenu"
                        @drop="onDrop"
                    >
                        <template #empty>
                            {{ search ? `По запросу «${search}» никого не нашли` : 'В графике пока нет сотрудников' }}
                        </template>
                    </CalendarGrid>

                    <div v-if="!period.hasAvailableDays.value" class="calendar__outside">
                        <p>Этот период не входит в график ({{ periodLabel }})</p>
                        <BaseButton variant="secondary" size="sm" @click="period.goToPeriod">Вернуться к периоду графика</BaseButton>
                    </div>
                </div>
            </section>

            <aside class="workspace__aside">
                <TemplatesPanel :templates="editor.templates.value" :usage="templateUsage" @apply="applyTemplateToSelection" />
                <div class="workspace__divider" />
                <CustomShiftForm :selected-count="selection.size.value" :loading="editor.isBusy.value" @submit="applyCustomShift" />
            </aside>
        </div>

        <FloatingPanel v-if="editorState" :key="editorState.key" :anchor="editorState.anchor" @close="editorState = null">
            <ShiftEditor
                :title="editorState.title"
                :subtitle="editorState.subtitle"
                :templates="editor.templates.value"
                :initial="editorState.initial"
                :removable="editorState.removable"
                :submit-label="editorState.submitLabel"
                :saving="editor.isBusy.value"
                @submit="submitEditor"
                @remove="removeFromEditor"
            />
        </FloatingPanel>

        <FloatingPanel v-if="dayMenu" :anchor="dayMenu.anchor" @close="dayMenu = null">
            <TemplateMenu :templates="editor.templates.value" :title="dayMenu.title" @choose="assignDay">
                <button class="template-menu__action" type="button" @click="selectDay">
                    <AppIcon name="check" :size="16" />
                    Выделить день
                </button>
                <button class="template-menu__action template-menu__action--danger" type="button" @click="clearDay">
                    <AppIcon name="trash" :size="16" />
                    Очистить день
                </button>
            </TemplateMenu>
        </FloatingPanel>
    </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import AppIcon from '@/components/ui/AppIcon.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import FloatingPanel from '@/components/ui/FloatingPanel.vue';
import SearchField from '@/components/ui/SearchField.vue';
import SegmentedTabs from '@/components/ui/SegmentedTabs.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import CalendarGrid from './CalendarGrid.vue';
import CustomShiftForm from './CustomShiftForm.vue';
import SelectionBar from './SelectionBar.vue';
import ShiftEditor from './ShiftEditor.vue';
import TemplateMenu from './TemplateMenu.vue';
import TemplatesPanel from './TemplatesPanel.vue';
import { useCalendarPeriod, type CalendarDay } from '@/composables/useCalendarPeriod';
import { useCellSelection } from '@/composables/useCellSelection';
import { useDragState } from '@/composables/useDragState';
import { useToast } from '@/composables/useToast';
import type { ScheduleEditor } from '@/composables/useScheduleEditor';
import { fullDateLabel, monthLabel, parseDate } from '@/utils/date';
import { formatHours } from '@/utils/time';
import { plural } from '@/utils/plural';
import type { CalendarMode, Cell, Schedule, Shift, ShiftColor, ShiftTemplate, ShiftTime } from '@/types';

const props = defineProps<{ editor: ScheduleEditor }>();

const toast = useToast();
const dragState = useDragState();
const selection = useCellSelection();

const schedule = computed(() => props.editor.schedule.value as Schedule);
const period = useCalendarPeriod(
    computed(() => schedule.value.period_start),
    computed(() => schedule.value.period_end),
);

const search = ref('');

const modeOptions: { value: CalendarMode; label: string }[] = [
    { value: 'month', label: 'Месяц' },
    { value: 'week', label: 'Неделя' },
];

const periodLabel = computed(() => monthLabel(parseDate(schedule.value.period_start)));

const filteredEmployees = computed(() => {
    const query = search.value.trim().toLowerCase();
    const employees = props.editor.employees.value;

    if (!query) {
        return employees;
    }

    return employees.filter((employee) =>
        `${employee.last_name} ${employee.first_name} ${employee.first_name} ${employee.last_name}`.toLowerCase().includes(query),
    );
});

const visibleShifts = computed(() =>
    Object.values(props.editor.shifts.value).filter((shift) => shift.date >= period.from.value && shift.date <= period.to.value),
);

const coverage = computed(() => {
    const result: Record<string, number> = {};

    for (const shift of visibleShifts.value) {
        result[shift.date] = (result[shift.date] ?? 0) + 1;
    }

    return result;
});

const hoursByEmployee = computed(() => {
    const minutes: Record<number, number> = {};

    for (const shift of visibleShifts.value) {
        minutes[shift.employee_id] = (minutes[shift.employee_id] ?? 0) + shift.duration_minutes;
    }

    return Object.fromEntries(Object.entries(minutes).map(([id, value]) => [id, formatHours(value)])) as Record<number, string>;
});

const templateUsage = computed(() => {
    const result: Record<number, number> = {};

    for (const shift of visibleShifts.value) {
        if (shift.shift_template_id) {
            result[shift.shift_template_id] = (result[shift.shift_template_id] ?? 0) + 1;
        }
    }

    return result;
});

const filledSelectedCount = computed(() => selection.cells.value.filter((cell) => props.editor.shiftAt(cell)).length);

function colorOf(shift?: Shift): ShiftColor {
    const template = shift?.shift_template_id ? props.editor.templatesById.value.get(shift.shift_template_id) : undefined;

    return template?.color ?? 'purple';
}

function employeeName(employeeId: number): string {
    return props.editor.employees.value.find((employee) => employee.id === employeeId)?.full_name ?? '';
}

interface EditorState {
    key: string;
    anchor: DOMRect;
    title: string;
    subtitle: string;
    initial?: ShiftTime;
    removable: boolean;
    submitLabel: string;
    shift?: Shift;
    cells: Cell[];
}

const editorState = ref<EditorState | null>(null);
const dayMenu = ref<{ anchor: DOMRect; day: CalendarDay; title: string } | null>(null);

function openShiftEditor(cell: Cell, rect: DOMRect): void {
    const shift = props.editor.shiftAt(cell);

    editorState.value = {
        key: `cell-${cell.employee_id}-${cell.date}`,
        anchor: rect,
        title: shift ? 'Редактирование смены' : 'Новая смена',
        subtitle: `${employeeName(cell.employee_id)} · ${fullDateLabel(cell.date)}`,
        initial: shift ? { start_time: shift.start_time, end_time: shift.end_time, shift_template_id: shift.shift_template_id } : undefined,
        removable: Boolean(shift),
        submitLabel: shift ? 'Сохранить' : 'Назначить',
        shift,
        cells: [cell],
    };
}

function openBulkEditor(rect: DOMRect): void {
    const firstShift = selection.cells.value.map((cell) => props.editor.shiftAt(cell)).find(Boolean);
    const count = selection.size.value;

    editorState.value = {
        key: `bulk-${count}`,
        anchor: rect,
        title: 'Изменить выделенные',
        subtitle: `${count} ${plural(count, ['ячейка', 'ячейки', 'ячеек'])} получат одинаковое время`,
        initial: firstShift ? { start_time: firstShift.start_time, end_time: firstShift.end_time, shift_template_id: firstShift.shift_template_id } : undefined,
        removable: false,
        submitLabel: 'Применить',
        cells: selection.cells.value,
    };
}

async function submitEditor(time: ShiftTime): Promise<void> {
    if (!editorState.value) {
        return;
    }

    const { shift, cells } = editorState.value;
    const isDone = shift ? await props.editor.update(shift, time) : await props.editor.assign(cells, time);

    if (isDone) {
        editorState.value = null;

        if (cells.length > 1) {
            selection.clear();
        }
    }
}

async function removeFromEditor(): Promise<void> {
    const shift = editorState.value?.shift;

    if (shift && (await props.editor.remove(shift))) {
        editorState.value = null;
    }
}

function onCellClick(cell: Cell, rect: DOMRect, event: MouseEvent): void {
    if (event.ctrlKey || event.metaKey || event.shiftKey || !props.editor.shiftAt(cell)) {
        selection.toggle(cell);

        return;
    }

    openShiftEditor(cell, rect);
}

async function onDrop(cell: Cell): Promise<void> {
    const payload = dragState.payload.value;
    dragState.end();

    if (!payload) {
        return;
    }

    if (payload.kind === 'template') {
        const template = props.editor.templatesById.value.get(payload.templateId);

        if (!template) {
            return;
        }

        const targetCells = selection.isSelected(cell) && selection.size.value > 1 ? selection.cells.value : [cell];

        if (await props.editor.assignTemplate(targetCells, template)) {
            if (targetCells.length > 1) {
                selection.clear();
            }
        }

        return;
    }

    const shift = Object.values(props.editor.shifts.value).find((item) => item.id === payload.shiftId);

    if (shift) {
        await props.editor.move(shift, cell);
    }
}

async function applyTemplateToSelection(template: ShiftTemplate): Promise<void> {
    if (!selection.size.value) {
        toast.info('Выделите ячейки в календаре или перетащите шаблон в нужную ячейку');

        return;
    }

    if (await props.editor.assignTemplate(selection.cells.value, template)) {
        selection.clear();
    }
}

async function applyCustomShift(time: ShiftTime): Promise<void> {
    if (await props.editor.assign(selection.cells.value, time)) {
        selection.clear();
    }
}

async function clearSelected(): Promise<void> {
    if (await props.editor.clear(selection.cells.value)) {
        selection.clear();
    }
}

function dayCells(day: CalendarDay): Cell[] {
    return filteredEmployees.value.map((employee) => ({ employee_id: employee.id, date: day.date }));
}

function openDayMenu(day: CalendarDay, rect: DOMRect): void {
    dayMenu.value = {
        anchor: rect,
        day,
        title: `${fullDateLabel(day.date)} · всем (${filteredEmployees.value.length})`,
    };
}

async function assignDay(template: ShiftTemplate): Promise<void> {
    if (!dayMenu.value) {
        return;
    }

    const cells = dayCells(dayMenu.value.day);
    dayMenu.value = null;

    if (!cells.length) {
        toast.info('Нет сотрудников для назначения');

        return;
    }

    await props.editor.assignTemplate(cells, template);
}

function selectDay(): void {
    if (dayMenu.value) {
        selection.add(dayCells(dayMenu.value.day));
        dayMenu.value = null;
    }
}

async function clearDay(): Promise<void> {
    if (dayMenu.value) {
        const cells = dayCells(dayMenu.value.day);
        dayMenu.value = null;
        await props.editor.clear(cells);
    }
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape' && !editorState.value && !dayMenu.value) {
        selection.clear();
    }
}

watch(
    [period.from, period.to],
    ([from, to]) => {
        selection.retain((cell) => cell.date >= from && cell.date <= to);
        props.editor.loadShifts(from, to);
    },
    { immediate: true },
);

watch(search, () => {
    const visibleIds = new Set(filteredEmployees.value.map((employee) => employee.id));
    selection.retain((cell) => visibleIds.has(cell.employee_id));
});

onMounted(() => document.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown));
</script>

<style lang="scss">
.workspace {
    &__breadcrumbs {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    &__back {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: $color-surface;
        transition: background-color $transition;

        &:hover {
            background: $color-surface-hover;
        }
    }

    &__crumb {
        font-size: 13px;
        color: $color-text-secondary;

        &:hover {
            color: $color-text;
        }
    }

    &__title-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    &__actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    &__sync {
        margin-right: 8px;
        font-size: 12px;
        color: $color-text-secondary;
    }

    &__body {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 280px;
        gap: 12px;
        align-items: start;
    }

    &__aside {
        @include panel;
        position: sticky;
        top: 20px;
        padding: 16px;
    }

    &__divider {
        height: 1px;
        margin: 20px 0;
        background: $color-border;
    }
}

.calendar {
    @include panel;
    display: flex;
    flex-direction: column;
    min-width: 0;
    padding: 12px;

    &__top {
        margin-bottom: 8px;
    }

    &__toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 46px;
    }

    &__search {
        flex: 0 1 300px;
    }

    &__period {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-left: auto;
    }

    &__period-label {
        min-width: 150px;
        font-size: 14px;
        font-weight: 600;
        text-align: center;
    }

    &__scroll {
        position: relative;
        max-height: calc(100vh - 260px);
        min-height: 320px;
        overflow: auto;
        @include scrollbar;
        transition: opacity $transition;

        &--loading {
            opacity: 0.65;
        }
    }

    &__outside {
        position: absolute;
        inset: 44px 0 44px var(--employee-column, 220px);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        background: rgba($color-surface, 0.85);
        color: $color-text-secondary;
        text-align: center;
    }
}

@include breakpoint(1280px) {
    .workspace__body {
        grid-template-columns: minmax(0, 1fr);
    }

    .workspace__aside {
        position: static;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    .workspace__divider {
        display: none;
    }
}

@include breakpoint(900px) {
    .calendar__toolbar {
        flex-wrap: wrap;
    }

    .workspace__aside {
        grid-template-columns: 1fr;
    }
}
</style>
