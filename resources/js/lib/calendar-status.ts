import { BadgeCheck, Lock } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import type { Component } from 'vue';

import type { BadgeVariants } from '@/components/ui/badge';
import type { LessonStatus } from '@/types/calendar';

export type StatusBadgeVariant = LessonStatus | 'blocked';

const statusBadges: Record<StatusBadgeVariant, { variant: BadgeVariants['variant']; icon?: Component }> = {
    planned: { variant: 'secondary', icon: BadgeCheck },
    cancelled: { variant: 'destructive' },
    requested: { variant: 'warning' },
    draft: { variant: 'outline' },
    blocked: { variant: 'hashed', icon: Lock },
};

export const statusVariants = Object.keys(statusBadges) as StatusBadgeVariant[];

export const getStatusLabel = (status: StatusBadgeVariant) => trans(`calendar.event.status.${status}`);

export const getStatusBadge = (status: StatusBadgeVariant) => ({
    ...statusBadges[status],
    label: getStatusLabel(status),
});
