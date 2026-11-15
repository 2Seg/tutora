<script lang="ts" setup>
import { Form } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';
import StatusBadge from '@/components/shared/calendar/StatusBadge.vue';
import FormActions from '@/components/shared/calendar/event-popover/FormActions.vue';
import ScheduleInputs from '@/components/shared/calendar/event-popover/ScheduleInputs.vue';
import { formatEventSchedule } from '@/lib/calendar-event';
import { toDate } from '@/lib/calendar-utils';
import { update } from '@/routes/teacher/calendar/events';
import type { SlotSelection, TimeOff } from '@/types/calendar';

const props = defineProps<{
    event: TimeOff;
    timezone: string;
}>();

const emit = defineEmits<{
    done: [];
    update: [slot: SlotSelection];
}>();

const slot = computed<SlotSelection>(() => ({
    startsAt: toDate(props.event.startsAt),
    endsAt: toDate(props.event.endsAt),
}));
const schedule = computed(() => formatEventSchedule(slot.value));
</script>

<template>
    <Form
        #default="{ errors, processing }"
        :action="update(Number(event.id))"
        :options="{ preserveScroll: true }"
        class="flex flex-col gap-3"
        @success="emit('done')"
    >
        <StatusBadge variant="time_off" />

        <h2 class="m-0 truncate pr-8">{{ trans('calendar.event.popover.time_off') }}</h2>

        <div class="text-sm">
            <p class="m-0">{{ schedule.day }}</p>
            <ScheduleInputs
                :errors="errors"
                :is-lesson="false"
                :slot="slot"
                :timezone="timezone"
                @update="emit('update', $event)"
            />
        </div>
        <p v-if="errors.starts_at" class="text-sm text-destructive">{{ errors.starts_at }}</p>
        <p v-if="errors.ends_at" class="text-sm text-destructive">{{ errors.ends_at }}</p>

        <FormActions
            :processing="processing"
            :submit-label="trans('calendar.event.popover.save')"
            @cancel="emit('done')"
        />
    </Form>
</template>
