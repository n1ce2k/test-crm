<template>
    <div class="schedule-page">
        <div v-if="editor.isLoading.value" class="schedule-page__loader">
            <span class="schedule-page__spinner" />
            Загружаем график…
        </div>

        <div v-else-if="editor.loadError.value || !editor.schedule.value" class="state-block">
            <p class="state-block__text">{{ editor.loadError.value || 'График не найден' }}</p>
            <div class="schedule-page__error-actions">
                <BaseButton variant="secondary" @click="editor.load">Повторить</BaseButton>
                <BaseButton tag="RouterLink" variant="ghost" :to="{ name: 'schedules' }">К списку графиков</BaseButton>
            </div>
        </div>

        <ScheduleWorkspace v-else :editor="editor" />
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import ScheduleWorkspace from '@/components/calendar/ScheduleWorkspace.vue';
import { useScheduleEditor } from '@/composables/useScheduleEditor';

const props = defineProps<{ id: number }>();

const editor = useScheduleEditor(props.id);

onMounted(editor.load);
</script>

<style lang="scss">
.schedule-page {
    &__loader {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 60vh;
        color: $color-text-secondary;
    }

    &__spinner {
        width: 18px;
        height: 18px;
        border: 2px solid $color-accent;
        border-right-color: transparent;
        border-radius: 50%;
        animation: button-spin 0.7s linear infinite;
    }

    &__error-actions {
        display: flex;
        gap: 8px;
    }
}
</style>
