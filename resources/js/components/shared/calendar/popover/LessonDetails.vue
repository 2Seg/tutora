<script lang="ts" setup>
import { Video } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { formatEventSchedule, getEventTitle } from '@/lib/calendar-event';
import StatusBadge from '@/components/shared/calendar/StatusBadge.vue';
import type { Lesson } from '@/types/calendar';

const props = defineProps<{
    event: Lesson;
}>();

const schedule = computed(() => formatEventSchedule(props.event));
</script>

<template>
    <h2 class="m-0 truncate pr-8">{{ getEventTitle(event) }}</h2>

    <div class="text-sm">
        <p class="m-0">{{ schedule.day }}</p>
        <p class="m-0 text-muted-foreground tabular-nums">{{ schedule.timeRange }}</p>
    </div>

    <p v-if="event.student" class="m-0 text-sm">{{ event.student.fullName }}</p>

    <div class="flex gap-2">
        <StatusBadge :variant="event.status" />
    </div>

    <Button v-if="event.meetingUrl" as-child size="sm" variant="outline">
        <a :href="event.meetingUrl" rel="noopener" target="_blank">
            <Video />
            {{ $t('calendar.popover.join_meeting') }}
        </a>
    </Button>
</template>
