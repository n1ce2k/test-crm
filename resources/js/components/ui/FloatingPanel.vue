<template>
    <Teleport to="body">
        <div ref="panel" class="floating-panel" :style="position" role="dialog" @mousedown.stop>
            <slot />
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';

const props = withDefaults(defineProps<{ anchor: DOMRect; align?: 'start' | 'end'; offset?: number }>(), {
    align: 'start',
    offset: 8,
});

const emit = defineEmits<{ close: [] }>();

const panel = ref<HTMLElement | null>(null);
const position = ref<Record<string, string>>({ top: '0px', left: '0px', visibility: 'hidden' });

const GUTTER = 12;

function updatePosition(): void {
    if (!panel.value) {
        return;
    }

    const { width, height } = panel.value.getBoundingClientRect();
    let left = props.align === 'end' ? props.anchor.right - width : props.anchor.left;
    let top = props.anchor.bottom + props.offset;

    if (top + height > window.innerHeight - GUTTER) {
        top = Math.max(GUTTER, props.anchor.top - height - props.offset);
    }

    left = Math.min(Math.max(GUTTER, left), window.innerWidth - width - GUTTER);

    position.value = { top: `${top}px`, left: `${left}px` };
}

function onDocumentMouseDown(): void {
    emit('close');
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        event.stopPropagation();
        emit('close');
    }
}

onMounted(async () => {
    await nextTick();
    updatePosition();
    window.setTimeout(() => document.addEventListener('mousedown', onDocumentMouseDown));
    document.addEventListener('keydown', onKeydown, true);
    window.addEventListener('resize', updatePosition);
    window.addEventListener('scroll', onDocumentMouseDown, true);
});

onBeforeUnmount(() => {
    document.removeEventListener('mousedown', onDocumentMouseDown);
    document.removeEventListener('keydown', onKeydown, true);
    window.removeEventListener('resize', updatePosition);
    window.removeEventListener('scroll', onDocumentMouseDown, true);
});
</script>

<style lang="scss">
.floating-panel {
    position: fixed;
    z-index: 60;
    border: 1px solid $color-border;
    border-radius: $radius-lg;
    background: $color-surface;
    box-shadow: $shadow-popover;
    animation: floating-panel-in 0.14s ease;
}

@keyframes floating-panel-in {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
}
</style>
