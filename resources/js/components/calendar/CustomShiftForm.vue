<template>
    <form class="custom-shift" @submit.prevent="submit">
        <h2 class="custom-shift__title">Своя смена</h2>

        <div class="custom-shift__fields">
            <FormField label="Начало">
                <input v-model="start" class="input" type="time" step="900" required />
            </FormField>
            <FormField label="Конец">
                <input v-model="end" class="input" type="time" step="900" required />
            </FormField>
        </div>

        <p v-if="error" class="custom-shift__error">{{ error }}</p>
        <p v-else class="custom-shift__duration">Длительность: {{ duration }}</p>

        <BaseButton class="custom-shift__submit" variant="accent-light" type="submit" icon="plus" :disabled="!canSubmit" :loading="loading">
            Поставить в выделенные
        </BaseButton>
        <p v-if="!selectedCount" class="custom-shift__hint">Сначала выделите ячейки в календаре</p>
    </form>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import FormField from '@/components/ui/FormField.vue';
import { durationInMinutes, formatHours, isValidTimeRange } from '@/utils/time';
import type { ShiftTime } from '@/types';

const props = defineProps<{ selectedCount: number; loading?: boolean }>();

const emit = defineEmits<{ submit: [time: ShiftTime] }>();

const start = ref('09:00');
const end = ref('17:00');

const isValid = computed(() => isValidTimeRange(start.value, end.value));
const error = computed(() => (start.value && end.value && !isValid.value ? 'Окончание должно быть позже начала' : ''));
const duration = computed(() => (isValid.value ? formatHours(durationInMinutes(start.value, end.value)) : '—'));
const canSubmit = computed(() => isValid.value && props.selectedCount > 0);

function submit(): void {
    if (canSubmit.value) {
        emit('submit', { start_time: start.value, end_time: end.value, shift_template_id: null });
    }
}
</script>

<style lang="scss">
.custom-shift {
    &__title {
        margin-bottom: 12px;
        font-size: 14px;
        font-weight: 600;
    }

    &__fields {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    &__duration,
    &__error,
    &__hint {
        margin-top: 8px;
        font-size: 12px;
        color: $color-text-secondary;
    }

    &__error {
        color: $color-danger;
    }

    &__submit {
        width: 100%;
        margin-top: 12px;
    }
}
</style>
