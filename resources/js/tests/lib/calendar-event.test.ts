import { Lock } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import {
    getDefaultTitle,
    getEventAppearance,
    getEventDescription,
    getEventStatus,
    getEventTitle,
} from '@/lib/calendar-event';
import type { BlockEvent, LessonEvent, LessonStatus } from '@/types/calendar';

const lesson = (status: LessonStatus, title?: string): LessonEvent => ({
    id: 1,
    startTime: '2026-10-28T09:00:00',
    endTime: '2026-10-28T10:00:00',
    type: 'lesson',
    status,
    title,
});

const block: BlockEvent = {
    id: 2,
    startTime: '2026-10-28T12:00:00',
    endTime: '2026-10-28T13:00:00',
    type: 'unavailable',
};

vi.mock('laravel-vue-i18n', () => ({
    trans: vi.fn((key: string) => `translated:${key}`),
}));

describe('calendar-event', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it.each(['lesson', 'unavailable'] as const)('translates the %s default title from its type key', (type) => {
        expect(getDefaultTitle(type)).toBe(`translated:calendar.event.default_title.${type}`);
        expect(trans).toHaveBeenCalledWith(`calendar.event.default_title.${type}`);
    });

    it('uses the event title when it has one', () => {
        expect(getEventTitle({ type: 'lesson', title: 'Piano with Alice' })).toBe('Piano with Alice');
        expect(trans).not.toHaveBeenCalled();
    });

    it.each(['lesson', 'unavailable'] as const)('falls back to the default title for an untitled %s', (type) => {
        expect(getEventTitle({ type })).toBe(`translated:calendar.event.default_title.${type}`);
    });

    it('returns the status of a lesson and null for a block', () => {
        expect(getEventStatus(lesson('requested'))).toBe('requested');
        expect(getEventStatus(block)).toBeNull();
    });

    it.each([
        ['planned', 'badge-secondary'],
        ['cancelled', 'badge-destructive'],
        ['requested', 'badge-warning'],
        ['draft', 'badge-outline'],
    ] as const)('maps a %s lesson to %s without an icon', (status, className) => {
        const appearance = getEventAppearance(lesson(status));

        expect(appearance.className).toBe(className);
        expect(appearance.icon).toBeUndefined();
        expect(appearance.statusLabel).toBe(`translated:calendar.event.status.${status}`);
    });

    it('gives a block the hashed class, the lock icon and no status label', () => {
        const appearance = getEventAppearance(block);

        expect(appearance.className).toBe('badge-hashed');
        expect(appearance.icon).toBe(Lock);
        expect(appearance.statusLabel).toBeNull();
        expect(appearance.title).toBe('translated:calendar.event.default_title.unavailable');
    });

    it('uses the lesson title in the appearance', () => {
        expect(getEventAppearance(lesson('planned', 'Piano with Alice')).title).toBe('Piano with Alice');
    });

    it('describes a lesson with its title, status and range', () => {
        expect(getEventDescription(lesson('planned', 'Piano with Alice'), '09:00 – 10:00')).toBe(
            'Piano with Alice, translated:calendar.event.status.planned, 09:00 – 10:00',
        );
    });

    it('describes a block with its title and range only', () => {
        expect(getEventDescription(block, '12:00 – 13:00')).toBe(
            'translated:calendar.event.default_title.unavailable, 12:00 – 13:00',
        );
    });
});
