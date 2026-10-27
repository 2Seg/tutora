<script lang="ts" setup>
import { ref } from 'vue';
import WeeklyCalendar from '@/components/shared/calendar/WeeklyCalendar.vue';
import { buildFakeEvents } from '@/hardcoded-data/calendar-events';
import { startOfWeek } from '@/lib/calendar-utils';
import type { CalendarEvent, SlotSelection } from '@/types/calendar';
import { Badge } from '@/components/ui/badge';
import { Lock } from '@lucide/vue';
import { Button } from '@/components/ui/button';

const weekStart = ref(startOfWeek(new Date()));
const events = buildFakeEvents();
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
                <Badge variant="secondary">Planned</Badge>
                <Badge variant="destructive">Cancelled</Badge>
                <Badge variant="warning">Requested</Badge>
                <Badge variant="outline">Draft</Badge>
                <Badge variant="hashed"><Lock />Unavailable</Badge>
            </div>
        </div>

        <WeeklyCalendar
            v-model:draft="draft"
            v-model:week-start="weekStart"
            :events="events"
            class="min-w-0"
            @event-click="onEventClick"
        />
    </div>
</template>
