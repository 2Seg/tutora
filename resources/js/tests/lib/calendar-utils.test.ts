import { afterEach, describe, expect, it, vi } from 'vitest';
import {
    addDays,
    atMinutes,
    describeTimezone,
    isSameDay,
    layoutOverlaps,
    MINUTES_PER_DAY,
    minutesOfDay,
    percentOfDay,
    segmentForDay,
    startOfWeek,
    toDate,
} from '@/lib/calendar-utils';

const d = (y: number, m: number, day: number, h = 0, min = 0, s = 0) => new Date(Date.UTC(y, m - 1, day, h, min, s));

describe('MINUTES_PER_DAY', () => {
    it('is 1440', () => {
        expect(MINUTES_PER_DAY).toBe(1440);
    });
});

describe('toDate', () => {
    it('returns the same instance when given a Date', () => {
        const date = d(2024, 1, 15);
        expect(toDate(date)).toBe(date);
    });

    it('parses an ISO string', () => {
        const result = toDate('2024-01-15T10:00:00Z');
        expect(result).toBeInstanceOf(Date);
        expect(result.getTime()).toBe(Date.UTC(2024, 0, 15, 10, 0, 0));
    });

    it('returns an Invalid Date for an unparseable string', () => {
        expect(Number.isNaN(toDate('not a date').getTime())).toBe(true);
    });
});

describe('startOfWeek', () => {
    it('returns the Monday of the week by default', () => {
        // Wed 10 Jan 2024
        expect(startOfWeek(d(2024, 1, 10))).toEqual(d(2024, 1, 8));
    });

    it('returns the same day when the date is already Monday', () => {
        expect(startOfWeek(d(2024, 1, 8))).toEqual(d(2024, 1, 8));
    });

    it('treats Sunday as the last day of the week (Monday-based)', () => {
        // Sun 14 Jan 2024
        expect(startOfWeek(d(2024, 1, 14))).toEqual(d(2024, 1, 8));
    });

    it('resets the time to local midnight', () => {
        expect(startOfWeek(d(2024, 1, 10, 17, 45, 30))).toEqual(d(2024, 1, 8, 0, 0, 0));
    });

    it('supports Sunday-based weeks', () => {
        expect(startOfWeek(d(2024, 1, 10), 0)).toEqual(d(2024, 1, 7)); // Wed -> Sun
        expect(startOfWeek(d(2024, 1, 14), 0)).toEqual(d(2024, 1, 14)); // Sun -> itself
        expect(startOfWeek(d(2024, 1, 13), 0)).toEqual(d(2024, 1, 7)); // Sat -> Sun
    });

    it('crosses month boundaries (including leap February)', () => {
        // Fri 1 Mar 2024 -> Mon 26 Feb 2024
        expect(startOfWeek(d(2024, 3, 1))).toEqual(d(2024, 2, 26));
    });

    it('crosses year boundaries', () => {
        // Thu 1 Jan 2026 -> Mon 29 Dec 2025
        expect(startOfWeek(d(2026, 1, 1))).toEqual(d(2025, 12, 29));
    });

    it('does not mutate its input', () => {
        const input = d(2024, 1, 10, 12, 0);
        const snapshot = input.getTime();
        startOfWeek(input);
        expect(input.getTime()).toBe(snapshot);
    });
});

