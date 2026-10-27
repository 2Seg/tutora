<script lang="ts" setup>
import { Lock, X } from '@lucide/vue';
import { getActiveLanguage } from 'laravel-vue-i18n';
import { computed, shallowRef, watch } from 'vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Popover, PopoverAnchor, PopoverContent } from '@/components/ui/popover';
import { toDate } from '@/lib/calendar-utils';
import type { CalendarEvent, LessonStatus, SlotSelection } from '@/types/calendar';

const props = defineProps<{
    /** Element that contains the draft and the events: the anchor is looked up inside it. */
    container: HTMLElement | null;
    /** The slot being created, not saved yet. */
    draft: SlotSelection | null;
    /** The clicked event. */
    event: CalendarEvent | null;
    /** The draft is being moved: it changes column, so its element is replaced. */
    isMoving: boolean;
}>();

const emit = defineEmits<{
    close: [];
}>();

// Attached to the draft (`data-draft`) or, if there is none, to the selected event (`data-selected`).
const anchor = shallowRef<HTMLElement | null>(null);

watch(
    [() => props.draft, () => props.event, () => props.isMoving],
    () => {
        const selector = props.draft ? '[data-draft]' : '[data-selected]';
        anchor.value = props.container?.querySelector<HTMLElement>(selector) ?? null;
    },
    { flush: 'post' }, // after the DOM is updated, so the element exists
);

const isOpen = computed(
    () => !props.isMoving && anchor.value !== null && (props.draft !== null || props.event !== null),
);

const close = () => emit('close');

const statusBadges: Record<
    LessonStatus,
    { label: string; variant: 'secondary' | 'destructive' | 'warning' | 'outline' }
> = {
    planned: { label: 'Planned', variant: 'secondary' },
    cancelled: { label: 'Cancelled', variant: 'destructive' },
    requested: { label: 'Requested', variant: 'warning' },
    draft: { label: 'Draft', variant: 'outline' },
};
const defaultTitles: Record<CalendarEvent['type'], string> = {
    lesson: '(No title)',
    unavailability: 'Unavailable',
};

// The draft takes precedence: it is the one being edited.
const shown = computed(() => props.draft ?? props.event);
const savedEvent = computed(() => (props.draft ? null : props.event));

const title = computed(() =>
    savedEvent.value ? (savedEvent.value.title ?? defaultTitles[savedEvent.value.type]) : 'New lesson',
);

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
                <Badge v-if="savedEvent.type === 'lesson'" :variant="statusBadges[savedEvent.status].variant">
                    {{ statusBadges[savedEvent.status].label }}
                </Badge>
                <Badge v-else variant="hashed"><Lock />Unavailable</Badge>
            </div>
        </PopoverContent>
    </Popover>
</template>
