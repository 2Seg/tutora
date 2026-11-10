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
import type { Availability, CalendarEvent, Draft, DraftUpdate } from '@/types/calendar';
import { Pagination, PaginationContent, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { useDraftMove } from '@/composables/calendar/useDraftMove';
import { StudentSummary } from '@/types/Student';

const props = withDefaults(
    defineProps<{
        events?: CalendarEvent[];
        availabilities?: Availability[];
        students?: StudentSummary[];
        timezone: string;
    }>(),
    { events: () => [], availabilities: () => [] },
);

const emit = defineEmits<{ 'event-click': [event: CalendarEvent] }>();

const startOfCurrentWeek = () => startOfWeek(toWallClock(new Date(), props.timezone));

const draft = defineModel<Draft | null>('draft', { default: null });
const weekStart = defineModel<Date>('weekStart', {
    default: (rawProps: Record<string, unknown>) => startOfWeek(toWallClock(new Date(), rawProps.timeZone as string)),
});

const zonedEvents = computed<CalendarEvent[]>(() =>
    props.events.map((event) => ({
        ...event,
        startsAt: toWallClock(toDate(event.startsAt), props.timezone),
        endsAt: toWallClock(toDate(event.endsAt), props.timezone),
    })),
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

function resizeDraft(_event: CalendarEvent, endsAt: Date) {
    if (draft.value) draft.value = { ...draft.value, endsAt };
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
    const timeZone = CALENDAR_TIME_ZONE;
    return {
        time: new Intl.DateTimeFormat(locale, { hour: '2-digit', minute: '2-digit', hourCycle: 'h23', timeZone }),
        weekday: new Intl.DateTimeFormat(locale, { weekday: 'short', timeZone }),
        range: new Intl.DateTimeFormat(locale, { day: 'numeric', month: 'long', year: 'numeric', timeZone }),
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

const { isMoving: isMovingDraft, onPointerDown: onDraftPointerDown } = useDraftMove({
    body,
    days: () => days.value,
    draft,
});

onMounted(() => {
    if (scroller.value && body.value) scroller.value.scrollTop = (body.value.offsetHeight / 24) * 7;
});
</script>

<template>
    <section
        :class="{ '**:data-draft:z-10 **:data-draft:scale-105 **:data-draft:shadow-lg': isMovingDraft }"
        class="text-foreground overflow-x-auto **:data-draft:transition-transform"
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

                <div ref="body" :class="gridColumns" @pointerdown="onDraftPointerDown">
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
                        :now="zonedNow"
                        :selected-event-id="selectedEventId"
                        :time-format="formats.time"
                        @event-click="selectEvent"
                        @draft-resizing="draftResizeEnd = $event"
                        @event-resize="resizeDraft"
                        @select-slot="draft = { ...$event, type: 'lesson' }"
                    />
                </div>
            </div>
        </div>

        <EventPopover
            :container="body"
            :draft="shownDraft"
            :event="selectedEvent"
            :is-moving="isMovingDraft"
            :students="props.students"
            :time-zone="props.timezone"
            @close="closePopover"
            @update-draft="updateDraft"
        />

        <!-- Carries the cursor while the draft is being moved -->
        <div v-if="isMovingDraft" class="fixed inset-0 z-50 cursor-grabbing" />
    </section>
</template>