describe('addDays', () => {
    it('adds days within a month', () => {
        expect(addDays(d(2024, 1, 10), 5)).toEqual(d(2024, 1, 15));
    });

    it('returns midnight of the same day when adding 0', () => {
        expect(addDays(d(2024, 1, 10, 15, 30), 0)).toEqual(d(2024, 1, 10));
    });

    it('subtracts days with a negative value', () => {
        expect(addDays(d(2024, 1, 10), -10)).toEqual(d(2023, 12, 31));
    });

    it('rolls over month ends', () => {
        expect(addDays(d(2024, 1, 31), 1)).toEqual(d(2024, 2, 1));
    });

    it('rolls over year ends', () => {
        expect(addDays(d(2024, 12, 31), 1)).toEqual(d(2025, 1, 1));
    });

    it('handles leap days', () => {
        expect(addDays(d(2024, 2, 28), 1)).toEqual(d(2024, 2, 29));
        expect(addDays(d(2023, 2, 28), 1)).toEqual(d(2023, 3, 1));
    });

    it('drops the time of day', () => {
        const result = addDays(d(2024, 1, 10, 23, 59, 59), 1);
        expect(result.getUTCHours()).toBe(0);
        expect(result.getUTCMinutes()).toBe(0);
        expect(result.getUTCSeconds()).toBe(0);
    });

    it('does not mutate its input', () => {
        const input = d(2024, 1, 10, 12, 0);
        const snapshot = input.getTime();
        addDays(input, 3);
        expect(input.getTime()).toBe(snapshot);
    });
});

describe('isSameDay', () => {
    it('is true for the same day at different times', () => {
        expect(isSameDay(d(2024, 1, 15, 0, 0), d(2024, 1, 15, 23, 59))).toBe(true);
    });

    it('is false for adjacent days, even a minute apart', () => {
        expect(isSameDay(d(2024, 1, 15, 23, 59), d(2024, 1, 16, 0, 0))).toBe(false);
    });

    it('is false when only the day-of-month matches', () => {
        expect(isSameDay(d(2024, 1, 5), d(2024, 2, 5))).toBe(false);
    });

    it('is false when only the month and day match', () => {
        expect(isSameDay(d(2024, 1, 5), d(2025, 1, 5))).toBe(false);
    });
});

describe('percentOfDay', () => {
    it('maps the start of the day to 0%', () => {
        expect(percentOfDay(0)).toBe('0%');
    });

    it('maps noon to 50%', () => {
        expect(percentOfDay(720)).toBe('50%');
    });

    it('maps a quarter of the day to 25%', () => {
        expect(percentOfDay(360)).toBe('25%');
    });

    it('maps the end of the day to 100%', () => {
        expect(percentOfDay(MINUTES_PER_DAY)).toBe('100%');
    });

    it('keeps fractional precision', () => {
        expect(percentOfDay(60)).toBe(`${(60 / 1440) * 100}%`);
        expect(parseFloat(percentOfDay(60))).toBeCloseTo(4.1667, 4);
    });
});

describe('minutesOfDay', () => {
    it('is 0 at midnight', () => {
        expect(minutesOfDay(d(2024, 1, 15, 0, 0))).toBe(0);
    });

    it('converts hours and minutes', () => {
        expect(minutesOfDay(d(2024, 1, 15, 9, 30))).toBe(570);
    });

    it('is 1439 at 23:59', () => {
        expect(minutesOfDay(d(2024, 1, 15, 23, 59))).toBe(1439);
    });

    it('ignores seconds', () => {
        expect(minutesOfDay(d(2024, 1, 15, 10, 15, 45))).toBe(615);
    });
});

describe('atMinutes', () => {
    const day = d(2024, 5, 10, 15, 42); // time of day should be ignored

    it('returns local midnight for 0 minutes', () => {
        expect(atMinutes(day, 0)).toEqual(d(2024, 5, 10));
    });

    it('returns the given minutes after midnight', () => {
        expect(atMinutes(day, 90)).toEqual(d(2024, 5, 10, 1, 30));
    });

    it('returns the next midnight for 1440', () => {
        expect(atMinutes(day, MINUTES_PER_DAY)).toEqual(d(2024, 5, 11));
    });

    it('rolls over month ends at 1440', () => {
        expect(atMinutes(d(2024, 1, 31), MINUTES_PER_DAY)).toEqual(d(2024, 2, 1));
    });

    it('round-trips with minutesOfDay', () => {
        expect(minutesOfDay(atMinutes(day, 745))).toBe(745);
    });
});

