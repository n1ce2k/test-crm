<template>
    <label class="search-field" :class="[`search-field--${theme}`, { 'search-field--filled': modelValue }]">
        <AppIcon class="search-field__icon" name="search" :size="16" />
        <input
            class="search-field__input"
            type="search"
            :value="modelValue"
            :placeholder="placeholder"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />
        <button
            v-if="modelValue"
            class="search-field__clear"
            type="button"
            aria-label="Очистить поиск"
            @click="$emit('update:modelValue', '')"
        >
            <AppIcon name="close" :size="14" />
        </button>
    </label>
</template>

<script setup lang="ts">
import AppIcon from './AppIcon.vue';

withDefaults(defineProps<{ modelValue: string; placeholder?: string; theme?: 'gray' | 'white' }>(), {
    placeholder: 'Поиск',
    theme: 'gray',
});

defineEmits<{ 'update:modelValue': [value: string] }>();
</script>

<style lang="scss">
.search-field {
    display: flex;
    align-items: center;
    gap: 8px;
    height: 40px;
    padding: 0 12px;
    border: 1px solid transparent;
    border-radius: $radius-md;
    transition: border-color $transition, background-color $transition;
    cursor: text;

    &--gray {
        background: $color-surface-muted;

        &:hover {
            background: $color-surface-hover;
        }
    }

    &--white {
        border-color: $color-border;
        background: $color-surface;

        &:hover {
            border-color: $color-border-dashed;
        }
    }

    &:focus-within {
        border-color: $color-accent;
        background: $color-surface;
    }

    &__icon {
        color: $color-text-secondary;
    }

    &__input {
        flex: 1;
        min-width: 0;
        border: 0;
        background: none;
        outline: none;
        font-size: 14px;

        &::placeholder {
            color: $color-text-secondary;
        }

        &::-webkit-search-cancel-button {
            display: none;
        }
    }

    &__clear {
        @include reset-button;
        display: flex;
        color: $color-text-secondary;

        &:hover {
            color: $color-text;
        }
    }
}
</style>
