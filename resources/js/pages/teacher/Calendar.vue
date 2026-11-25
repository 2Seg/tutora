<script lang="ts" setup>
import { router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import StatusBadge from '@/components/shared/calendar/StatusBadge.vue';
import WeeklyCalendar from '@/components/shared/calendar/WeeklyCalendar.vue';
import { statusVariants } from '@/lib/calendar-status';
import { toDate } from '@/lib/calendar-utils';
import { show } from '@/routes/teacher/calendar';
import type { Availability, CalendarEvent, Draft } from '@/types/calendar';
import AvailabilitiesPopover from '@/components/shared/calendar/AvailabilitiesPopover.vue';
import { StudentSummary } from '@/types/Student';

const props = defineProps<{
    events: CalendarEvent[];
    availabilities: Availability[];
    students?: StudentSummary[];
    selectedEventId?: number | null;
    weekStart: string;
}>();
const timezone = usePage().props.auth.user.timezone;
const weekStart = ref(toDate(props.weekStart));
const draft = ref<Draft | null>(null);
const selectedEventId = ref<CalendarEvent['id'] | null>(props.selectedEventId ?? null);

watch(weekStart, (week) =>
    router.get(
        show({ query: { week: week.toISOString().slice(0, 10) } }).url,
        {},
        { only: ['events'], preserveState: true, preserveScroll: true },
    ),
);

watch(selectedEventId, (eventId) =>
    router.push({
        url: show({ mergeQuery: { event: eventId } }).url,
        props: (current) => ({ ...current, selectedEventId: eventId }),
        preserveState: true,
        preserveScroll: true,
    }),
);
</script>

<template>
    <div class="grid grid-cols-[minmax(10rem,1fr)_minmax(0,6fr)] content-start gap-6">
        <div class="flex flex-col justify-between h-full bg-card rounded-2xl p-4">
            <div class="flex flex-col gap-2 w-full">
                <AvailabilitiesPopover :availabilities="props.availabilities" />
            </div>

            <div class="flex flex-wrap w-full gap-2">
                <StatusBadge v-for="status in statusVariants" :key="status" :variant="status" />
            </div>
        </div>

        <WeeklyCalendar
            v-model:draft="draft"
            v-model:selected-event-id="selectedEventId"
            v-model:week-start="weekStart"
            :availabilities="props.availabilities"
            :events="props.events"
            :students="props.students"
            :timezone="timezone"
            class="min-w-0"
        />
    </div>
</template>
