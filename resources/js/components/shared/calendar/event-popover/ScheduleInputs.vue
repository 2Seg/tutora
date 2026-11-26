<script lang="ts" setup>
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';
import TimeInput from '@/components/shared/TimeInput.vue';
import {
    fromWallClock,
    minutesToTime,
    slotMinutes,
    timeToMinutes,
    withEnd,
    withLessonStart,
    withStart,
} from '@/lib/calendar-utils';
import type { SlotSelection } from '@/types/calendar';

const props = defineProps<{
    slot: SlotSelection;
    timezone: string;
    isLesson: boolean;
    errors: Record<string, string>;
}>();

const emit = defineEmits<{
    update: [slot: SlotSelection];
}>();

const times = computed(() => {
    const { startMin, endMin } = slotMinutes(props.slot);
    return { start: minutesToTime(startMin), end: minutesToTime(endMin) };
});

function moveStartTo(time: string | undefined) {
    if (!time) return;
    const startMin = timeToMinutes(time);
    emit('update', props.isLesson ? withLessonStart(props.slot, startMin) : withStart(props.slot, startMin));
}

function moveEndTo(time: string | undefined) {
    if (time) emit('update', withEnd(props.slot, timeToMinutes(time)));
}
</script>

<template>
    <p class="m-0 flex items-center gap-2 text-muted-foreground tabular-nums">
        <TimeInput
            :aria-invalid="!!errors.starts_at"
            :aria-label="trans('calendar.event.popover.time.start')"
            :model-value="times.start"
            @update:model-value="moveStartTo"
        />
        <span>–</span>
        <span v-if="isLesson">{{ times.end }}</span>
        <TimeInput
            v-else
            :aria-invalid="!!errors.ends_at"
            :aria-label="trans('calendar.event.popover.time.end')"
            :model-value="times.end"
            @update:model-value="moveEndTo"
        />
    </p>
    <input :value="fromWallClock(slot.startsAt, timezone).toISOString()" name="starts_at" type="hidden" />
    <input :value="fromWallClock(slot.endsAt, timezone).toISOString()" name="ends_at" type="hidden" />
</template>