describe('segmentForDay', () => {
    const day = d(2024, 1, 15);
    const segment = (startMin: number, endMin: number, continuesBefore = false, continuesAfter = false) => ({
        startMin,
        endMin,
        continuesBefore,
        continuesAfter,
    });

    it('returns the segment for an event fully inside the day', () => {
        expect(segmentForDay(d(2024, 1, 15, 10, 0), d(2024, 1, 15, 11, 30), day)).toEqual(segment(600, 690));
    });

    it('handles an event starting exactly at midnight', () => {
        expect(segmentForDay(d(2024, 1, 15, 0, 0), d(2024, 1, 15, 1, 0), day)).toEqual(segment(0, 60));
    });

    it('returns null for an event on the previous day', () => {
        expect(segmentForDay(d(2024, 1, 14, 10, 0), d(2024, 1, 14, 11, 0), day)).toBeNull();
    });

    it('returns null for an event on the next day', () => {
        expect(segmentForDay(d(2024, 1, 16, 10, 0), d(2024, 1, 16, 11, 0), day)).toBeNull();
    });

    it('returns null when the event ends exactly at the start of the day', () => {
        expect(segmentForDay(d(2024, 1, 14, 22, 0), d(2024, 1, 15, 0, 0), day)).toBeNull();
    });

    it('returns null when the event starts exactly at the end of the day', () => {
        expect(segmentForDay(d(2024, 1, 16, 0, 0), d(2024, 1, 16, 1, 0), day)).toBeNull();
    });

    it('clamps an event that started the previous day to 0 and marks it as continuing before', () => {
        expect(segmentForDay(d(2024, 1, 14, 22, 0), d(2024, 1, 15, 2, 0), day)).toEqual(segment(0, 120, true));
    });

    it('clamps an event that ends the next day to 1440 and marks it as continuing after', () => {
        expect(segmentForDay(d(2024, 1, 15, 22, 0), d(2024, 1, 16, 2, 0), day)).toEqual(
            segment(1320, 1440, false, true),
        );
    });

    it('treats an event ending exactly at the next midnight as ending at 1440, without continuing after', () => {
        expect(segmentForDay(d(2024, 1, 15, 22, 0), d(2024, 1, 16, 0, 0), day)).toEqual(segment(1320, 1440));
    });

    it('covers the whole day for an event spanning it entirely', () => {
        expect(segmentForDay(d(2024, 1, 14, 12, 0), d(2024, 1, 16, 12, 0), day)).toEqual(segment(0, 1440, true, true));
    });

    it('returns null for a zero-length event', () => {
        expect(segmentForDay(d(2024, 1, 15, 10, 0), d(2024, 1, 15, 10, 0), day)).toBeNull();
    });

    it('returns null when end is before start', () => {
        expect(segmentForDay(d(2024, 1, 15, 11, 0), d(2024, 1, 15, 10, 0), day)).toBeNull();
    });

    it('splits a multi-day event into per-day segments', () => {
        const start = d(2024, 1, 15, 20, 0);
        const end = d(2024, 1, 17, 8, 0);

        expect(segmentForDay(start, end, d(2024, 1, 15))).toEqual(segment(1200, 1440, false, true));
        expect(segmentForDay(start, end, d(2024, 1, 16))).toEqual(segment(0, 1440, true, true));
        expect(segmentForDay(start, end, d(2024, 1, 17))).toEqual(segment(0, 480, true));
        expect(segmentForDay(start, end, d(2024, 1, 18))).toBeNull();
    });

    it('ignores the time of day on the `day` argument', () => {
        expect(segmentForDay(d(2024, 1, 15, 10, 0), d(2024, 1, 15, 11, 0), d(2024, 1, 15, 18, 30))).toEqual(
            segment(600, 660),
        );
    });
});

