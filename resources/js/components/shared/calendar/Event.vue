<script lang="ts" setup>
import { computed } from 'vue';
import { getEventAppearance, getEventDescription } from '@/lib/calendar-event';
import type { CalendarEvent } from '@/types/calendar';

const props = defineProps<{
    event: CalendarEvent;
    range: string;
    isPast: boolean;
    isCompact: boolean;
    isResizable?: boolean;
    isSelected?: boolean;
}>();

const appearance = computed(() => getEventAppearance(props.event));
const description = computed(() => getEventDescription(props.event, props.range));

const emit = defineEmits<{ 'resize-start': [e: PointerEvent] }>();
</script>

<template>
    <button
        :aria-label="description"
        :class="[
            appearance.className,
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
            <component :is="appearance.icon" v-if="appearance.icon" aria-hidden="true" class="size-3.5 shrink-0" />
            <span class="truncate">{{ appearance.title }}</span>
        </span>
        <span class="min-w-0 truncate text-[11px]">{{ range }}</span>
        <span v-if="appearance.statusLabel && !isCompact" class="min-w-0 truncate text-[11px]">
            {{ appearance.statusLabel }}
        </span>
        <span
            v-if="isResizable"
            aria-hidden="true"
            class="absolute inset-x-0 bottom-0 h-2 cursor-ns-resize touch-none"
            @click.stop
            @pointerdown.stop.prevent="emit('resize-start', $event)"
        />
    </button>
</template>
