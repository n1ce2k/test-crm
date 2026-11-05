<template>
    <div class="empty-page">
        <h1 class="empty-page__title">Страница не найдена</h1>
        <p class="empty-page__text">Возможно, её удалили или ссылка указана неверно.</p>
        <BaseButton tag="RouterLink" :to="{ name: 'schedules' }" variant="accent">К списку графиков</BaseButton>
    </div>
</template>

<script setup lang="ts">
import BaseButton from '@/components/ui/BaseButton.vue';
</script>

<style lang="scss">
.empty-page {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    min-height: 60vh;
    text-align: center;

    &__title {
        font-size: 24px;
        font-weight: 600;
    }

    &__text {
        margin-bottom: 8px;
        color: $color-text-secondary;
    }
}
</style>