describe('layoutOverlaps', () => {
    const ev = (id: string, startMin: number, endMin: number) => ({ id, startMin, endMin });
    const byId = <T extends { id: string }>(items: T[]) => Object.fromEntries(items.map((i) => [i.id, i]));

    it('returns an empty array for no items', () => {
        expect(layoutOverlaps([])).toEqual([]);
    });

    it('gives a single event one full-width column', () => {
        expect(layoutOverlaps([ev('a', 60, 120)])).toEqual([{ id: 'a', startMin: 60, endMin: 120, col: 0, cols: 1 }]);
    });

    it('keeps non-overlapping events in their own single-column clusters', () => {
        const out = byId(layoutOverlaps([ev('a', 0, 60), ev('b', 120, 180)]));
        expect(out.a).toMatchObject({ col: 0, cols: 1 });
        expect(out.b).toMatchObject({ col: 0, cols: 1 });
    });

    it('does not treat back-to-back events as overlapping', () => {
        const out = byId(layoutOverlaps([ev('a', 0, 60), ev('b', 60, 120)]));
        expect(out.a).toMatchObject({ col: 0, cols: 1 });
        expect(out.b).toMatchObject({ col: 0, cols: 1 });
    });

    it('splits two overlapping events into two columns', () => {
        const out = byId(layoutOverlaps([ev('a', 0, 60), ev('b', 30, 90)]));
        expect(out.a).toMatchObject({ col: 0, cols: 2 });
        expect(out.b).toMatchObject({ col: 1, cols: 2 });
    });

    it('puts three fully overlapping events in three columns', () => {
        const out = byId(layoutOverlaps([ev('a', 0, 60), ev('b', 0, 60), ev('c', 0, 60)]));
        const cols = Object.values(out)
            .map((o) => o.col)
            .sort();
        expect(cols).toEqual([0, 1, 2]);
        Object.values(out).forEach((o) => expect(o.cols).toBe(3));
    });

    it('reuses a freed column within the same cluster', () => {
        // a(0-60) and b(30-90) overlap; c(60-120) overlaps b but can reuse a's column
        const out = byId(layoutOverlaps([ev('a', 0, 60), ev('b', 30, 90), ev('c', 60, 120)]));
        expect(out.a).toMatchObject({ col: 0, cols: 2 });
        expect(out.b).toMatchObject({ col: 1, cols: 2 });
        expect(out.c).toMatchObject({ col: 0, cols: 2 });
    });

    it('lays out a long event with several short ones nested inside', () => {
        const out = byId(layoutOverlaps([ev('big', 0, 120), ev('s1', 10, 20), ev('s2', 30, 40)]));
        expect(out.big).toMatchObject({ col: 0, cols: 2 });
        expect(out.s1).toMatchObject({ col: 1, cols: 2 });
        expect(out.s2).toMatchObject({ col: 1, cols: 2 });
    });

    it('sorts by start, placing the longer event first on equal starts', () => {
        const result = layoutOverlaps([ev('short', 0, 30), ev('long', 0, 60)]);
        expect(result.map((r) => r.id)).toEqual(['long', 'short']);
        expect(result[0]).toMatchObject({ col: 0, cols: 2 });
        expect(result[1]).toMatchObject({ col: 1, cols: 2 });
    });

    it('handles unsorted input', () => {
        const out = byId(layoutOverlaps([ev('c', 120, 180), ev('b', 30, 90), ev('a', 0, 60)]));
        expect(out.a).toMatchObject({ col: 0, cols: 2 });
        expect(out.b).toMatchObject({ col: 1, cols: 2 });
        expect(out.c).toMatchObject({ col: 0, cols: 1 });
    });

    it('computes `cols` per cluster rather than globally', () => {
        const out = byId(
            layoutOverlaps([ev('a', 0, 60), ev('b', 30, 90), ev('c', 30, 90), ev('d', 200, 260), ev('e', 220, 280)]),
        );
        expect(out.a.cols).toBe(3);
        expect(out.b.cols).toBe(3);
        expect(out.c.cols).toBe(3);
        expect(out.d.cols).toBe(2);
        expect(out.e.cols).toBe(2);
    });

    it('preserves extra properties on items', () => {
        const [result] = layoutOverlaps([{ id: 'x', title: 'Lesson', startMin: 0, endMin: 30 }]);
        expect(result).toMatchObject({ id: 'x', title: 'Lesson' });
    });

    it('does not mutate the input array or its items', () => {
        const input = [ev('b', 30, 90), ev('a', 0, 60)];
        const snapshot = JSON.parse(JSON.stringify(input));
        layoutOverlaps(input);
        expect(input).toEqual(snapshot);
        expect(input[0]).not.toHaveProperty('col');
    });
});

