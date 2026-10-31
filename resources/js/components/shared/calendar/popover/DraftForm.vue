<script lang="ts" setup>
import { computed, shallowRef } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatEventSchedule } from '@/lib/calendar-event';
import type { SlotSelection } from '@/types/calendar';

const props = defineProps<{
    range: SlotSelection;
}>();

const emit = defineEmits<{
    cancel: [];
}>();

const title = shallowRef('');
const schedule = computed(() => formatEventSchedule(props.range));

const submit = () => {};
</script>

<template>
    <form class="flex flex-col gap-3" @submit.prevent="submit">
        <Input
            v-model="title"
            :aria-label="$t('calendar.popover.title')"
            :placeholder="$t('calendar.popover.title.placeholder')"
            autofocus
            class="w-[calc(100%-3rem)] h-10 text-lg!"
            name="title"
        />

        <div class="text-sm">
            <p class="m-0">{{ schedule.day }}</p>
            <p class="m-0 text-muted-foreground tabular-nums">{{ schedule.timeRange }}</p>
        </div>

        <div class="flex justify-end gap-2">
            <Button size="sm" type="button" variant="ghost" @click="emit('cancel')">
                {{ $t('calendar.popover.cancel') }}
            </Button>
            <Button size="sm" type="submit">
                {{ $t('calendar.popover.create') }}
            </Button>
        </div>
    </form>
</template>
