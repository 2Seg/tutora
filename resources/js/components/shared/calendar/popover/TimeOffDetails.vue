<script lang="ts" setup>
import { computed } from 'vue';
import { formatEventSchedule, getEventTitle } from '@/lib/calendar-event';
import StatusBadge from '@/components/shared/calendar/StatusBadge.vue';
import type { TimeOff } from '@/types/calendar';

const props = defineProps<{
    event: TimeOff;
}>();

const schedule = computed(() => formatEventSchedule(props.event));
</script>

<template>
    <h2 class="m-0 truncate pr-8">{{ getEventTitle(event) }}</h2>

    <div class="text-sm">
        <p class="m-0">{{ schedule.day }}</p>
        <p class="m-0 text-muted-foreground tabular-nums">{{ schedule.timeRange }}</p>
    </div>

    <div class="flex gap-2">
        <StatusBadge variant="time_off" />
    </div>
</template>
