import axios, { AxiosError } from 'axios';
import type { ValidationErrors } from '@/types';

export const http = axios.create({
    baseURL: '/api/v1',
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

interface ErrorPayload {
    message?: string;
    errors?: ValidationErrors;
}

export function getErrorMessage(error: unknown, fallback = 'Что-то пошло не так. Попробуйте ещё раз.'): string {
    if (error instanceof AxiosError) {
        const payload = error.response?.data as ErrorPayload | undefined;
        const firstError = payload?.errors ? Object.values(payload.errors)[0]?.[0] : undefined;

        if (firstError) {
            return firstError;
        }

        if (!error.response) {
            return 'Нет соединения с сервером.';
        }

        if (error.response.status === 404) {
            return 'Запись не найдена.';
        }
    }

    return fallback;
}

export function getValidationErrors(error: unknown): ValidationErrors {
    if (error instanceof AxiosError && error.response?.status === 422) {
        return (error.response.data as ErrorPayload).errors ?? {};
    }

    return {};
}
