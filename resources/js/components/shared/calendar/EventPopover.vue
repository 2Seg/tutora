<script lang="ts" setup>
import { SquarePen, X } from '@lucide/vue';
import { computed, shallowRef, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Popover, PopoverAnchor, PopoverContent } from '@/components/ui/popover';
import TimeOffDetails from '@/components/shared/calendar/event-popover/TimeOffDetails.vue';
import DraftForm from '@/components/shared/calendar/event-popover/DraftForm.vue';
import LessonDetails from '@/components/shared/calendar/event-popover/LessonDetails.vue';
import type { CalendarEvent, Draft, DraftUpdate, Lesson, TimeOff } from '@/types/calendar';
import { StudentSummary } from '@/types/Student';
import { ButtonGroup } from '@/components/ui/button-group';
import { trans } from 'laravel-vue-i18n';
import { toDate, toWallClock } from '@/lib/calendar-utils';

type Shown = { kind: 'draft'; event: Draft } | { kind: 'lesson'; event: Lesson } | { kind: 'time_off'; event: TimeOff };

const props = defineProps<{
    container: HTMLElement | null;
    draft: Draft | null;
    event: CalendarEvent | null;
    students?: StudentSummary[];
    isMoving: boolean;
    timezone: string;
}>();

const emit = defineEmits<{
    close: [];
    'update-draft': [update: DraftUpdate];
}>();

const anchor = shallowRef<HTMLElement>();
const shown = shallowRef<Shown>();

watch(
    () => [props.draft, props.event, props.isMoving],
    () => {
        if (props.draft) {
            shown.value = { kind: 'draft', event: props.draft };
        } else if (props.event) {
            shown.value =
                props.event.type === 'lesson'
                    ? { kind: 'lesson', event: props.event }
                    : { kind: 'time_off', event: props.event };
        } else {
            return;
        }
        const selector = props.draft ? '[data-draft]' : '[data-selected]';
        anchor.value = props.container?.querySelector<HTMLElement>(selector) ?? anchor.value;
    },
    { flush: 'post', immediate: true },
);

const reference = computed(() => {
    const element = anchor.value;
    if (!element) return undefined;
    let lastRect = element.getBoundingClientRect();
    return {
        contextElement: element,
        getBoundingClientRect: () => {
            if (element.isConnected) lastRect = element.getBoundingClientRect();
            return lastRect;
        },
    };
});

const isOpen = computed(() => !props.isMoving && !!anchor.value && !!(props.draft ?? props.event));
const isEditable = computed(() => {
    if (shown.value?.kind === 'time_off') return true;
    if (shown.value?.kind !== 'lesson') return false;
    return toDate(shown.value.event.startsAt) > toWallClock(new Date(), props.timezone);
});
</script>

<template>
    <Popover :open="isOpen">
        <PopoverAnchor :reference="reference" />

        <PopoverContent
            align="start"
            class="relative flex w-80 flex-col gap-3"
            data-keep-draft
            hide-when-detached
            side="right"
            @interact-outside.prevent
        >
            <div class="absolute top-2 right-2 flex flex-row gap-0.5">
                <ButtonGroup>
                    <Button
                        v-if="isEditable"
                        :aria-label="$t('calendar.event.popover.edit')"
                        :title="trans('calendar.event.popover.edit')"
                        size="icon-sm"
                        variant="ghost"
                    >
                        <SquarePen />
                    </Button>
                </ButtonGroup>
                <Button
                    :aria-label="$t('calendar.event.popover.close')"
                    :title="trans('calendar.event.popover.close')"
                    size="icon-sm"
                    variant="ghost"
                    @click="emit('close')"
                >
                    <X />
                </Button>
            </div>

            <DraftForm
                v-if="shown?.kind === 'draft'"
                :draft="shown.event"
                :students="props.students"
                :timezone="props.timezone"
                @cancel="emit('close')"
                @update="emit('update-draft', $event)"
            />
            <LessonDetails v-else-if="shown?.kind === 'lesson'" :event="shown.event" :timezone="props.timezone" />
            <TimeOffDetails v-else-if="shown?.kind === 'time_off'" :event="shown.event" />
        </PopoverContent>
    </Popover>
</template>
