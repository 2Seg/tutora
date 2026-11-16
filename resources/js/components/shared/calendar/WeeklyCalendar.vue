<script lang="ts" setup>
import { useEventListener, useIntervalFn, useNow } from '@vueuse/core';
import { getActiveLanguage } from 'laravel-vue-i18n';
import { computed, onMounted, ref, watch } from 'vue';
import DayColumn from '@/components/shared/calendar/DayColumn.vue';
import EventPopover from '@/components/shared/calendar/EventPopover.vue';
import { Button } from '@/components/ui/button';
import {
    addDays,
    atMinutes,
    CALENDAR_TIME_ZONE,
    describeTimezone,
    fitLesson,
    fromWallClock,
    isSameDay,
    startOfWeek,
    toDate,
    toWallClock,
} from '@/lib/calendar-utils';
import type { Availability, CalendarEvent, Draft, DraftUpdate, SlotSelection, TimeOff } from '@/types/calendar';
import { Pagination, PaginationContent, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { useEventMove } from '@/composables/calendar/useEventMove';
import { StudentSummary } from '@/types/Student';
import { router } from '@inertiajs/vue3';
import { update } from '@/routes/teacher/calendar/events';
import { DRAFT_ID } from '@/lib/calendar-event';

type PendingTimeOff = TimeOff & SlotSelection;

const props = withDefaults(
    defineProps<{
        events?: CalendarEvent[];
        availabilities?: Availability[];
        students?: StudentSummary[];
        timezone: string;
        initialSelectedEventId?: CalendarEvent['id'] | null;
    }>(),
    { events: () => [], availabilities: () => [] },
);

const emit = defineEmits<{ 'event-click': [event: CalendarEvent] }>();

const startOfCurrentWeek = () => startOfWeek(toWallClock(new Date(), props.timezone));

const draft = defineModel<Draft | null>('draft', { default: null });
const weekStart = defineModel<Date>('weekStart', {
    default: (rawProps: Record<string, unknown>) => startOfWeek(toWallClock(new Date(), rawProps.timezone as string)),
});

const pendingTimeOff = ref<PendingTimeOff | null>(null);

const zonedEvents = computed<CalendarEvent[]>(() =>
    props.events.map((event) =>
        event.id === pendingTimeOff.value?.id
            ? pendingTimeOff.value
            : {
                  ...event,
                  startsAt: toWallClock(toDate(event.startsAt), props.timezone),
                  endsAt: toWallClock(toDate(event.endsAt), props.timezone),
              },
    ),
);

// Week navigation
const days = computed(() => Array.from({ length: 7 }, (_, i) => addDays(weekStart.value, i)));

function shiftWeek(direction: number) {
    weekStart.value = addDays(weekStart.value, direction * 7);
}
function goToThisWeek() {
    weekStart.value = startOfCurrentWeek();
}

watch(weekStart, closePopover);

// Draft and selection
const selectedEventId = ref<CalendarEvent['id'] | null>(null);
const selectedEvent = computed(() => zonedEvents.value.find((event) => event.id === selectedEventId.value) ?? null);

function selectEvent(event: CalendarEvent) {
    selectedEventId.value = event.id;
    emit('event-click', event);
}

function previewTimeOff(timeOff: TimeOff | null) {
    pendingTimeOff.value = timeOff && { ...timeOff, startsAt: toDate(timeOff.startsAt), endsAt: toDate(timeOff.endsAt) };
}

function resizeEvent(event: CalendarEvent, endsAt: Date) {
    if (event.id === DRAFT_ID) {
        if (draft.value) draft.value = { ...draft.value, endsAt };
        return;
    }
    if (event.type !== 'time_off') return;

    const resized = { ...event, startsAt: toDate(event.startsAt), endsAt };
    pendingTimeOff.value = resized;
    savePendingTimeOff(resized);
}

function updateDraft(update: DraftUpdate) {
    if (!draft.value) return;
    const updated = { ...draft.value, ...update };
    draft.value = update.type === 'lesson' && draft.value.type !== 'lesson' ? fitLesson(updated) : updated;
}

const draftResizeEnd = ref<Date | null>(null);
const shownDraft = computed(() =>
    draft.value && draftResizeEnd.value ? { ...draft.value, endsAt: draftResizeEnd.value } : draft.value,
);

function closePopover() {
    draft.value = null;
    selectedEventId.value = null;
}

useEventListener(window, 'pointerdown', (e) => {
    if (document.body.style.pointerEvents === 'none') return;
    const target = e.target as HTMLElement;
    if (!target.closest('[data-draft], [data-keep-draft]')) draft.value = null;
    if (!target.closest('[data-event], [data-keep-draft]')) selectedEventId.value = null;
});
useEventListener(window, 'keydown', (e) => {
    if (e.key === 'Escape') closePopover();
});

// Formatting
const formats = computed(() => {
    const locale = getActiveLanguage().replace('_', '-');
    const timezone = CALENDAR_TIME_ZONE;
    return {
        time: new Intl.DateTimeFormat(locale, { hour: '2-digit', minute: '2-digit', hourCycle: 'h23', timeZone: timezone }),
        weekday: new Intl.DateTimeFormat(locale, { weekday: 'short', timeZone: timezone }),
        range: new Intl.DateTimeFormat(locale, { day: 'numeric', month: 'long', year: 'numeric', timeZone: timezone }),
    };
});

const hourLabels = computed(() =>
    Array.from({ length: 24 }, (_, hour) => formats.value.time.format(atMinutes(weekStart.value, hour * 60))),
);
const rangeTitle = computed(() => formats.value.range.formatRange(days.value[0], days.value[6]));
const timezoneLabel = computed(() => describeTimezone(fromWallClock(weekStart.value, props.timezone), props.timezone));

// Grid
const gridColumns = 'grid min-w-[760px] grid-cols-[64px_repeat(7,minmax(96px,1fr))]';
const scroller = ref<HTMLElement | null>(null);
const body = ref<HTMLElement | null>(null);
const now = useNow({ scheduler: (tick) => useIntervalFn(tick, 60_000) });
const zonedNow = computed(() => toWallClock(now.value, props.timezone));

const draftMove = useEventMove({
    body,
    days: () => days.value,
    grab: (target) => (target.closest('[data-draft]') ? draft.value : null),
    onMove: (moved) => (draft.value = moved),
    isStillHeld: () => draft.value !== null,
});

function grabTimeOff(target: HTMLElement): PendingTimeOff | null {
    const eventId = target.closest<HTMLElement>('[data-movable-time-off]')?.dataset.eventId;
    const timeOff = zonedEvents.value.find((event) => event.type === 'time_off' && String(event.id) === eventId);
    if (!timeOff || timeOff.type !== 'time_off') return null;

    return { ...timeOff, startsAt: toDate(timeOff.startsAt), endsAt: toDate(timeOff.endsAt) };
}

function savePendingTimeOff(timeOff: PendingTimeOff) {
    router.patch(
        update(Number(timeOff.id)).url,
        {
            starts_at: fromWallClock(timeOff.startsAt, props.timezone).toISOString(),
            ends_at: fromWallClock(timeOff.endsAt, props.timezone).toISOString(),
        },
        { preserveScroll: true, preserveState: true, onFinish: () => (pendingTimeOff.value = null) },
    );
}

const timeOffMove = useEventMove({
    body,
    days: () => days.value,
    grab: grabTimeOff,
    onMove: (moved) => (pendingTimeOff.value = moved),
    onRelease: savePendingTimeOff,
    onCancel: () => (pendingTimeOff.value = null),
});

const isMoving = computed(() => draftMove.isMoving.value || timeOffMove.isMoving.value);
const movingEventId = computed(() => {
    if (draftMove.isMoving.value) return DRAFT_ID;
    if (timeOffMove.isMoving.value) return pendingTimeOff.value?.id ?? null;
    return null;
});

function onBodyPointerDown(e: PointerEvent) {
    draftMove.onPointerDown(e);
    timeOffMove.onPointerDown(e);
}

onMounted(() => {
    if (scroller.value && body.value) scroller.value.scrollTop = (body.value.offsetHeight / 24) * 7;
    if (props.initialSelectedEventId != null) selectedEventId.value = props.initialSelectedEventId;
});
</script>

<template>
    <section
        class="text-foreground overflow-x-auto **:data-moving:z-10 **:data-moving:scale-105 **:data-moving:shadow-lg"
    >
        <div class="min-w-175">
            <header class="mb-4 flex items-center gap-x-4 whitespace-nowrap">
                <div class="flex items-center gap-1">
                    <Button size="sm" variant="secondary" @click="goToThisWeek">This week</Button>

                    <Pagination :items-per-page="1" :page="2" :total="3" @update:page="shiftWeek($event - 2)">
                        <PaginationContent>
                            <PaginationPrevious aria-label="Previous week" />
                            <PaginationNext aria-label="Next week" />
                        </PaginationContent>
                    </Pagination>
                </div>
                <h2 class="m-0 font-serif text-[26px] font-normal">{{ rangeTitle }}</h2>
                <p class="m-0 ml-auto text-[13px] text-muted-foreground">{{ timezoneLabel }}</p>
            </header>

            <div
                ref="scroller"
                class="relative h-120 max-h-[75dvh] overflow-y-auto overscroll-contain rounded-2xl bg-card"
            >
                <div :class="gridColumns" class="sticky top-0 z-30 h-10 border-b border-border bg-card">
                    <div class="sticky left-0 z-20 bg-card" />
                    <div
                        v-for="day in days"
                        :key="day.getTime()"
                        class="flex items-center justify-center text-sm font-semibold"
                    >
                        <span
                            :class="{ 'bg-secondary text-secondary-foreground': isSameDay(day, zonedNow) }"
                            class="inline-flex h-8 items-center rounded-md px-3"
                        >
                            {{ formats.weekday.format(day) }} {{ day.getUTCDate() }}
                        </span>
                    </div>
                </div>

                <div ref="body" :class="gridColumns" @pointerdown="onBodyPointerDown">
                    <div class="sticky left-0 z-20 bg-card">
                        <div v-for="(label, hour) in hourLabels" :key="hour" class="relative h-10">
                            <span
                                v-if="hour > 0"
                                class="absolute top-0 right-2.5 -translate-y-1/2 text-xs text-muted-foreground tabular-nums"
                            >
                                {{ label }}
                            </span>
                        </div>
                    </div>

                    <DayColumn
                        v-for="day in days"
                        :key="day.getTime()"
                        :availabilities="availabilities"
                        :day="day"
                        :draft="draft"
                        :events="zonedEvents"
                        :moving-event-id="movingEventId"
                        :now="zonedNow"
                        :selected-event-id="selectedEventId"
                        :time-format="formats.time"
                        @event-click="selectEvent"
                        @draft-resizing="draftResizeEnd = $event"
                        @event-resize="resizeEvent"
                        @select-slot="draft = { ...$event, type: 'lesson' }"
                    />
                </div>
            </div>
        </div>

        <EventPopover
            :container="body"
            :draft="shownDraft"
            :event="selectedEvent"
            :is-moving="isMoving"
            :students="props.students"
            :timezone="props.timezone"
            @close="closePopover"
            @update-draft="updateDraft"
            @preview-time-off="previewTimeOff"
        />

        <!-- Carries the cursor while the draft is being moved -->
        <div v-if="isMoving" class="fixed inset-0 z-50 cursor-grabbing" />
    </section>
</template>
