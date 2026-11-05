import { readonly, ref } from 'vue';

export type ToastType = 'success' | 'error' | 'info';

export interface Toast {
    id: number;
    type: ToastType;
    message: string;
}

const toasts = ref<Toast[]>([]);
let nextId = 1;

function dismiss(id: number): void {
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
}

function show(message: string, type: ToastType = 'info', timeout = 3500): void {
    const id = nextId++;
    toasts.value.push({ id, type, message });
    window.setTimeout(() => dismiss(id), timeout);
}

export function useToast() {
    return {
        toasts: readonly(toasts),
        dismiss,
        success: (message: string) => show(message, 'success'),
        error: (message: string) => show(message, 'error', 5000),
        info: (message: string) => show(message, 'info'),
    };
}
