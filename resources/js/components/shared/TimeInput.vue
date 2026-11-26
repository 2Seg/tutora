<script lang="ts" setup>
import { nextTick, type HTMLAttributes } from 'vue';
import { Input } from '@/components/ui/input';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();
const time = defineModel<string>();

const blendWithSurroundingText =
    'h-auto w-auto rounded-sm border-0 bg-transparent p-0 text-sm shadow-none dark:bg-transparent';
const highlightWhenHoveredOrFocused = 'hover:bg-accent focus-visible:bg-accent focus-visible:ring-0';
const looksLikeMutedTime = 'tabular-nums text-muted-foreground aria-invalid:text-destructive';
const hideBrowserClockIcon = '[&::-webkit-calendar-picker-indicator]:hidden';

async function updateOnceTimeIsComplete(event: Event) {
    const input = event.target as HTMLInputElement;
    const typedTime = input.value;
    const isComplete = typedTime !== '';
    if (isComplete) time.value = typedTime;
    await nextTick();
    input.value = time.value ?? '';
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
