<script lang="ts" setup>
import { Video } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { formatEventSchedule, getEventTitle } from '@/lib/calendar-event';
import { fromWallClock, toDate } from '@/lib/calendar-utils';
import StatusBadge from '@/components/shared/calendar/StatusBadge.vue';
import type { Lesson } from '@/types/calendar';
import UserAvatar from '@/components/user/UserAvatar.vue';
import { ClockAlert } from '@lucide/vue';

const props = defineProps<{
    event: Lesson;
    timezone: string;
}>();

const schedule = computed(() => formatEventSchedule(props.event));
const studentSchedule = computed(() => {
    const studentTimezone = props.event.student?.timezone;
    if (!studentTimezone || studentTimezone === props.timezone) return null;

    return formatEventSchedule(
        {
            startsAt: fromWallClock(toDate(props.event.startsAt), props.timezone),
            endsAt: fromWallClock(toDate(props.event.endsAt), props.timezone),
        },
        studentTimezone,
    );
});
</script>

<template>
    <StatusBadge :variant="event.status" />

    <h2 class="m-0 truncate pr-8">{{ getEventTitle(event) }}</h2>

    <div class="text-sm m-0">
        <div class="flex items-center justify-between gap-2">
            <p class="flex flex-col">
                <span>{{ schedule.day }}</span>
                <span
                    v-if="studentSchedule && schedule.day !== studentSchedule.day"
                    :title="$t('calendar.event.popover.time.student')"
                    class="flex items-center gap-2 text-secondary-foreground"
                >
                    {{ studentSchedule.day }}
                    <ClockAlert :size="15" />
                </span>
            </p>
            <span class="text-secondary-foreground font-bold">{{ schedule.duration }}</span>
        </div>
        <p class="flex flex-col tabular-nums">
            <span class="text-muted-foreground">{{ schedule.timeRange }}</span>
            <span
                v-if="studentSchedule"
                :title="$t('calendar.event.popover.time.student')"
                class="flex items-center gap-2 text-secondary-foreground"
            >
                {{ studentSchedule.timeRange }}
                <ClockAlert :size="15" />
            </span>
        </p>
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
