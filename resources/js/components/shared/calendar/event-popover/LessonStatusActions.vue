<script lang="ts" setup>
import { Check, X } from '@lucide/vue';
import { ButtonGroup } from '@/components/ui/button-group';
import { Button } from '@/components/ui/button';
import { Lesson } from '@/types/calendar';
import { computed } from 'vue';
import { toDate, toWallClock } from '@/lib/calendar-utils';
import ConfirmDialog from '@/components/shared/ConfirmDialog.vue';
import { cancel, plan } from '@/routes/teacher/calendar/events';

const props = defineProps<{
    event: Lesson;
    timezone: string;
}>();

const isUpcoming = computed(() => toDate(props.event.startsAt) > toWallClock(new Date(), props.timezone));
const isPlannable = computed(() => isUpcoming.value && props.event.status === 'requested');
const isCancellable = computed(() => isUpcoming.value && props.event.status !== 'cancelled');
</script>

<template>
    <ButtonGroup>
        <ConfirmDialog :action="plan(Number(event.id))" :title="$t('calendar.event.dialog.plan_event')">
            <Button
                v-if="isPlannable"
                :aria-label="$t('calendar.event.popover.action.plan')"
                :title="$t('calendar.event.popover.action.plan')"
                class="rounded-full"
                size="icon-xs"
                variant="ok-outline"
            >
                <Check />
            </Button>
        </ConfirmDialog>
        <ConfirmDialog :action="cancel(Number(event.id))" :title="$t('calendar.event.dialog.cancel_event')" destructive>
            <Button
                v-if="isCancellable"
                :aria-label="$t('calendar.event.popover.action.cancel')"
                :title="$t('calendar.event.popover.action.cancel')"
                class="rounded-full"
                size="icon-xs"
                variant="destructive-outline"
            >
                <X />
            </Button>
        </ConfirmDialog>
    </ButtonGroup>
</template>
