<template>
    <label class="form-field" :class="{ 'form-field--invalid': error }">
        <span class="form-field__label">{{ label }}</span>
        <slot />
        <span v-if="error" class="form-field__error">{{ error }}</span>
    </label>
</template>

<script setup lang="ts">
defineProps<{ label: string; error?: string }>();
</script>

<style lang="scss">
.form-field {
    display: flex;
    flex-direction: column;
    gap: 6px;

    &__label {
        font-size: 12px;
        color: $color-text-secondary;
    }

    &__error {
        font-size: 12px;
        color: $color-danger;
    }
}

.input {
    width: 100%;
    height: 40px;
    padding: 0 12px;
    border: 1px solid transparent;
    border-radius: $radius-md;
    background: $color-surface-muted;
    font-size: 14px;
    outline: none;
    transition: border-color $transition, background-color $transition;

    &:hover {
        background: $color-surface-hover;
    }

    &:focus {
        border-color: $color-accent;
        background: $color-surface;
    }

    &::placeholder {
        color: $color-text-secondary;
    }

    .form-field--invalid & {
        border-color: $color-danger;
    }

    &::-webkit-calendar-picker-indicator {
        opacity: 0.45;
        cursor: pointer;
    }
}
</style>
