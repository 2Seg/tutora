<script lang="ts" setup>
import { X } from '@lucide/vue';
import { getActiveLanguage } from 'laravel-vue-i18n';
import { computed, shallowRef, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Popover, PopoverAnchor, PopoverContent } from '@/components/ui/popover';
import { getDefaultTitle, getEventStatus, getEventTitle } from '@/lib/calendar-event';
import { CALENDAR_TIME_ZONE, toDate } from '@/lib/calendar-utils';
import StatusBadge from '@/components/shared/calendar/StatusBadge.vue';
import type { CalendarEvent, SlotSelection } from '@/types/calendar';

const props = defineProps<{
    container: HTMLElement | null;
    draft: SlotSelection | null;
    event: CalendarEvent | null;
    isMoving: boolean;
}>();

const emit = defineEmits<{
    close: [];
}>();

const anchor = shallowRef<HTMLElement>();
const shown = shallowRef<{ range: SlotSelection | CalendarEvent; event: CalendarEvent | null }>();

watch(
    () => [props.draft, props.event, props.isMoving],
    () => {
        const range = props.draft ?? props.event;
        if (!range) return;
        shown.value = { range, event: props.draft ? null : props.event };
        const selector = props.draft ? '[data-draft]' : '[data-selected]';
        anchor.value = props.container?.querySelector<HTMLElement>(selector) ?? anchor.value;
    },
    { flush: 'post', immediate: true },
);

const isOpen = computed(() => !props.isMoving && !!anchor.value && !!(props.draft ?? props.event));

const details = computed(() => {
    if (!shown.value) return null;
    const { range, event } = shown.value;
    const locale = getActiveLanguage().replace('_', '-');
    const start = toDate(range.startTime);
    return {
        title: event ? getEventTitle(event) : getDefaultTitle('lesson'),
        day: new Intl.DateTimeFormat(locale, {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            timeZone: CALENDAR_TIME_ZONE,
        }).format(start),
        timeRange: new Intl.DateTimeFormat(locale, {
            hour: '2-digit',
            minute: '2-digit',
            hourCycle: 'h23',
            timeZone: CALENDAR_TIME_ZONE,
        }).formatRange(start, toDate(range.endTime)),
        status: event ? (getEventStatus(event) ?? ('blocked' as const)) : null,
    };
});
</script>

<template>
    <Popover :open="isOpen">
        <PopoverAnchor :reference="anchor" />

        <PopoverContent
            align="start"
            class="flex w-80 flex-col gap-3"
            data-keep-draft
            hide-when-detached
            side="right"
            @interact-outside.prevent
        >
            <template v-if="details">
                <div class="flex items-start gap-2">
                    <h3 class="m-0 min-w-0 flex-1 truncate font-semibold">{{ details.title }}</h3>
                    <Button
                        aria-label="Close"
                        class="-mt-1 -mr-2"
                        size="icon-sm"
                        variant="ghost"
                        @click="emit('close')"
                    >
                        <X />
                    </Button>
                </div>

                <div class="text-sm">
                    <p class="m-0">{{ details.day }}</p>
                    <p class="m-0 text-muted-foreground tabular-nums">{{ details.timeRange }}</p>
                </div>

                <div v-if="details.status" class="flex gap-2">
                    <StatusBadge :variant="details.status" />
                </div>
            </template>
        </PopoverContent>
    </Popover>
</template>
