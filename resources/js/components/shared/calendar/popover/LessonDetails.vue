<script lang="ts" setup>
import { Video } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { formatEventSchedule, getEventTitle } from '@/lib/calendar-event';
import StatusBadge from '@/components/shared/calendar/StatusBadge.vue';
import type { Lesson } from '@/types/calendar';
import UserAvatar from '@/components/user/UserAvatar.vue';

const props = defineProps<{
    event: Lesson;
}>();

const schedule = computed(() => formatEventSchedule(props.event));
</script>

<template>
    <h2 class="m-0 truncate font-semibold pr-8">{{ getEventTitle(event) }}</h2>

    <div class="flex items-start justify-between gap-3">
        <div class="text-sm">
            <p class="m-0">{{ schedule.day }}</p>
            <p class="m-0 text-muted-foreground tabular-nums">{{ schedule.timeRange }}</p>
        </div>
        <StatusBadge :variant="event.status" />
    </div>

    <div v-if="event.student" class="flex gap-3 items-center">
        <UserAvatar :user="event.student" />
        <span>{{ event.student.fullName }}</span>
    </div>

    <Button v-if="event.meetingUrl" as-child size="sm" variant="outline">
        <a :href="event.meetingUrl" rel="noopener" target="_blank">
            <Video />
            {{ $t('calendar.event.popover.join_meeting') }}
        </a>
    </Button>
</template>
