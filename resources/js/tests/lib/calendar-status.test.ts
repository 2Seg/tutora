import { BadgeCheck, Lock } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { getStatusBadge, getStatusLabel, statusVariants } from '@/lib/calendar-status';

vi.mock('laravel-vue-i18n', () => ({
    trans: vi.fn((key: string) => `translated:${key}`),
}));

describe('calendar-status', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('lists the statuses in legend order', () => {
        expect(statusVariants).toEqual(['planned', 'cancelled', 'requested', 'draft', 'blocked']);
    });

    it.each(statusVariants)('translates the %s label from its status key', (status) => {
        expect(getStatusLabel(status)).toBe(`translated:calendar.event.status.${status}`);
        expect(trans).toHaveBeenCalledWith(`calendar.event.status.${status}`);
    });

    it.each([
        ['planned', 'secondary'],
        ['cancelled', 'destructive'],
        ['requested', 'warning'],
        ['draft', 'outline'],
        ['blocked', 'hashed'],
    ] as const)('maps %s to the %s badge variant', (status, variant) => {
        expect(getStatusBadge(status).variant).toBe(variant);
    });

    it.each([
        ['planned', BadgeCheck],
        ['cancelled', undefined],
        ['requested', undefined],
        ['draft', undefined],
        ['blocked', Lock],
    ] as const)('gives %s the expected icon', (status, icon) => {
        expect(getStatusBadge(status).icon).toBe(icon);
    });

    it('includes the translated label in the badge', () => {
        expect(getStatusBadge('requested').label).toBe('translated:calendar.event.status.requested');
    });
});
