import { trans } from 'laravel-vue-i18n';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { getDefaultTitle, getEventTitle } from '@/lib/calendar-event';

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
});
