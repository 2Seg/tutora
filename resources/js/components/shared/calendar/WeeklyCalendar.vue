<script lang="ts" setup>
import { getActiveLanguage } from 'laravel-vue-i18n';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import DayColumn from '@/components/shared/calendar/DayColumn.vue';
import EventPopover from '@/components/shared/calendar/EventPopover.vue';
import { Button } from '@/components/ui/button';
import { addDays, describeTimezone, isSameDay, startOfWeek } from '@/lib/calendar-utils';
import type { CalendarEvent, SlotSelection } from '@/types/calendar';
import { Pagination, PaginationContent, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { useDraftMove } from '@/composables/calendar/useDraftMove';

const props = withDefaults(defineProps<{ events?: CalendarEvent[] }>(), { events: () => [] });

const emit = defineEmits<{
    'event-click': [event: CalendarEvent];
}>();

const draft = defineModel<SlotSelection | null>('draft', { default: null });

function resizeDraft(_event: CalendarEvent, endTime: Date) {
    if (draft.value) draft.value = { ...draft.value, endTime };
}

function discardDraftOnPointerDown(e: PointerEvent) {
    if (draft.value && !(e.target as HTMLElement).closest('[data-draft], [data-keep-draft]')) draft.value = null;
}
function discardDraftOnEscapeKey(e: KeyboardEvent) {
    if (e.key === 'Escape' && draft.value) draft.value = null;
}

const selectedEventId = ref<CalendarEvent['id'] | null>(null);

const selectedEvent = computed(() => props.events.find((event) => event.id === selectedEventId.value) ?? null);

function selectEvent(event: CalendarEvent) {
    selectedEventId.value = event.id;
    emit('event-click', event);
}

function closePopover() {
    draft.value = null;
    selectedEventId.value = null;
}

function clearSelectionOnPointerDown(e: PointerEvent) {
    if (!(e.target as HTMLElement).closest('[data-event], [data-keep-draft]')) selectedEventId.value = null;
}
function clearSelectionOnEscapeKey(e: KeyboardEvent) {
    if (e.key === 'Escape') selectedEventId.value = null;
}

const weekStart = defineModel<Date>('weekStart', { default: () => startOfWeek(new Date()) });

watch(weekStart, closePopover);

const gridColumns = 'grid min-w-[760px] grid-cols-[64px_repeat(7,minmax(96px,1fr))]';

const locale = computed(() => getActiveLanguage().replace('_', '-'));

const days = computed(() => Array.from({ length: 7 }, (_, i) => addDays(weekStart.value, i)));
const hours = Array.from({ length: 24 }, (_, h) => h);

const timeFormat = computed(
    () => new Intl.DateTimeFormat(locale.value, { hour: '2-digit', minute: '2-digit', hourCycle: 'h23' }),
);
const weekdayFormat = computed(() => new Intl.DateTimeFormat(locale.value, { weekday: 'short' }));
const rangeFormat = computed(
    () => new Intl.DateTimeFormat(locale.value, { day: 'numeric', month: 'long', year: 'numeric' }),
);

const hourLabel = (hour: number) => timeFormat.value.format(new Date(2000, 0, 1, hour));
const rangeTitle = computed(() => rangeFormat.value.formatRange(days.value[0], days.value[6]));
const timezoneLabel = computed(() => describeTimezone(weekStart.value));

function shiftWeek(direction: number) {
    weekStart.value = addDays(weekStart.value, direction * 7);
}
function goToThisWeek() {
    weekStart.value = startOfWeek(new Date());
}

const scroller = ref<HTMLElement | null>(null);
const body = ref<HTMLElement | null>(null);
const now = ref(new Date());
let timer: number | undefined;

const { isMoving: isMovingDraft, onPointerDown: onDraftPointerDown } = useDraftMove({
    body,
    days: () => days.value,
    draft,
});

onMounted(() => {
    if (scroller.value && body.value) scroller.value.scrollTop = (body.value.offsetHeight / 24) * 7;
    timer = window.setInterval(() => (now.value = new Date()), 60_000);

    window.addEventListener('pointerdown', discardDraftOnPointerDown);
    window.addEventListener('keydown', discardDraftOnEscapeKey);
    window.addEventListener('pointerdown', clearSelectionOnPointerDown);
    window.addEventListener('keydown', clearSelectionOnEscapeKey);
});
onBeforeUnmount(() => {
    window.clearInterval(timer);
    window.removeEventListener('pointerdown', discardDraftOnPointerDown);
    window.removeEventListener('keydown', discardDraftOnEscapeKey);
    window.removeEventListener('pointerdown', clearSelectionOnPointerDown);
    window.removeEventListener('keydown', clearSelectionOnEscapeKey);
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
                        :class="{ 'text-primary': isSameDay(day, now) }"
                        class="flex items-center justify-center text-sm font-semibold"
                    >
                        {{ weekdayFormat.format(day) }} {{ day.getDate() }}
                    </div>
                </div>

                <div ref="body" :class="gridColumns" @pointerdown="onDraftPointerDown">
                    <div class="sticky left-0 z-20 bg-card">
                        <div v-for="hour in hours" :key="hour" class="relative h-10">
                            <span
                                v-if="hour > 0"
                                class="absolute top-0 right-2.5 -translate-y-1/2 text-xs text-muted-foreground tabular-nums"
                            >
                                {{ hourLabel(hour) }}
                            </span>
                        </div>
                    </div>

                    <DayColumn
                        v-for="day in days"
                        :key="day.getTime()"
                        :day="day"
                        :draft="draft"
                        :events="events"
                        :now="now"
                        :selected-event-id="selectedEventId"
                        :time-format="timeFormat"
                        @event-click="selectEvent"
                        @event-resize="resizeDraft"
                        @select-slot="draft = $event"
                    />
                </div>
            </div>
        </div>

        <EventPopover
            :container="body"
            :draft="draft"
            :event="selectedEvent"
            :is-moving="isMovingDraft"
            @close="closePopover"
        />

        <!-- Carries the cursor while the draft is being moved -->
        <div v-if="isMovingDraft" class="fixed inset-0 z-50 cursor-grabbing" />
    </section>
</template>
