<script lang="ts" setup>
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import StatusBadge from '@/components/shared/calendar/StatusBadge.vue';
import WeeklyCalendar from '@/components/shared/calendar/WeeklyCalendar.vue';
import { statusVariants } from '@/lib/calendar-status';
import { startOfWeek, toDate, toWallClock } from '@/lib/calendar-utils';
import type { Availability, CalendarEvent, Draft } from '@/types/calendar';
import AvailabilitiesPopover from '@/components/shared/calendar/AvailabilitiesPopover.vue';
import { StudentSummary } from '@/types/Student';

const props = defineProps<{
    events: CalendarEvent[];
    availabilities: Availability[];
    students?: StudentSummary[];
    selectedEventId?: number | null;
}>();
const timezone = usePage().props.auth.user.timezone;
const initialEvent = props.events.find((event) => event.id === props.selectedEventId);
const weekStart = ref(startOfWeek(toWallClock(initialEvent ? toDate(initialEvent.startsAt) : new Date(), timezone)));
const draft = ref<Draft | null>(null);
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
            v-model:week-start="weekStart"
            :availabilities="props.availabilities"
            :events="props.events"
            :initial-selected-event-id="initialEvent?.id ?? null"
            :students="props.students"
            :timezone="timezone"
            class="min-w-0"
        />
    </div>
</template>
