<template>
    <Teleport to="body">
        <TransitionGroup class="toasts" name="toast" tag="div">
            <div v-for="toast in toasts" :key="toast.id" class="toasts__item" :class="`toasts__item--${toast.type}`">
                <AppIcon :name="toast.type === 'error' ? 'alert' : 'check'" :size="18" class="toasts__icon" />
                <span class="toasts__message">{{ toast.message }}</span>
                <button class="toasts__close" type="button" aria-label="Закрыть" @click="dismiss(toast.id)">
                    <AppIcon name="close" :size="14" />
                </button>
            </div>
        </TransitionGroup>
    </Teleport>
</template>

<script setup lang="ts">
import AppIcon from './AppIcon.vue';
import { useToast } from '@/composables/useToast';

const { toasts, dismiss } = useToast();
</script>

<style lang="scss">
.toasts {
    position: fixed;
    right: 24px;
    bottom: 24px;
    z-index: 100;
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 340px;

    &__item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 14px;
        border-radius: $radius-lg;
        background: $color-dark;
        color: #fff;
        box-shadow: $shadow-popover;
    }

    &__item--success &__icon {
        color: $color-success;
    }

    &__item--error &__icon {
        color: $color-danger;
    }

    &__item--info &__icon {
        color: $color-accent;
    }

    &__message {
        flex: 1;
        font-size: 13px;
        line-height: 1.45;
    }

    &__close {
        @include reset-button;
        display: flex;
        opacity: 0.6;

        &:hover {
            opacity: 1;
        }
    }
}

.toast-enter-active,
.toast-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(8px);
}
</style>
