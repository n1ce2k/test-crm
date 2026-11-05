<template>
    <svg
        class="icon"
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <path v-for="(path, index) in paths" :key="index" :d="path" />
    </svg>
</template>

<script setup lang="ts">
import { computed } from 'vue';

export type IconName =
    | 'home'
    | 'calendar'
    | 'bell'
    | 'search'
    | 'chevron-down'
    | 'chevron-up'
    | 'chevron-left'
    | 'chevron-right'
    | 'plus'
    | 'pin'
    | 'users'
    | 'clock'
    | 'sidebar'
    | 'close'
    | 'edit'
    | 'trash'
    | 'template'
    | 'check'
    | 'alert';

const props = withDefaults(defineProps<{ name: IconName; size?: number }>(), {
    size: 18,
});

const icons: Record<IconName, string[]> = {
    home: ['M4 10.5 12 4l8 6.5', 'M6 9v10h12V9', 'M10 19v-5h4v5'],
    calendar: ['M5 6h14v14H5z', 'M5 10h14', 'M9 4v4', 'M15 4v4'],
    bell: ['M6 16V11a6 6 0 0 1 12 0v5l1.5 2h-15z', 'M10 20.5a2 2 0 0 0 4 0'],
    search: ['M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14z', 'm20 20-3.5-3.5'],
    'chevron-down': ['m6 9 6 6 6-6'],
    'chevron-up': ['m6 15 6-6 6 6'],
    'chevron-left': ['m15 6-6 6 6 6'],
    'chevron-right': ['m9 6 6 6-6 6'],
    plus: ['M12 5v14', 'M5 12h14'],
    pin: ['M12 21s-6-5.6-6-11a6 6 0 0 1 12 0c0 5.4-6 11-6 11z', 'M12 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z'],
    users: ['M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z', 'M2.5 20c.6-3.4 3.2-5.5 6.5-5.5s5.9 2.1 6.5 5.5', 'M16 4.5a3.5 3.5 0 0 1 0 6.5', 'M18 14.8c2 .7 3.2 2.6 3.5 5.2'],
    clock: ['M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z', 'M12 7.5V12l3 2'],
    sidebar: ['M4 5h16v14H4z', 'M9.5 5v14'],
    close: ['m6 6 12 12', 'M18 6 6 18'],
    edit: ['M4 20h4L19 9l-4-4L4 16z', 'm13.5 6.5 4 4'],
    trash: ['M4.5 7h15', 'M9.5 7V4.5h5V7', 'M6.5 7l1 13h9l1-13', 'M10 11v5.5', 'M14 11v5.5'],
    template: ['M5 4h14v6H5z', 'M5 14h6v6H5z', 'M15 14h4', 'M15 18h4'],
    check: ['m5 12.5 4.5 4.5L19 7.5'],
    alert: ['M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z', 'M12 8v5', 'M12 16.5v.01'],
};

const paths = computed(() => icons[props.name]);
</script>

<style lang="scss">
.icon {
    flex-shrink: 0;
}
</style>
