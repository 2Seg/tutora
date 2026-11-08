<script lang="ts" setup>
import { computed } from 'vue';
import { formatEventSchedule } from '@/lib/calendar-event';
import StatusBadge from '@/components/shared/calendar/StatusBadge.vue';
import type { TimeOff } from '@/types/calendar';
import { trans } from 'laravel-vue-i18n';

const props = defineProps<{
    event: TimeOff;
}>();

const schedule = computed(() => formatEventSchedule(props.event));
</script>

<template>
    <h2 class="m-0 truncate font-semibold pr-8 flex items-center gap-2">
        {{ trans('calendar.event.popover.time_off') }}
    </h2>

    <div class="flex items-start justify-between gap-3">
        <div class="text-sm">
            <p class="m-0">{{ schedule.day }}</p>
            <p class="m-0 text-muted-foreground tabular-nums">{{ schedule.timeRange }}</p>
        </div>
        <StatusBadge variant="time_off" />
    </div>
</template>
