<template>
    <div
        class="shift-cell"
        :class="{
            'shift-cell--selected': selected,
            'shift-cell--disabled': !available,
            'shift-cell--drop': dropTarget,
            'shift-cell--filled': shift,
        }"
        :title="available ? undefined : 'Дата вне периода графика'"
    >
        <div
            v-if="shift"
            class="shift-chip"
            :class="[`shift-chip--${color}`, { 'shift-chip--selected': selected, 'shift-chip--compact': compact }]"
            draggable="true"
            @dragstart="$emit('chip-dragstart', $event)"
            @dragend="$emit('chip-dragend')"
        >
            <template v-if="compact">
                <span class="shift-chip__hours">{{ hours }}</span>
            </template>
            <template v-else>
                <span class="shift-chip__time">{{ range }}</span>
                <span class="shift-chip__hours">{{ hours }}</span>
            </template>
        </div>
        <div v-else class="shift-cell__empty">
            <span v-if="selected" class="shift-cell__plus">
                <AppIcon name="plus" :size="12" />
            </span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import AppIcon from '@/components/ui/AppIcon.vue';
import { formatCompactRange, formatHours } from '@/utils/time';
import type { Shift, ShiftColor } from '@/types';

const props = defineProps<{
    shift?: Shift;
    color: ShiftColor;
    compact: boolean;
    selected: boolean;
    available: boolean;
    dropTarget: boolean;
}>();

defineEmits<{ 'chip-dragstart': [event: DragEvent]; 'chip-dragend': [] }>();

const range = computed(() => (props.shift ? formatCompactRange(props.shift.start_time, props.shift.end_time) : ''));
const hours = computed(() => (props.shift ? formatHours(props.shift.duration_minutes) : ''));
</script>

<style lang="scss">
@use 'sass:list';

.shift-cell {
    position: relative;
    height: 100%;
    padding: 4px;
    cursor: pointer;
    &__empty {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;

        border: 1px dashed $color-border-dashed;
        border-radius: $radius-sm;
        transition: background-color $transition, border-color $transition;
    }

    &:hover &__empty {
        border-color: rgba($color-accent, 0.5);
        background: rgba($color-accent-light, 0.5);
    }

    &--selected &__empty {
        border: 1px solid $color-accent;
        background: $color-accent-light;
    }

    &__plus {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: $color-accent;
        color: #fff;
    }

    &--drop &__empty,
    &--drop .shift-chip {
        border: 1px dashed $color-accent;
        background: $color-accent-light;
        box-shadow: inset 0 0 0 1px $color-accent;
    }

    &--disabled {
        cursor: not-allowed;

        .shift-cell__empty {
            border-color: transparent;
            background: repeating-linear-gradient(-45deg, transparent 0 6px, rgba($color-border-dashed, 0.45) 6px 7px);
        }

        &:hover .shift-cell__empty {
            border-color: transparent;
            background: repeating-linear-gradient(-45deg, transparent 0 6px, rgba($color-border-dashed, 0.45) 6px 7px);
        }
    }
}

.shift-chip {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    border: 1px solid transparent;
    border-radius: $radius-sm;
    cursor: grab;
    transition: background-color $transition, color $transition, transform $transition;
    user-select: none;

    &:active {
        cursor: grabbing;
    }

    @each $name, $colors in $shift-colors {
        &--#{$name} {
            background: list.nth($colors, 2);

            .shift-chip__hours {
                color: list.nth($colors, 1);
            }

            &.shift-chip--compact .shift-chip__hours {
                font-size: 13px;
                font-weight: 600;
            }
        }
    }

    &:hover {
        filter: brightness(0.97);
    }

    &__time {
        font-size: 13px;
        font-weight: 500;
        line-height: 1.2;
    }

    &__hours {
        font-size: 11px;
        line-height: 1.2;
    }

    &--selected {
        background: $color-accent;
        color: #fff;

        .shift-chip__hours {
            color: rgba(#fff, 0.8);
        }

        &.shift-chip--compact .shift-chip__hours {
            color: #fff;
        }
    }
}
</style>
