<template>
    <div class="template-menu">
        <p v-if="title" class="template-menu__title">{{ title }}</p>
        <ul class="template-menu__list">
            <li v-for="template in templates" :key="template.id">
                <button class="template-menu__item" type="button" @click="$emit('choose', template)">
                    <span class="template-menu__marker" :class="`template-menu__marker--${template.color}`" />
                    <span class="template-menu__name">{{ template.name }}</span>
                    <span class="template-menu__time">{{ formatRange(template.start_time, template.end_time) }}</span>
                </button>
            </li>
        </ul>
        <div v-if="$slots.default" class="template-menu__extra">
            <slot />
        </div>
    </div>
</template>

<script setup lang="ts">
import { formatRange } from '@/utils/time';
import type { ShiftTemplate } from '@/types';

defineProps<{ templates: ShiftTemplate[]; title?: string }>();

defineEmits<{ choose: [template: ShiftTemplate] }>();
</script>

<style lang="scss">
@use 'sass:list';

.template-menu {
    min-width: 240px;
    padding: 6px;

    &__title {
        padding: 8px 10px 6px;
        font-size: 12px;
        color: $color-text-secondary;
    }

    &__item,
    &__action {
        @include reset-button;
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 9px 10px;
        border-radius: $radius-sm;
        text-align: left;

        &:hover {
            background: $color-surface-muted;
        }
    }

    &__marker {
        width: 4px;
        height: 18px;
        border-radius: 2px;

        @each $name, $colors in $shift-colors {
            &--#{$name} {
                background: list.nth($colors, 1);
            }
        }
    }

    &__name {
        flex: 1;
        font-weight: 500;
    }

    &__time {
        font-size: 12px;
        color: $color-text-secondary;
    }

    &__extra {
        margin-top: 4px;
        padding-top: 4px;
        border-top: 1px solid $color-border;
    }

    &__action {
        color: $color-text-secondary;

        &:hover {
            color: $color-text;
        }

        &--danger:hover {
            background: $color-danger-light;
            color: $color-danger;
        }
    }
}
</style>
