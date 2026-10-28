import { trans } from 'laravel-vue-i18n';

import type { CalendarEvent, EventType } from '@/types/calendar';

export const getDefaultTitle = (type: EventType) => trans(`calendar.event.default_title.${type}`);

export const getEventTitle = (event: Pick<CalendarEvent, 'type' | 'title'>) =>
    event.title ?? getDefaultTitle(event.type);
