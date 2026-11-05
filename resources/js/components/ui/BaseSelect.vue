<template>
    <div class="select">
        <button
            ref="trigger"
            class="select__trigger"
            :class="{ 'select__trigger--open': isOpen, 'select__trigger--placeholder': !selected }"
            type="button"
            :aria-expanded="isOpen"
            @mousedown.stop
            @click="toggle"
        >
            <span class="select__value">{{ selected?.label ?? placeholder }}</span>
            <AppIcon class="select__arrow" name="chevron-down" :size="16" />
        </button>

        <FloatingPanel v-if="isOpen && anchor" :anchor="anchor" @close="isOpen = false">
            <ul class="select__list" :style="{ minWidth: `${anchor.width}px` }">
                <li v-for="option in options" :key="option.value">
                    <button
                        class="select__option"
                        :class="{ 'select__option--active': option.value === modelValue }"
                        type="button"
                        @click="choose(option.value)"
                    >
                        {{ option.label }}
                        <AppIcon v-if="option.value === modelValue" name="check" :size="16" />
                    </button>
                </li>
            </ul>
        </FloatingPanel>
    </div>
</template>

<script setup lang="ts" generic="T extends string">
import { computed, ref } from 'vue';
import AppIcon from './AppIcon.vue';
import FloatingPanel from './FloatingPanel.vue';

const props = withDefaults(
    defineProps<{ modelValue: T; options: { value: T; label: string }[]; placeholder?: string }>(),
    { placeholder: 'Выберите' },
);

const emit = defineEmits<{ 'update:modelValue': [value: T] }>();

const trigger = ref<HTMLElement | null>(null);
const anchor = ref<DOMRect | null>(null);
const isOpen = ref(false);

const selected = computed(() => props.options.find((option) => option.value === props.modelValue && option.value !== ''));

function toggle(): void {
    anchor.value = trigger.value?.getBoundingClientRect() ?? null;
    isOpen.value = !isOpen.value;
}

function choose(value: T): void {
    emit('update:modelValue', value);
    isOpen.value = false;
}
</script>

<style lang="scss">
.select {
    position: relative;

    &__trigger {
        @include reset-button;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        width: 100%;
        min-width: 160px;
        height: 40px;
        padding: 0 12px;
        border: 1px solid $color-border;
        border-radius: $radius-md;
        background: $color-surface;
        transition: border-color $transition;

        &:hover {
            border-color: $color-border-dashed;
        }

        &--open {
            border-color: $color-accent;
        }

        &--open .select__arrow {
            transform: rotate(180deg);
        }
    }

    &__arrow {
        color: $color-text-secondary;
        transition: transform $transition;
    }

    &__list {
        padding: 6px;
    }

    &__option {
        @include reset-button;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        width: 100%;
        padding: 9px 10px;
        border-radius: $radius-sm;
        text-align: left;

        &:hover {
            background: $color-surface-muted;
        }

        &--active {
            color: $color-accent;
            font-weight: 600;
        }
    }
}
</style>
