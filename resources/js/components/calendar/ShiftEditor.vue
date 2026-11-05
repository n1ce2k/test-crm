<template>
    <form class="shift-editor" @submit.prevent="submit">
        <div class="shift-editor__head">
            <p class="shift-editor__title">{{ title }}</p>
            <p class="shift-editor__subtitle">{{ subtitle }}</p>
        </div>

        <div class="shift-editor__templates">
            <button
                v-for="template in templates"
                :key="template.id"
                class="shift-editor__template"
                :class="[`shift-editor__template--${template.color}`, { 'shift-editor__template--active': templateId === template.id }]"
                type="button"
                @click="useTemplate(template)"
            >
                {{ template.name }}
            </button>
        </div>

        <div class="shift-editor__fields">
            <FormField label="Начало">
                <input v-model="start" class="input" type="time" step="900" required @input="templateId = null" />
            </FormField>
            <FormField label="Конец">
                <input v-model="end" class="input" type="time" step="900" required @input="templateId = null" />
            </FormField>
        </div>

        <p v-if="error" class="shift-editor__error">{{ error }}</p>

        <div class="shift-editor__actions">
            <BaseButton v-if="removable" variant="danger" size="sm" icon="trash" :loading="removing" @click="$emit('remove')">
                Удалить
            </BaseButton>
            <BaseButton class="shift-editor__apply" variant="accent" size="sm" type="submit" :disabled="!isValid" :loading="saving">
                {{ submitLabel }}
            </BaseButton>
        </div>
    </form>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import FormField from '@/components/ui/FormField.vue';
import { isValidTimeRange } from '@/utils/time';
import type { ShiftTemplate, ShiftTime } from '@/types';

const props = withDefaults(
    defineProps<{
        title: string;
        subtitle: string;
        templates: ShiftTemplate[];
        initial?: ShiftTime;
        removable?: boolean;
        submitLabel?: string;
        saving?: boolean;
        removing?: boolean;
    }>(),
    { submitLabel: 'Применить' },
);

const emit = defineEmits<{ submit: [time: ShiftTime]; remove: [] }>();

const start = ref(props.initial?.start_time ?? '09:00');
const end = ref(props.initial?.end_time ?? '18:00');
const templateId = ref<number | null>(props.initial?.shift_template_id ?? null);

const isValid = computed(() => isValidTimeRange(start.value, end.value));
const error = computed(() => (start.value && end.value && !isValid.value ? 'Окончание должно быть позже начала' : ''));

function useTemplate(template: ShiftTemplate): void {
    start.value = template.start_time;
    end.value = template.end_time;
    templateId.value = template.id;
}

function submit(): void {
    if (isValid.value) {
        emit('submit', { start_time: start.value, end_time: end.value, shift_template_id: templateId.value });
    }
}
</script>

<style lang="scss">
@use 'sass:list';

.shift-editor {
    width: 300px;
    padding: 16px;

    &__title {
        font-size: 15px;
        font-weight: 600;
    }

    &__subtitle {
        margin-top: 2px;
        font-size: 12px;
        color: $color-text-secondary;
    }

    &__templates {
        display: flex;
        gap: 6px;
        margin: 14px 0 12px;
    }

    &__template {
        @include reset-button;
        flex: 1;
        height: 30px;
        border-radius: $radius-sm;
        font-size: 12px;
        font-weight: 500;
        transition: background-color $transition, color $transition;

        @each $name, $colors in $shift-colors {
            &--#{$name} {
                background: list.nth($colors, 2);
                color: list.nth($colors, 1);

                &.shift-editor__template--active {
                    background: list.nth($colors, 1);
                    color: #fff;
                }
            }
        }
    }

    &__fields {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    &__error {
        margin-top: 8px;
        font-size: 12px;
        color: $color-danger;
    }

    &__actions {
        display: flex;
        gap: 8px;
        margin-top: 16px;
    }

    &__apply {
        margin-left: auto;
    }
}
</style>
