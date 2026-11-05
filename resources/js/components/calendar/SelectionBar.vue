<template>
    <div class="selection-bar">
        <div class="selection-bar__summary">
            <span class="selection-bar__label">Выделено:</span>
            <span class="selection-bar__count">{{ size }} {{ plural(size, ['ячейка', 'ячейки', 'ячеек']) }}</span>
            <span class="selection-bar__details">
                {{ employeesCount }} {{ plural(employeesCount, ['сотрудник', 'сотрудника', 'сотрудников']) }} ·
                {{ daysCount }} дн.
            </span>
        </div>

        <div class="selection-bar__actions">
            <button ref="templateTrigger" class="selection-bar__action" type="button" @mousedown.stop @click="toggleTemplates">
                <AppIcon name="template" :size="16" />
                Применить шаблон
            </button>
            <button ref="editTrigger" class="selection-bar__action" type="button" @mousedown.stop @click="openEditor">
                <AppIcon name="edit" :size="16" />
                Изменить
            </button>
            <button class="selection-bar__action" type="button" :disabled="!filledCount" @click="$emit('clear')">
                <AppIcon name="trash" :size="16" />
                Очистить
            </button>
            <button class="selection-bar__action" type="button" @click="$emit('reset')">
                <AppIcon name="close" :size="16" />
                Снять
            </button>
        </div>

        <FloatingPanel v-if="templatesAnchor" :anchor="templatesAnchor" align="end" @close="templatesAnchor = null">
            <TemplateMenu :templates="templates" title="Применить к выделенным" @choose="chooseTemplate" />
        </FloatingPanel>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AppIcon from '@/components/ui/AppIcon.vue';
import FloatingPanel from '@/components/ui/FloatingPanel.vue';
import TemplateMenu from './TemplateMenu.vue';
import { plural } from '@/utils/plural';
import type { ShiftTemplate } from '@/types';

defineProps<{
    size: number;
    employeesCount: number;
    daysCount: number;
    filledCount: number;
    templates: ShiftTemplate[];
}>();

const emit = defineEmits<{
    'apply-template': [template: ShiftTemplate];
    edit: [rect: DOMRect];
    clear: [];
    reset: [];
}>();

const templateTrigger = ref<HTMLElement | null>(null);
const editTrigger = ref<HTMLElement | null>(null);
const templatesAnchor = ref<DOMRect | null>(null);

function toggleTemplates(): void {
    templatesAnchor.value = templatesAnchor.value ? null : templateTrigger.value?.getBoundingClientRect() ?? null;
}

function chooseTemplate(template: ShiftTemplate): void {
    templatesAnchor.value = null;
    emit('apply-template', template);
}

function openEditor(): void {
    if (editTrigger.value) {
        emit('edit', editTrigger.value.getBoundingClientRect());
    }
}
</script>

<style lang="scss">
.selection-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    min-height: 46px;
    padding: 6px 8px 6px 16px;
    border-radius: $radius-md;
    background: $color-dark;
    color: #fff;
    animation: selection-bar-in 0.16s ease;

    &__summary {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        font-size: 13px;
        white-space: nowrap;
    }

    &__label {
        color: rgba(#fff, 0.7);
    }

    &__count {
        padding: 3px 8px;
        border-radius: $radius-xs;
        background: rgba(#fff, 0.14);
        font-weight: 600;
    }

    &__details {
        color: rgba(#fff, 0.85);
    }

    &__actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 2px;
    }

    &__action {
        @include reset-button;
        display: flex;
        align-items: center;
        gap: 6px;
        height: 34px;
        padding: 0 10px;
        border-radius: $radius-sm;
        font-size: 13px;
        white-space: nowrap;
        transition: background-color $transition;

        &:hover:not(:disabled) {
            background: rgba(#fff, 0.12);
        }

        &:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
    }
}

@keyframes selection-bar-in {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
}
</style>
