import { createRouter, createWebHistory } from 'vue-router';
import MainLayout from '@/components/layout/MainLayout.vue';

export const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: MainLayout,
            children: [
                {
                    path: '',
                    name: 'home',
                    component: () => import('@/pages/HomePage.vue'),
                    meta: { title: 'Главная' },
                },
                {
                    path: 'schedules',
                    name: 'schedules',
                    component: () => import('@/pages/SchedulesPage.vue'),
                    meta: { title: 'Все графики' },
                },
                {
                    path: 'schedules/:id(\\d+)',
                    name: 'schedule',
                    component: () => import('@/pages/SchedulePage.vue'),
                    props: (route) => ({ id: Number(route.params.id) }),
                    meta: { title: 'График' },
                },
                {
                    path: ':pathMatch(.*)*',
                    name: 'not-found',
                    component: () => import('@/pages/NotFoundPage.vue'),
                    meta: { title: 'Страница не найдена' },
                },
            ],
        },
    ],
});

router.afterEach((to) => {
    document.title = to.meta.title ? `${to.meta.title} — CRM` : 'CRM';
});
