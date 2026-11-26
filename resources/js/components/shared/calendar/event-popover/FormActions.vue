<script lang="ts" setup>
import { Button } from '@/components/ui/button';

defineProps<{
    submitLabel: string;
    processing: boolean;
}>();

const emit = defineEmits<{
    cancel: [];
}>();
</script>

<template>
    <div class="flex justify-end gap-2">
        <Button size="sm" type="button" variant="ghost" @click="emit('cancel')">
            {{ $t('calendar.event.popover.cancel') }}
        </Button>
        <Button :disabled="processing" size="sm">
            {{ submitLabel }}
        </Button>
    </div>
</template>
