import { Lock } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import type { Component } from 'vue';

import { getStatusLabel } from '@/lib/calendar-status';
import type { BlockEvent, CalendarEvent, EventType, LessonStatus } from '@/types/calendar';

const eventAppearances: Record<LessonStatus | BlockEvent['type'], { className: string; icon?: Component }> = {
    planned: { className: 'badge-secondary' },
    cancelled: { className: 'badge-destructive' },
    requested: { className: 'badge-warning' },
    draft: { className: 'badge-outline' },
    unavailable: { className: 'badge-hashed', icon: Lock },
};

export const getDefaultTitle = (type: EventType) => trans(`calendar.event.default_title.${type}`);

export const getEventTitle = (event: Pick<CalendarEvent, 'type' | 'title'>) =>
    event.title ?? getDefaultTitle(event.type);

export const getEventStatus = (event: CalendarEvent) => (event.type === 'lesson' ? event.status : null);

export const getEventAppearance = (event: CalendarEvent) => {
    const status = getEventStatus(event);
    return {
        ...eventAppearances[event.type === 'lesson' ? event.status : event.type],
        title: getEventTitle(event),
        statusLabel: status ? getStatusLabel(status) : null,
    };
};

export const getEventDescription = (event: CalendarEvent, range: string) => {
    const { title, statusLabel } = getEventAppearance(event);
    return [title, statusLabel, range].filter(Boolean).join(', ');
};
