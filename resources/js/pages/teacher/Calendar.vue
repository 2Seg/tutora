<script lang="ts" setup>
import { ref } from 'vue';
import StatusBadge from '@/components/shared/calendar/StatusBadge.vue';
import WeeklyCalendar from '@/components/shared/calendar/WeeklyCalendar.vue';
import { statusVariants } from '@/lib/calendar-status';
import { startOfWeek } from '@/lib/calendar-utils';
import type { CalendarEvent, SlotSelection } from '@/types/calendar';
import { Button } from '@/components/ui/button';

const props = defineProps<{ events: CalendarEvent[] }>();
const weekStart = ref(startOfWeek(new Date()));
const draft = ref<SlotSelection | null>(null);

function onEventClick(event: CalendarEvent) {
    console.log('event', event);
}
</script>

<template>
    <div class="grid grid-cols-[minmax(7.5rem,1fr)_minmax(0,5fr)] content-start gap-6">
        <div class="flex flex-col justify-between h-full bg-card rounded-2xl p-4">
            <div class="flex flex-col gap-2 w-full">
                <Button variant="secondary">Schedule lesson</Button>
                <Button size="sm" variant="outline">Add time off</Button>
            </div>

            <div class="flex flex-wrap w-full gap-2">
                <StatusBadge v-for="status in statusVariants" :key="status" :variant="status" />
            </div>
        </div>

        <WeeklyCalendar
            v-model:draft="draft"
            v-model:week-start="weekStart"
            :events="props.events"
            class="min-w-0"
            @event-click="onEventClick"
        />
    </div>
</template>
