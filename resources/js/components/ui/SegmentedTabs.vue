<template>
    <div class="tabs" role="tablist">
        <button
            v-for="option in options"
            :key="option.value"
            class="tabs__item"
            :class="{ 'tabs__item--active': option.value === modelValue }"
            type="button"
            role="tab"
            :aria-selected="option.value === modelValue"
            @click="$emit('update:modelValue', option.value)"
        >
            {{ option.label }}
        </button>
    </div>
</template>

<script setup lang="ts" generic="T extends string">
defineProps<{ modelValue: T; options: { value: T; label: string }[] }>();

defineEmits<{ 'update:modelValue': [value: T] }>();
</script>

<style lang="scss">
.tabs {
    display: inline-flex;
    padding: 3px;
    border-radius: $radius-md;
    background: $color-surface-muted;

    &__item {
        @include reset-button;
        min-width: 112px;
        height: 34px;
        padding: 0 18px;
        border-radius: $radius-sm;
        font-size: 14px;
        font-weight: 500;
        transition: background-color $transition, color $transition;

        &:hover:not(&--active) {
            background: $color-surface-hover;
        }

        &--active {
            background: $color-dark;
            color: #fff;
        }
    }
}
</style>
