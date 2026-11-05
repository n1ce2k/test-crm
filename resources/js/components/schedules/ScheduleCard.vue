<template>
    <RouterLink class="schedule-card" :to="{ name: 'schedule', params: { id: schedule.id } }">
        <div class="schedule-card__head">
            <span class="schedule-card__address">
                <AppIcon name="pin" :size="14" />
                <span class="schedule-card__address-text">{{ schedule.address }}</span>
            </span>
            <StatusBadge :status="schedule.status" />
        </div>

        <h3 class="schedule-card__title">{{ schedule.name }}</h3>

        <div class="schedule-card__progress">
            <div class="schedule-card__progress-row">
                <span>Прогресс</span>
                <span>{{ schedule.progress }}%</span>
            </div>
            <div class="schedule-card__progress-bar">
                <span class="schedule-card__progress-fill" :style="{ width: `${schedule.progress}%` }" />
            </div>
        </div>

        <div class="schedule-card__footer">
            <span class="schedule-card__meta">
                <AppIcon name="users" :size="14" />
                {{ schedule.employees_count }} чел.
            </span>
            <span class="schedule-card__meta" :title="`Обновлён ${updatedAt}`">
                <AppIcon name="clock" :size="14" />
                {{ updatedAt }}
            </span>
        </div>
    </RouterLink>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import AppIcon from '@/components/ui/AppIcon.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { shortDateLabel } from '@/utils/date';
import type { Schedule } from '@/types';

const props = defineProps<{ schedule: Schedule }>();

const updatedAt = computed(() => shortDateLabel(props.schedule.updated_at));
</script>

<style lang="scss">
.schedule-card {
    display: flex;
    flex-direction: column;
    padding: 16px;
    border: 1px solid transparent;
    border-radius: $radius-lg;
    background: $color-surface-muted;
    transition: border-color $transition, background-color $transition, box-shadow $transition;

    &:hover {
        border-color: $color-border;
        background: $color-surface;
        box-shadow: $shadow-card-hover;
    }

    &__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    &__address {
        display: flex;
        align-items: center;
        gap: 4px;
        min-width: 0;
        font-size: 12px;
        color: $color-text-secondary;
    }

    &__address-text {
        @include text-ellipsis;
    }

    &__title {
        margin: 12px 0 28px;
        font-size: 18px;
        font-weight: 600;
        @include text-ellipsis;
    }

    &__progress-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 14px;
    }

    &__progress-bar {
        height: 4px;
        overflow: hidden;
        border-radius: 2px;
        background: $color-border;
    }

    &__progress-fill {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: $color-dark;
        transition: width 0.3s ease;
    }

    &__footer {
        display: flex;
        justify-content: space-between;
        margin-top: 16px;
    }

    &__meta {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: $color-text-secondary;
    }
}
</style>
