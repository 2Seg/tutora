<script lang="ts" setup>
import { computed } from 'vue';
import Event from '@/components/shared/calendar/Event.vue';
import { useSlotSelection } from '@/composables/useSlotSelection';
import { isSameDay, layoutOverlaps, minutesOfDay, percentOfDay, segmentForDay, toDate } from '@/lib/calendar-utils';
import type { CalendarEvent, SlotSelection } from '@/types/calendar';

const props = defineProps<{
    day: Date;
    events: CalendarEvent[];
    now: Date;
    timeFormat: Intl.DateTimeFormat;
}>();

const emit = defineEmits<{
    'select-slot': [selection: SlotSelection];
    'event-click': [event: CalendarEvent];
}>();

const isToday = computed(() => isSameDay(props.day, props.now));
const nowStyle = computed(() => ({ top: percentOfDay(minutesOfDay(props.now)) }));

const items = computed(() => {
    const segments = [];

    for (const event of props.events) {
        const start = toDate(event.startTime);
        const end = toDate(event.endTime);
        const segment = segmentForDay(start, end, props.day);
        if (segment) segments.push({ event, start, end, ...segment });
    }

    return layoutOverlaps(segments).map((s) => ({
        ...s,
        isPast: s.end.getTime() < props.now.getTime(),
        isCompact: s.endMin - s.startMin < 90,
        style: {
            top: `calc(${percentOfDay(s.startMin)} + 1px)`,
            height: `calc(${percentOfDay(s.endMin - s.startMin)} - 2px)`,
            left: `calc(${(s.col / s.cols) * 100}% + 4px)`,
            width: `calc(${100 / s.cols}% - 8px)`,
        },
    }));
});

const { selectionStyle, onPointerDown, onPointerMove, onPointerUp, onPointerCancel } = useSlotSelection(
    () => props.day,
    (selection) => emit('select-slot', selection),
);
</script>

<template>
    <div
        :class="{ 'bg-primary/5': isToday }"
        class="relative touch-pan-y border-l border-border select-none"
        @pointercancel="onPointerCancel"
        @pointerdown="onPointerDown"
        @pointermove="onPointerMove"
        @pointerup="onPointerUp"
    >
        <div v-for="hour in 24" :key="hour" class="h-10 border-b border-border" />

        <Event
            v-for="item in items"
            :key="item.event.id"
            :event="item.event"
            :is-compact="item.isCompact"
            :is-past="item.isPast"
            :range="timeFormat.formatRange(item.start, item.end)"
            :style="item.style"
            class="absolute"
            @click="emit('event-click', item.event)"
        />

        <div
            v-if="selectionStyle"
            :style="selectionStyle"
            class="pointer-events-none absolute inset-x-1 z-10 rounded-lg border border-primary bg-primary/15"
        />

        <!-- Current time pointer -->
        <div
            v-if="isToday"
            :style="nowStyle"
            class="pointer-events-none absolute inset-x-0 z-20 -mt-px h-0.5 bg-destructive before:absolute before:-top-1 before:-left-1.25 before:size-2.5 before:rounded-full before:bg-destructive before:content-['']"
        />
    </div>
</template>
