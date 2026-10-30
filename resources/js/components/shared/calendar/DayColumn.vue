<script lang="ts" setup>
import { computed, ref } from 'vue';
import Event from '@/components/shared/calendar/Event.vue';
import { useSlotSelection } from '@/composables/calendar/useSlotSelection';
import { useEventResize } from '@/composables/calendar/useEventResize';
import {
    atMinutes,
    isSameDay,
    layoutOverlaps,
    minutesOfDay,
    percentOfDay,
    segmentForDay,
    toDate,
} from '@/lib/calendar-utils';
import type { CalendarEvent, SlotSelection } from '@/types/calendar';

const props = defineProps<{
    day: Date;
    events: CalendarEvent[];
    now: Date;
    timeFormat: Intl.DateTimeFormat;
    draft: SlotSelection | null;
    selectedEventId?: CalendarEvent['id'] | null;
}>();

const emit = defineEmits<{
    'select-slot': [selection: SlotSelection];
    'event-click': [event: CalendarEvent];
    'event-resize': [event: CalendarEvent, endTime: Date];
}>();

const DRAFT_ID = 'draft';
const eventsIncludingDrafts = computed<CalendarEvent[]>(() =>
    props.draft
        ? [
              ...props.events,
              {
                  id: DRAFT_ID,
                  type: 'lesson',
                  status: 'draft',
                  startTime: props.draft.startTime,
                  endTime: props.draft.endTime,
              },
          ]
        : props.events,
);

const column = ref<HTMLElement | null>(null);
const { resizing, onResizeStart } = useEventResize(
    column,
    () => props.day,
    (event, endTime) => emit('event-resize', event, endTime),
);

// Edges that continue into the previous or next day touch midnight, so both halves read as one event
const positionStyle = (startMin: number, endMin: number, col = 0, cols = 1, continuesBefore = false, continuesAfter = false) => ({
    top: `calc(${percentOfDay(startMin)} + ${continuesBefore ? 0 : 1}px)`,
    height: `calc(${percentOfDay(endMin - startMin)} - ${(continuesBefore ? 0 : 1) + (continuesAfter ? 0 : 1)}px)`,
    left: `calc(${(col / cols) * 100}% + 4px)`,
    width: `calc(${100 / cols}% - 8px)`,
});

const isToday = computed(() => isSameDay(props.day, props.now));
const nowStyle = computed(() => ({ top: percentOfDay(minutesOfDay(props.now)) }));
const items = computed(() => {
    const segments = [];

    for (const event of eventsIncludingDrafts.value) {
        const start = toDate(event.startTime);
        const end =
            resizing.value?.id === event.id ? atMinutes(props.day, resizing.value.endMin) : toDate(event.endTime);
        const segment = segmentForDay(start, end, props.day);
        if (segment) segments.push({ event, start, end, ...segment });
    }

    return layoutOverlaps(segments).map((s) => ({
        ...s,
        isPast: s.end.getTime() < props.now.getTime(),
        isCompact: s.endMin - s.startMin < 90,
        style: positionStyle(s.startMin, s.endMin, s.col, s.cols, s.continuesBefore, s.continuesAfter),
    }));
});

const { selection, onPointerDown, onPointerMove, onPointerUp, onPointerCancel } = useSlotSelection(
    () => props.day,
    (slot) => emit('select-slot', slot),
);

function onColumnPointerDown(e: PointerEvent) {
    if (!props.draft) onPointerDown(e);
}

const dragEventPreview = computed(() => {
    if (!selection.value) return null;
    const { startMin, endMin } = selection.value;
    const start = atMinutes(props.day, startMin);
    const end = atMinutes(props.day, endMin);
    const event: CalendarEvent = { id: 'preview', type: 'lesson', status: 'draft', startTime: start, endTime: end };

    return { event, start, end, compact: endMin - startMin < 90, style: positionStyle(startMin, endMin) };
});
</script>

<template>
    <div
        ref="column"
        :class="{ 'bg-secondary/20': isToday, 'cursor-ns-resize': selection }"
        class="relative touch-pan-y border-l border-border select-none"
        data-day
        @pointercancel="onPointerCancel"
        @pointerdown="onColumnPointerDown"
        @pointermove="onPointerMove"
        @pointerup="onPointerUp"
    >
        <div v-for="hour in 24" :key="hour" class="h-10 border-b border-border" />

        <Event
            v-for="item in items"
            :key="item.event.id"
            :continues-after="item.continuesAfter"
            :continues-before="item.continuesBefore"
            :data-draft="item.event.id === DRAFT_ID || undefined"
            :event="item.event"
            :is-compact="item.isCompact"
            :is-past="item.isPast"
            :is-resizable="item.event.id === DRAFT_ID"
            :is-selected="item.event.id === selectedEventId"
            :range="timeFormat.formatRange(item.start, item.end)"
            :style="item.style"
            class="absolute"
            @click="item.event.id !== DRAFT_ID && emit('event-click', item.event)"
            @resize-start="onResizeStart(item.event, $event)"
        />

        <Event
            v-if="dragEventPreview"
            :event="dragEventPreview.event"
            :is-compact="dragEventPreview.compact"
            :is-past="false"
            :range="timeFormat.formatRange(dragEventPreview.start, dragEventPreview.end)"
            :style="dragEventPreview.style"
            class="pointer-events-none absolute z-10"
        />

        <!-- Current time pointer -->
        <div
            v-if="isToday"
            :style="nowStyle"
            class="pointer-events-none absolute inset-x-0 z-20 -mt-px h-0.5 bg-destructive before:absolute before:-top-1 before:-left-1.25 before:size-2.5 before:rounded-full before:bg-destructive before:content-['']"
        />
    </div>
</template>
