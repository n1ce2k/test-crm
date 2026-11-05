<template>
    <div class="layout" :class="{ 'layout--collapsed': isCollapsed }">
        <aside class="sidebar">
            <div class="sidebar__header">
                <RouterLink class="sidebar__logo" :to="{ name: 'home' }">CRM</RouterLink>
                <button class="sidebar__notify" type="button" aria-label="Уведомления">
                    <AppIcon name="bell" :size="18" />
                </button>
            </div>

            <nav class="sidebar__nav">
                <RouterLink
                    v-for="item in menu"
                    :key="item.label"
                    class="sidebar__link"
                    :class="{ 'sidebar__link--active': item.isActive() }"
                    :to="item.to"
                    :title="item.label"
                >
                    <AppIcon :name="item.icon" :size="18" />
                    <span class="sidebar__link-text">{{ item.label }}</span>
                </RouterLink>
            </nav>

            <button class="sidebar__collapse" type="button" @click="toggleCollapsed">
                <AppIcon name="sidebar" :size="18" />
                <span class="sidebar__link-text">{{ isCollapsed ? 'Развернуть меню' : 'Свернуть меню' }}</span>
            </button>
        </aside>

        <main class="layout__content">
            <RouterView :key="$route.fullPath" />
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import AppIcon, { type IconName } from '@/components/ui/AppIcon.vue';

const STORAGE_KEY = 'sidebar-collapsed';

const route = useRoute();
const isCollapsed = ref(localStorage.getItem(STORAGE_KEY) === '1');

const menu: { label: string; icon: IconName; to: string; isActive: () => boolean }[] = [
    { label: 'Главная', icon: 'home', to: '/', isActive: () => route.path === '/' },
    { label: 'Смены', icon: 'calendar', to: '/schedules', isActive: () => route.path.startsWith('/schedules') },
];

function toggleCollapsed(): void {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem(STORAGE_KEY, isCollapsed.value ? '1' : '0');
}
</script>

<style lang="scss">
.layout {
    display: flex;
    min-height: 100vh;

    &__content {
        flex: 1;
        min-width: 0;
        padding: 28px 32px 32px;
    }
}

.sidebar {
    position: sticky;
    top: 0;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    width: $sidebar-width;
    height: 100vh;
    padding: 20px 16px;
    background: $color-surface;
    transition: width $transition;

    &__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 36px;
        margin-bottom: 20px;
        padding: 0 8px;
    }

    &__logo {
        font-size: 18px;
        font-weight: 700;
    }

    &__notify {
        @include reset-button;
        display: flex;
        padding: 6px;
        border-radius: $radius-sm;
        color: $color-text;

        &:hover {
            background: $color-surface-muted;
        }
    }

    &__nav {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    &__link,
    &__collapse {
        display: flex;
        align-items: center;
        gap: 10px;
        height: 40px;
        padding: 0 12px;
        border-radius: $radius-md;
        font-size: 14px;
        font-weight: 500;
        transition: background-color $transition;

        &:hover {
            background: $color-surface-muted;
        }
    }

    &__link--active {
        background: $color-surface-muted;
    }

    &__collapse {
        @include reset-button;
        margin-top: auto;
    }

    &__link-text {
        @include text-ellipsis;
    }

    .layout--collapsed & {
        width: $sidebar-width-collapsed;

        .sidebar__header {
            justify-content: center;
        }

        .sidebar__logo {
            display: none;
        }

        .sidebar__link,
        .sidebar__collapse {
            justify-content: center;
            padding: 0;
        }

        .sidebar__link-text {
            display: none;
        }
    }
}

@include breakpoint(1100px) {
    .layout__content {
        padding: 20px;
    }
}
</style>
