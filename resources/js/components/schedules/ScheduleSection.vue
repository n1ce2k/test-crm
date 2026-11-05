<template>
    <section class="schedule-section" :class="{ 'schedule-section--collapsed': collapsed }">
        <button class="schedule-section__header" type="button" :aria-expanded="!collapsed" @click="$emit('toggle')">
            <span class="schedule-section__title">
                {{ title }}
                <span class="schedule-section__count">{{ schedules.length }}</span>
            </span>
            <span class="schedule-section__toggle">
                <AppIcon :name="collapsed ? 'chevron-down' : 'chevron-up'" :size="16" />
            </span>
        </button>

        <div v-if="!collapsed" class="schedule-section__body">
            <div v-if="schedules.length" class="schedule-section__grid">
                <ScheduleCard v-for="schedule in schedules" :key="schedule.id" :schedule="schedule" />
            </div>
            <p v-else class="schedule-section__empty">{{ emptyText }}</p>
        </div>
    </section>
</template>

<script setup lang="ts">
import AppIcon from '@/components/ui/AppIcon.vue';
import ScheduleCard from './ScheduleCard.vue';
import type { Schedule } from '@/types';

withDefaults(defineProps<{ title: string; schedules: Schedule[]; collapsed: boolean; emptyText?: string }>(), {
    emptyText: 'Здесь пока нет графиков',
});

defineEmits<{ toggle: [] }>();
</script>

<style lang="scss">
.schedule-section {
    @include panel;

    & + & {
        margin-top: 12px;
    }

    &__header {
        @include reset-button;
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 16px 20px;
        text-align: left;
    }

    &__title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 18px;
        font-weight: 600;
    }

    &__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: $radius-xs;
        background: $color-surface-muted;
        font-size: 11px;
        font-weight: 600;
    }

    &__toggle {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: $color-surface-muted;
        transition: background-color $transition;
    }

    &__header:hover &__toggle {
        background: $color-surface-hover;
    }

    &__body {
        padding: 0 20px 20px;
    }

    &__grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 12px;
    }

    &__empty {
        padding: 24px 0 8px;
        text-align: center;
        color: $color-text-secondary;
    }
}
</style>
