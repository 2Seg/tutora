<script lang="ts" setup>
import type { HTMLAttributes } from 'vue';
import { Input } from '@/components/ui/input';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();
const time = defineModel<string>();

const blendWithSurroundingText =
    'h-auto w-auto -mx-1 mr-0.5 rounded-sm border-0 bg-transparent p-0 text-sm shadow-none dark:bg-transparent';
const highlightWhenHoveredOrFocused = 'hover:bg-accent focus-visible:bg-accent focus-visible:ring-0';
const looksLikeMutedTime = 'tabular-nums text-muted-foreground aria-invalid:text-destructive';
const hideBrowserClockIcon = '[&::-webkit-calendar-picker-indicator]:hidden';

function updateOnceTimeIsComplete(event: Event) {
    const typedTime = (event.target as HTMLInputElement).value;
    const isComplete = typedTime !== '';
    if (isComplete) time.value = typedTime;
}
</script>

<template>
    <Input
        :class="[
            blendWithSurroundingText,
            highlightWhenHoveredOrFocused,
            looksLikeMutedTime,
            hideBrowserClockIcon,
            props.class,
        ]"
        :model-value="time"
        step="600"
        type="time"
        @change="updateOnceTimeIsComplete"
    />
</template>
