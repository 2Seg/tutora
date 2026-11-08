<script lang="ts" setup>
import { X } from '@lucide/vue';
import { computed, shallowRef, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Popover, PopoverAnchor, PopoverContent } from '@/components/ui/popover';
import TimeOffDetails from '@/components/shared/calendar/event-popover/TimeOffDetails.vue';
import DraftForm from '@/components/shared/calendar/event-popover/DraftForm.vue';
import LessonDetails from '@/components/shared/calendar/event-popover/LessonDetails.vue';
import type { CalendarEvent, Draft, DraftUpdate, Lesson, TimeOff } from '@/types/calendar';
import { StudentSummary } from '@/types/Student';

type Shown = { kind: 'draft'; draft: Draft } | { kind: 'lesson'; event: Lesson } | { kind: 'time_off'; event: TimeOff };

const props = defineProps<{
    container: HTMLElement | null;
    draft: Draft | null;
    event: CalendarEvent | null;
    students?: StudentSummary[];
    isMoving: boolean;
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
            shown.value = { kind: 'draft', draft: props.draft };
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
            <Button
                :aria-label="$t('calendar.event.popover.close')"
                class="absolute top-2 right-2"
                size="icon-sm"
                variant="ghost"
                @click="emit('close')"
            >
                <X />
            </Button>

            <DraftForm
                v-if="shown?.kind === 'draft'"
                :draft="shown.draft"
                :students="props.students"
                @cancel="emit('close')"
                @update="emit('update-draft', $event)"
            />
            <LessonDetails v-else-if="shown?.kind === 'lesson'" :event="shown.event" />
            <TimeOffDetails v-else-if="shown?.kind === 'time_off'" :event="shown.event" />
        </PopoverContent>
    </Popover>
</template>
