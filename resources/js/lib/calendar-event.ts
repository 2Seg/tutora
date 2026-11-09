import { Lock } from '@lucide/vue';
import { getActiveLanguage, trans } from 'laravel-vue-i18n';
import type { Component } from 'vue';
import { getStatusLabel } from '@/lib/calendar-status';
import { CALENDAR_TIME_ZONE, toDate } from '@/lib/calendar-utils';
import type { CalendarEvent, EventType, Lesson, LessonStatus, TimeOff } from '@/types/calendar';

const eventAppearances: Record<LessonStatus | TimeOff['type'], { className: string; icon?: Component }> = {
    planned: { className: 'badge-secondary' },
    cancelled: { className: 'badge-destructive' },
    requested: { className: 'badge-warning' },
    draft: { className: 'badge-outline' },
    time_off: { className: 'badge-hashed', icon: Lock },
};

export const getDefaultTitle = (type: EventType) => trans(`calendar.event.default_title.${type}`);

export const getEventTitle = (event: Pick<Lesson, 'type' | 'title'> | Pick<TimeOff, 'type'>) =>
    (event.type === 'lesson' && event.title) || getDefaultTitle(event.type);

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

/** "25 min", "1h20", "2h". */
export const formatDuration = (minutes: number) => {
    const hours = Math.floor(minutes / 60);
    const rest = minutes % 60;
    if (!hours) return `${rest} min`;
    return rest ? `${hours}h${String(rest).padStart(2, '0')}` : `${hours}h`;
};

export const formatEventSchedule = (range: Pick<CalendarEvent, 'startsAt' | 'endsAt'>) => {
    const locale = getActiveLanguage().replace('_', '-');
    const start = toDate(range.startsAt);
    const end = toDate(range.endsAt);
    return {
        day: new Intl.DateTimeFormat(locale, {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            timeZone: CALENDAR_TIME_ZONE,
        }).format(start),
        timeRange: new Intl.DateTimeFormat(locale, {
            hour: '2-digit',
            minute: '2-digit',
            hourCycle: 'h23',
            timeZone: CALENDAR_TIME_ZONE,
        }).formatRange(start, end),
        duration: formatDuration(Math.round((end.getTime() - start.getTime()) / 60_000)),
    };
};
