<template>
    <span class="status-badge" :class="`status-badge--${status}`">{{ label }}</span>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { ScheduleStatus } from '@/types';

const props = defineProps<{ status: ScheduleStatus }>();

const label = computed(() => (props.status === 'published' ? 'Опубликован' : 'Черновик'));
</script>

<style lang="scss">
.status-badge {
    display: inline-flex;
    align-items: center;
    height: 22px;
    padding: 0 10px;
    border-radius: 11px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
    color: #fff;

    &--published {
        background: $color-accent;
    }

    &--draft {
        background: $color-dark;
    }
}
</style>
