<template>
    <Teleport to="body">
        <Transition name="modal" appear>
            <div class="modal" @mousedown.self="$emit('close')">
                <div class="modal__dialog" :style="{ maxWidth: `${width}px` }" role="dialog" aria-modal="true">
                    <div class="modal__header">
                        <h2 class="modal__title">{{ title }}</h2>
                        <button class="modal__close" type="button" aria-label="Закрыть" @click="$emit('close')">
                            <AppIcon name="close" :size="18" />
                        </button>
                    </div>
                    <div class="modal__body">
                        <slot />
                    </div>
                    <div v-if="$slots.footer" class="modal__footer">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted } from 'vue';
import AppIcon from './AppIcon.vue';

withDefaults(defineProps<{ title: string; width?: number }>(), { width: 520 });

const emit = defineEmits<{ close: [] }>();

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        emit('close');
    }
}

onMounted(() => document.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown));
</script>

<style lang="scss">
.modal {
    position: fixed;
    inset: 0;
    z-index: 80;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: rgba(27, 27, 31, 0.35);

    &__dialog {
        display: flex;
        flex-direction: column;
        width: 100%;
        max-height: calc(100vh - 48px);
        border-radius: $radius-xl;
        background: $color-surface;
        box-shadow: $shadow-popover;
    }

    &__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px 0;
    }

    &__title {
        font-size: 20px;
        font-weight: 600;
    }

    &__close {
        @include reset-button;
        display: flex;
        padding: 6px;
        border-radius: $radius-sm;
        color: $color-text-secondary;

        &:hover {
            background: $color-surface-muted;
            color: $color-text;
        }
    }

    &__body {
        padding: 20px 24px;
        overflow-y: auto;
        @include scrollbar;
    }

    &__footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 16px 24px 20px;
        border-top: 1px solid $color-border;
    }
}

.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.18s ease;

    .modal__dialog {
        transition: transform 0.18s ease;
    }
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;

    .modal__dialog {
        transform: translateY(8px) scale(0.98);
    }
}
</style>
