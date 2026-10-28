<script lang="ts" setup>
import { X } from '@lucide/vue';
import { getActiveLanguage } from 'laravel-vue-i18n';
import { computed, shallowRef, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Popover, PopoverAnchor, PopoverContent } from '@/components/ui/popover';
import { getDefaultTitle, getEventTitle } from '@/lib/calendar-event';
import { toDate } from '@/lib/calendar-utils';
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

const anchor = shallowRef<HTMLElement | null>(null);

watch(
    [() => props.draft, () => props.event, () => props.isMoving],
    () => {
        const selector = props.draft ? '[data-draft]' : '[data-selected]';
        const element = props.container?.querySelector<HTMLElement>(selector);
        if (element) anchor.value = element;
    },
    { flush: 'post' },
);

const isOpen = computed(
    () => !props.isMoving && anchor.value !== null && (props.draft !== null || props.event !== null),
);

const close = () => emit('close');

const shown = shallowRef<SlotSelection | CalendarEvent | null>(null);
const savedEvent = shallowRef<CalendarEvent | null>(null);

watch(
    [() => props.draft, () => props.event],
    ([draft, event]) => {
        if (!draft && !event) return;
        shown.value = draft ?? event;
        savedEvent.value = draft ? null : event;
    },
    { immediate: true },
);

const title = computed(() => (savedEvent.value ? getEventTitle(savedEvent.value) : getDefaultTitle('lesson')));

const locale = computed(() => getActiveLanguage().replace('_', '-'));
const dayFormat = computed(
    () => new Intl.DateTimeFormat(locale.value, { weekday: 'long', day: 'numeric', month: 'long' }),
);
const timeFormat = computed(
    () => new Intl.DateTimeFormat(locale.value, { hour: '2-digit', minute: '2-digit', hourCycle: 'h23' }),
);

const day = computed(() => (shown.value ? dayFormat.value.format(toDate(shown.value.startTime)) : ''));
const timeRange = computed(() =>
    shown.value ? timeFormat.value.formatRange(toDate(shown.value.startTime), toDate(shown.value.endTime)) : '',
);
</script>

<template>
    <Popover :open="isOpen">
        <PopoverAnchor :reference="anchor ?? undefined" />

        <PopoverContent
            align="start"
            class="flex w-80 flex-col gap-3"
            data-keep-draft
            hide-when-detached
            side="right"
            @interact-outside.prevent
        >
            <div class="flex items-start gap-2">
                <h3 class="m-0 min-w-0 flex-1 truncate font-semibold">{{ title }}</h3>
                <Button aria-label="Close" class="-mt-1 -mr-2" size="icon-sm" variant="ghost" @click="close">
                    <X />
                </Button>
            </div>

            <div class="text-sm">
                <p class="m-0">{{ day }}</p>
                <p class="m-0 text-muted-foreground tabular-nums">{{ timeRange }}</p>
            </div>

            <div v-if="savedEvent" class="flex gap-2">
                <StatusBadge :variant="savedEvent.type === 'lesson' ? savedEvent.status : 'unavailable'" />
            </div>
        </PopoverContent>
    </Popover>
</template>
