<template>
    <div class="schedules-page">
        <header class="page-header">
            <div class="page-header__info">
                <h1 class="page-header__title">Все графики</h1>
                <p class="page-header__subtitle">Создавайте, редактируйте и публикуйте графики по точкам региона</p>
            </div>
            <BaseButton variant="accent" icon="plus" @click="isCreateOpen = true">Создать график</BaseButton>
        </header>

        <div class="schedules-page__filters">
            <SearchField v-model="search" class="schedules-page__search" placeholder="Поиск по названию" theme="white" />
            <BaseSelect v-model="status" :options="statusOptions" placeholder="Статус" />
        </div>

        <div v-if="isLoading && !schedules.length" class="schedules-page__skeleton">
            <div v-for="index in 2" :key="index" class="schedules-page__skeleton-item" />
        </div>

        <div v-else-if="loadError" class="state-block">
            <p class="state-block__text">{{ loadError }}</p>
            <BaseButton variant="secondary" @click="load">Повторить</BaseButton>
        </div>

        <div v-else class="schedules-page__sections" :class="{ 'schedules-page__sections--loading': isLoading }">
            <ScheduleSection
                v-for="section in visibleSections"
                :key="section.status"
                :title="section.title"
                :schedules="section.items"
                :collapsed="collapsed[section.status]"
                :empty-text="search ? 'Ничего не найдено' : undefined"
                @toggle="collapsed[section.status] = !collapsed[section.status]"
            />
        </div>

        <CreateScheduleModal v-if="isCreateOpen" @close="isCreateOpen = false" @created="onCreated" />
    </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseSelect from '@/components/ui/BaseSelect.vue';
import SearchField from '@/components/ui/SearchField.vue';
import ScheduleSection from '@/components/schedules/ScheduleSection.vue';
import CreateScheduleModal from '@/components/schedules/CreateScheduleModal.vue';
import { fetchSchedules } from '@/api/schedules';
import { getErrorMessage } from '@/api/http';
import type { Schedule, ScheduleStatus } from '@/types';

const router = useRouter();

const schedules = ref<Schedule[]>([]);
const search = ref('');
const status = ref<ScheduleStatus | ''>('');
const isLoading = ref(true);
const loadError = ref('');
const isCreateOpen = ref(false);
const collapsed = reactive<Record<ScheduleStatus, boolean>>({ published: false, draft: false });

const statusOptions: { value: ScheduleStatus | ''; label: string }[] = [
    { value: '', label: 'Все статусы' },
    { value: 'published', label: 'Опубликован' },
    { value: 'draft', label: 'Черновик' },
];

const sections: { status: ScheduleStatus; title: string }[] = [
    { status: 'published', title: 'Опубликован' },
    { status: 'draft', title: 'Черновик' },
];

const visibleSections = computed(() =>
    sections
        .filter((section) => !status.value || section.status === status.value)
        .map((section) => ({
            ...section,
            items: schedules.value.filter((schedule) => schedule.status === section.status),
        })),
);

let controller: AbortController | null = null;
let searchTimer: number | undefined;

async function load(): Promise<void> {
    controller?.abort();
    controller = new AbortController();
    isLoading.value = true;
    loadError.value = '';

    try {
        schedules.value = await fetchSchedules({ search: search.value, status: status.value }, controller.signal);
        isLoading.value = false;
    } catch (error) {
        if ((error as { name?: string }).name === 'CanceledError') {
            return;
        }

        loadError.value = getErrorMessage(error, 'Не удалось загрузить графики');
        isLoading.value = false;
    }
}

function onCreated(schedule: Schedule): void {
    isCreateOpen.value = false;
    router.push({ name: 'schedule', params: { id: schedule.id } });
}

watch(search, () => {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(load, 250);
});

watch(status, load);

onMounted(load);

onBeforeUnmount(() => {
    controller?.abort();
    window.clearTimeout(searchTimer);
});
</script>

<style lang="scss">
.schedules-page {
    max-width: 1180px;

    &__filters {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
    }

    &__search {
        flex: 0 1 360px;
    }

    &__sections {
        transition: opacity $transition;

        &--loading {
            opacity: 0.6;
        }
    }

    &__skeleton {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    &__skeleton-item {
        height: 120px;
        border-radius: $radius-xl;
        background: linear-gradient(90deg, $color-surface 0%, $color-surface-muted 50%, $color-surface 100%);
        background-size: 200% 100%;
        animation: skeleton 1.2s ease infinite;
    }
}

@keyframes skeleton {
    from {
        background-position: 100% 0;
    }

    to {
        background-position: -100% 0;
    }
}
</style>