describe('describeTimezone', () => {
    afterEach(() => {
        vi.restoreAllMocks();
    });

    /** Stubs Intl.DateTimeFormat so results don't depend on ICU data. */
    function mockIntl(opts: { short?: string; longOffset?: string }) {
        const formatToParts = vi.fn((_at?: Date) => [] as { type: string; value: string }[]);
        vi.spyOn(Intl, 'DateTimeFormat').mockImplementation(function (
            _locale?: unknown,
            options?: Intl.DateTimeFormatOptions,
        ) {
            return {
                formatToParts: (at?: Date) => {
                    formatToParts(at);
                    const value = options?.timeZoneName === 'short' ? opts.short : opts.longOffset;
                    return value === undefined ? [] : [{ type: 'timeZoneName', value }];
                },
            };
        } as any);
        return formatToParts;
    }

    it('describes the calendar timezone (UTC) by default, with the real Intl', () => {
        expect(describeTimezone(new Date('2024-07-01T12:00:00Z'))).toBe('UTC time · UTC+0');
    });

    it('shows the abbreviation and offset when an abbreviation exists', () => {
        mockIntl({ short: 'CEST', longOffset: 'GMT+02:00' });
        expect(describeTimezone(undefined, 'Europe/Paris')).toBe('Paris time · CEST (UTC+2)');
    });

    it('formats negative offsets', () => {
        mockIntl({ short: 'EDT', longOffset: 'GMT-04:00' });
        expect(describeTimezone(undefined, 'America/New_York')).toBe('New York time · EDT (UTC-4)');
    });

    it('replaces underscores and uses the last path segment as the city', () => {
        mockIntl({ short: 'GMT-3', longOffset: 'GMT-03:00' });
        expect(describeTimezone(undefined, 'America/Argentina/Buenos_Aires')).toBe('Buenos Aires time · UTC-3');
    });

    it('omits the abbreviation when it is just a GMT±N offset, and keeps minutes', () => {
        mockIntl({ short: 'GMT+5:30', longOffset: 'GMT+05:30' });
        expect(describeTimezone(undefined, 'Asia/Kolkata')).toBe('Kolkata time · UTC+5:30');
    });

    it('falls back to plain "UTC" when there is no offset (e.g. London in winter)', () => {
        mockIntl({ short: 'GMT', longOffset: 'GMT' });
        expect(describeTimezone(undefined, 'Europe/London')).toBe('London time · UTC');
    });

    it('handles the UTC zone itself', () => {
        mockIntl({ short: 'UTC', longOffset: 'GMT' });
        expect(describeTimezone(undefined, 'UTC')).toBe('UTC time · UTC');
    });

    it('degrades gracefully when Intl returns no timeZoneName parts', () => {
        mockIntl({});
        expect(describeTimezone(undefined, 'Europe/Paris')).toBe('Paris time · UTC');
    });

    it('formats the given date (so DST is evaluated at that moment)', () => {
        const formatToParts = mockIntl({ short: 'CET', longOffset: 'GMT+01:00' });
        const at = new Date('2024-01-15T12:00:00Z');
        describeTimezone(at, 'Europe/Paris');
        expect(formatToParts).toHaveBeenCalledWith(at);
    });
});
