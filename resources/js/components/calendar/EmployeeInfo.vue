<template>
    <div class="employee">
        <img v-if="employee.avatar && !hasImageError" class="employee__avatar" :src="employee.avatar" :alt="employee.full_name" loading="lazy" @error="hasImageError = true" />
        <span v-else class="employee__avatar employee__avatar--initials">{{ employee.initials }}</span>
        <div class="employee__info">
            <span class="employee__name" :title="employee.full_name">{{ employee.full_name }}</span>
            <span class="employee__meta">{{ employee.position }} · {{ hours }}</span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import type { Employee } from '@/types';

defineProps<{ employee: Employee; hours: string }>();

const hasImageError = ref(false);
</script>

<style lang="scss">
.employee {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;

    &__avatar {
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        border-radius: $radius-sm;
        object-fit: cover;

        &--initials {
            display: flex;
            align-items: center;
            justify-content: center;
            background: $color-accent-light;
            font-size: 11px;
            font-weight: 600;
            color: $color-accent;
        }
    }

    &__info {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    &__name {
        font-size: 13px;
        font-weight: 500;
        @include text-ellipsis;
    }

    &__meta {
        font-size: 11px;
        color: $color-text-secondary;
    }
}
</style>
