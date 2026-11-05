<template>
    <section class="templates">
        <h2 class="templates__title">Шаблоны смен</h2>
        <p class="templates__hint">Перетащите шаблон в ячейку или выделите ячейки и нажмите на шаблон</p>

        <ul class="templates__list">
            <li
                v-for="template in templates"
                :key="template.id"
                class="template-card"
                :class="[`template-card--${template.color}`, { 'template-card--dragging': draggingId === template.id }]"
                draggable="true"
                role="button"
                tabindex="0"
                @dragstart="onDragStart($event, template)"
                @dragend="onDragEnd"
                @click="$emit('apply', template)"
                @keydown.enter="$emit('apply', template)"
            >
                <div class="template-card__info">
                    <span class="template-card__name">{{ template.name }}</span>
                    <span class="template-card__time">
                        {{ formatRange(template.start_time, template.end_time) }} ·
                        {{ formatHours(durationInMinutes(template.start_time, template.end_time)) }}
                    </span>
                </div>
                <span class="template-card__count" :title="`Назначено в текущем периоде: ${usage[template.id] ?? 0}`">
                    {{ usage[template.id] ?? 0 }}
                </span>
            </li>
        </ul>
    </section>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useDragState } from '@/composables/useDragState';
import { durationInMinutes, formatHours, formatRange } from '@/utils/time';
import type { ShiftTemplate } from '@/types';

defineProps<{ templates: ShiftTemplate[]; usage: Record<number, number> }>();

defineEmits<{ apply: [template: ShiftTemplate] }>();

const dragState = useDragState();
const draggingId = ref<number | null>(null);

function onDragStart(event: DragEvent, template: ShiftTemplate): void {
    draggingId.value = template.id;
    dragState.start(event, { kind: 'template', templateId: template.id });
}

function onDragEnd(): void {
    draggingId.value = null;
    dragState.end();
}
</script>

<style lang="scss">
@use 'sass:list';

.templates {
    &__title {
        font-size: 14px;
        font-weight: 600;
    }

    &__hint {
        margin: 4px 0 12px;
        font-size: 12px;
        line-height: 1.4;
        color: $color-text-secondary;
    }

    &__list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
}

.template-card {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    padding: 10px 10px 10px 14px;
    border-left: 3px solid transparent;
    border-radius: $radius-sm;
    background: $color-surface-muted;
    cursor: grab;
    transition: background-color $transition, opacity $transition, transform $transition;

    @each $name, $colors in $shift-colors {
        &--#{$name} {
            border-left-color: list.nth($colors, 1);
        }
    }

    &:hover {
        background: $color-surface-hover;
    }

    &:active {
        cursor: grabbing;
    }

    &--dragging {
        opacity: 0.5;
        transform: scale(0.98);
    }

    &__info {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    &__name {
        font-size: 14px;
        font-weight: 600;
    }

    &__time {
        font-size: 13px;
    }

    &__count {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        border-radius: $radius-xs;
        background: $color-surface;
        font-size: 11px;
        font-weight: 600;
    }
}
</style>
