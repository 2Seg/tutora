<script lang="ts" setup>
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { getStatusBadge, type StatusBadgeVariant } from '@/lib/calendar-status';

const props = defineProps<{
    variant: StatusBadgeVariant;
}>();

const badge = computed(() => getStatusBadge(props.variant));
</script>

<template>
    <Badge :variant="badge.variant">
        <component :is="badge.icon" v-if="badge.icon" class="size-3 text-current" />
        {{ badge.label }}
    </Badge>
</template>
