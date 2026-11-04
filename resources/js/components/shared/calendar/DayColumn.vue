<script lang="ts" setup>
import { trans } from 'laravel-vue-i18n';
import { computed, ref, watch } from 'vue';
import Event from '@/components/shared/calendar/Event.vue';
import { useSlotSelection } from '@/composables/calendar/useSlotSelection';
import { useEventResize } from '@/composables/calendar/useEventResize';
import {
    atMinutes,
    availabilitySideLinesForDay,
    isSameDay,
    layoutOverlaps,
    MINUTES_PER_DAY,
    minutesOfDay,
    percentOfDay,
    segmentForDay,
    toDate,
    unavailableSlotsForDay,
} from '@/lib/calendar-utils';
import type { Availability, CalendarEvent, Draft, SlotSelection } from '@/types/calendar';

const props = defineProps<{
    day: Date;
    events: CalendarEvent[];
    availabilities: Availability[];
    now: Date;
    timeFormat: Intl.DateTimeFormat;
    draft: Draft | null;
    selectedEventId?: CalendarEvent['id'] | null;
}>();

const emit = defineEmits<{
    'select-slot': [selection: SlotSelection];
    'event-click': [event: CalendarEvent];
    'event-resize': [event: CalendarEvent, endsAt: Date];
    'draft-resizing': [endsAt: Date | null];
}>();

const DRAFT_ID = 'draft';
const draftEvent = computed<CalendarEvent | null>(() => {
    if (!props.draft) return null;
    const { type, title, startsAt, endsAt } = props.draft;
    return type === 'time_off'
        ? { id: DRAFT_ID, type, startsAt, endsAt }
        : { id: DRAFT_ID, type, status: 'draft', title, startsAt, endsAt };
});
const eventsIncludingDrafts = computed<CalendarEvent[]>(() =>
    draftEvent.value ? [...props.events, draftEvent.value] : props.events,
);

const column = ref<HTMLElement | null>(null);
const { resizing, onResizeStart } = useEventResize(
    column,
    () => props.day,
    (event, endsAt) => emit('event-resize', event, endsAt),
);

watch(
    () => resizing.value?.endMin,
    (endMin) => emit('draft-resizing', endMin === undefined ? null : atMinutes(props.day, endMin)),
);

const RIGHT_GUTTER = 12;

const positionStyle = (
    startMin: number,
    endMin: number,
    col = 0,
    cols = 1,
    continuesBefore = false,
    continuesAfter = false,
) => ({
    top: `calc(${percentOfDay(startMin)} + ${continuesBefore ? 0 : 1}px)`,
    height: `calc(${percentOfDay(endMin - startMin)} - ${(continuesBefore ? 0 : 1) + (continuesAfter ? 0 : 1)}px)`,
    left: `calc(${col} * (100% - ${RIGHT_GUTTER}px) / ${cols} + 4px)`,
    width: `calc((100% - ${RIGHT_GUTTER}px) / ${cols} - 8px)`,
});

const onGridLineStyle = (startMin: number, endMin: number) => {
    const offset = startMin > 0 ? 1 : 0;

    return {
        top: `calc(${percentOfDay(startMin)} - ${offset}px)`,
        height: `calc(${percentOfDay(endMin - startMin)} + ${offset}px)`,
    };
};

const isToday = computed(() => isSameDay(props.day, props.now));
const unavailableSlots = computed(() =>
    unavailableSlotsForDay(props.availabilities, props.day).map(({ startMin, endMin }) => ({
        startMin,
        edges: { 'border-t': startMin > 0, 'border-b': endMin < MINUTES_PER_DAY },
        style: onGridLineStyle(startMin, endMin),
    })),
);
const availabilitySideLines = computed(() => {
    const { left, right } = availabilitySideLinesForDay(props.availabilities, props.day);
    // The left line sits on this column's border and the right one on the next column's. Sunday has no next column.
    const rightSide = props.day.getUTCDay() === 0 ? 'right-0' : '-right-px';
    const toLine =
        (side: string) =>
        ({ startMin, endMin }: { startMin: number; endMin: number }) => ({
            key: `${side}-${startMin}`,
            side,
            style: onGridLineStyle(startMin, endMin),
        });

    return [...left.map(toLine('-left-px')), ...right.map(toLine(rightSide))];
});
const nowStyle = computed(() => ({ top: percentOfDay(minutesOfDay(props.now)) }));
const items = computed(() => {
    const segments = [];

    for (const event of eventsIncludingDrafts.value) {
        const start = toDate(event.startsAt);
        const end =
            resizing.value?.id === event.id ? atMinutes(props.day, resizing.value.endMin) : toDate(event.endsAt);
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
    const event: CalendarEvent = { id: 'preview', type: 'lesson', status: 'draft', startsAt: start, endsAt: end };

    return { event, start, end, compact: endMin - startMin < 90, style: positionStyle(startMin, endMin) };
});
</script>

<template>
    <div
        ref="column"
        :class="{ 'bg-secondary/20': isToday, 'cursor-ns-resize': selection }"
        :title="trans('calendar.availability.available')"
        class="relative touch-pan-y border-l border-border select-none"
        data-day
        @pointercancel="onPointerCancel"
        @pointerdown="onColumnPointerDown"
        @pointermove="onPointerMove"
        @pointerup="onPointerUp"
    >
        <div v-for="hour in 24" :key="hour" class="h-10 border-b border-border" />

        <div
            v-for="slot in unavailableSlots"
            :key="slot.startMin"
            :class="slot.edges"
            :style="slot.style"
            :title="trans('calendar.availability.unavailable')"
            class="bg-foreground/2 absolute -left-px right-0 border-foreground/15"
        />

        <div
            v-for="line in availabilitySideLines"
            :key="line.key"
            :class="line.side"
            :style="line.style"
            class="pointer-events-none absolute z-1 w-px bg-foreground/15"
        />

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
