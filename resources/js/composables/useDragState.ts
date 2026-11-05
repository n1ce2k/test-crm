import { ref } from 'vue';

export type DragPayload = { kind: 'template'; templateId: number } | { kind: 'shift'; shiftId: number };

const payload = ref<DragPayload | null>(null);

export function useDragState() {
    function start(event: DragEvent, value: DragPayload): void {
        payload.value = value;

        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = value.kind === 'shift' ? 'move' : 'copy';
            event.dataTransfer.setData('text/plain', JSON.stringify(value));
        }
    }

    function end(): void {
        payload.value = null;
    }

    return { payload, start, end };
}
