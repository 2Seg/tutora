<script lang="ts" setup>
import { Lock } from '@lucide/vue';
import { computed, type Component } from 'vue';

import type { BlockEvent, CalendarEvent, LessonStatus } from '@/types/calendar';

const props = defineProps<{
    event: CalendarEvent;
    range: string;
    isPast: boolean;
    isCompact: boolean;
    isResizable?: boolean;
    isSelected?: boolean;
}>();

const lessonClasses: Record<LessonStatus, string> = {
    planned: 'badge-secondary',
    cancelled: 'badge-destructive',
    requested: 'badge-warning',
    draft: 'badge-outline',
};
const blockClasses: Record<BlockEvent['type'], string> = {
    unavailability: 'badge-hashed',
};
const icons: Partial<Record<CalendarEvent['type'], Component>> = { unavailability: Lock };
const defaultTitles: Record<CalendarEvent['type'], string> = {
    lesson: '(No title)',
    unavailability: 'Unavailable',
};
const statusLabels: Record<LessonStatus, string> = {
    draft: 'Draft',
    requested: 'Requested',
    planned: 'Planned',
    cancelled: 'Cancelled',
};

const status = computed(() => (props.event.type === 'lesson' ? props.event.status : null));
const classes = computed(() =>
    status.value ? lessonClasses[status.value] : blockClasses[props.event.type as BlockEvent['type']],
);
const icon = computed(() => icons[props.event.type]);
const title = computed(() => props.event.title ?? defaultTitles[props.event.type]);
const description = computed(() =>
    [title.value, status.value && statusLabels[status.value], props.range].filter(Boolean).join(', '),
);

const emit = defineEmits<{ 'resize-start': [e: PointerEvent] }>();
</script>

<template>
    <button
        :aria-label="description"
        :class="[
            classes,
            { 'opacity-60': isPast },
            isCompact ? 'flex-row items-center gap-2' : 'flex-col items-start justify-start py-1.5',
            isResizable ? 'cursor-grab' : 'cursor-pointer',
            isSelected && 'z-10 scale-105 shadow-lg',
        ]"
        :data-selected="isSelected || undefined"
        :title="description"
        class="flex min-h-4.5 min-w-0 overflow-hidden transition-transform rounded-lg border px-2.5 text-left text-xs leading-snug focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
        data-event
        type="button"
    >
        <span class="flex min-w-0 items-center gap-1.5 font-semibold">
            <component :is="icon" v-if="icon" aria-hidden="true" class="size-3.5 shrink-0" />
            <span class="truncate">{{ title }}</span>
        </span>
        <span class="min-w-0 truncate text-[11px]">{{ range }}</span>
        <span v-if="status && !isCompact" class="min-w-0 truncate text-[11px]">{{ statusLabels[status] }}</span>
        <span
            v-if="isResizable"
            aria-hidden="true"
            class="absolute inset-x-0 bottom-0 h-2 cursor-ns-resize touch-none"
            @click.stop
            @pointerdown.stop.prevent="emit('resize-start', $event)"
        />
    </button>
</template>
