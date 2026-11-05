<template>
    <component
        :is="tag"
        class="button"
        :class="[`button--${variant}`, `button--${size}`, { 'button--loading': loading, 'button--icon-only': iconOnly }]"
        :type="tag === 'button' ? type : undefined"
        :disabled="tag === 'button' ? disabled || loading : undefined"
    >
        <span v-if="loading" class="button__spinner" />
        <AppIcon v-else-if="icon" :name="icon" :size="size === 'sm' ? 16 : 18" />
        <span v-if="$slots.default" class="button__text"><slot /></span>
    </component>
</template>

<script setup lang="ts">
import AppIcon, { type IconName } from './AppIcon.vue';

withDefaults(
    defineProps<{
        variant?: 'primary' | 'accent' | 'accent-light' | 'secondary' | 'tertiary' | 'ghost' | 'danger';
        size?: 'md' | 'sm';
        type?: 'button' | 'submit';
        tag?: string;
        icon?: IconName;
        iconOnly?: boolean;
        loading?: boolean;
        disabled?: boolean;
    }>(),
    {
        variant: 'primary',
        size: 'md',
        type: 'button',
        tag: 'button',
    },
);
</script>

<style lang="scss">
.button {
    @include reset-button;
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 40px;
    padding: 0 16px;
    border: 1px solid transparent;
    border-radius: $radius-md;
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
    transition: background-color $transition, border-color $transition, color $transition, opacity $transition;

    &:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    &--sm {
        height: 32px;
        padding: 0 12px;
        border-radius: $radius-sm;
        font-size: 13px;
    }

    &--icon-only {
        width: 40px;
        padding: 0;

        &.button--sm {
            width: 32px;
        }
    }

    &--primary {
        background: $color-dark;
        color: #fff;

        &:not(:disabled):hover {
            background: $color-dark-hover;
        }

        &:not(:disabled):active {
            background: $color-dark-active;
        }
    }

    &--accent {
        background: $color-accent;
        color: #fff;

        &:not(:disabled):hover {
            background: $color-accent-hover;
        }

        &:not(:disabled):active {
            background: $color-accent-active;
        }
    }

    &--accent-light {
        background: $color-accent-light;
        color: $color-accent;

        &:not(:disabled):hover {
            background: $color-accent-light-hover;
        }

        &:not(:disabled):active {
            background: #cdd8fc;
        }
    }

    &--secondary {
        background: $color-surface-muted;
        color: $color-text;

        &:not(:disabled):hover {
            background: $color-surface-hover;
        }

        &:not(:disabled):active {
            background: #e3e6ea;
        }
    }

    &--tertiary {
        border-color: $color-border;
        background: $color-surface;

        &:not(:disabled):hover {
            border-color: $color-border-dashed;
            background: $color-surface-muted;
        }
    }

    &--ghost {
        color: $color-text-secondary;

        &:not(:disabled):hover {
            background: $color-surface-muted;
            color: $color-text;
        }
    }

    &--danger {
        background: $color-danger-light;
        color: $color-danger;

        &:not(:disabled):hover {
            background: #fbe1e1;
        }
    }

    &--loading {
        cursor: progress;
    }

    &__spinner {
        width: 16px;
        height: 16px;
        border: 2px solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: button-spin 0.7s linear infinite;
    }
}

@keyframes button-spin {
    to {
        transform: rotate(360deg);
    }
}
</style>
