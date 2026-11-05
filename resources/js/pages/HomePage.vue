<template>
    <section class="home">
        <h1 class="home__title">Тут что-то должно быть... наверное</h1>
    </section>
</template>

<style lang="scss">
.home {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 60px);

    &__title {
        font-size: 28px;
        font-weight: 600;
        color: $color-text-secondary;
        text-align: center;
    }
}
</style>
