import { afterEach, describe, expect, it, vi } from 'vitest';
import {
    addDays,
    atMinutes,
    availabilitySideLinesForDay,
    describeTimezone,
    fitLesson,
    isSameDay,
    layoutOverlaps,
    lessonRange,
    MINUTES_PER_DAY,
    minutesOfDay,
    minutesToTime,
    nearestLessonDuration,
    percentOfDay,
    segmentForDay,
    slotMinutes,
    startOfWeek,
    timeToMinutes,
    toDate,
    unavailableSlotsForDay,
    withEnd,
    withLessonDuration,
    withLessonStart,
    withStart,
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

describe('nearestLessonDuration', () => {
    it.each([
        [25, 25],
        [50, 50],
        [80, 80],
        [110, 110],
        [10, 25],
        [40, 50],
        [70, 80],
        [100, 110],
        [300, 110],
    ])('maps %s minutes to %s', (minutes, expected) => {
        expect(nearestLessonDuration(minutes)).toBe(expected);
    });

    it('picks the shorter duration on a tie', () => {
        expect(nearestLessonDuration(65)).toBe(50);
    });

    it('only considers durations no longer than max', () => {
        expect(nearestLessonDuration(110, 90)).toBe(80);
    });

    it('falls back to 25 minutes when none fits under max', () => {
        expect(nearestLessonDuration(50, 10)).toBe(25);
    });
});

describe('lessonRange', () => {
    it('starts at the given minute with the nearest lesson duration', () => {
        expect(lessonRange(600, 90)).toEqual({ startMin: 600, endMin: 680 });
    });

    it('moves the start earlier so the lesson ends by midnight', () => {
        expect(lessonRange(1400, 50)).toEqual({ startMin: 1390, endMin: 1440 });
    });
});

describe('timeToMinutes / minutesToTime', () => {
    it('converts both ways', () => {
        expect(timeToMinutes('09:05')).toBe(545);
        expect(minutesToTime(545)).toBe('09:05');
    });

    it('shows the next midnight as 00:00', () => {
        expect(minutesToTime(MINUTES_PER_DAY)).toBe('00:00');
    });
});

describe('slotMinutes', () => {
    it('gives the start and end in minutes of the day', () => {
        expect(slotMinutes({ startsAt: d(2024, 5, 10, 9), endsAt: d(2024, 5, 10, 9, 50) })).toEqual({
            startMin: 540,
            endMin: 590,
        });
    });

    it('gives 1440 for an end at the next midnight', () => {
        expect(slotMinutes({ startsAt: d(2024, 5, 10, 23), endsAt: d(2024, 5, 11) }).endMin).toBe(MINUTES_PER_DAY);
    });
});

describe('time edits', () => {
    const slot = (h1: number, m1: number, h2: number, m2: number) => ({
        startsAt: d(2024, 5, 10, h1, m1),
        endsAt: d(2024, 5, 10, h2, m2),
    });

    describe('withLessonStart', () => {
        it('moves the lesson and keeps its duration', () => {
            expect(withLessonStart(slot(9, 0, 9, 50), 14 * 60 + 30)).toEqual(slot(14, 30, 15, 20));
        });

        it('moves the start earlier so the lesson ends by midnight', () => {
            expect(withLessonStart(slot(9, 0, 9, 50), 23 * 60 + 30)).toEqual({
                startsAt: d(2024, 5, 10, 23, 10),
                endsAt: d(2024, 5, 11),
            });
        });
    });

    describe('withLessonDuration', () => {
        it('keeps the start and changes the duration', () => {
            expect(withLessonDuration(slot(9, 0, 9, 50), 80)).toEqual(slot(9, 0, 10, 20));
        });

        it('moves the start earlier when the new duration would run past midnight', () => {
            expect(withLessonDuration(slot(23, 0, 23, 25), 110)).toEqual({
                startsAt: d(2024, 5, 10, 22, 10),
                endsAt: d(2024, 5, 11),
            });
        });
    });

    describe('withStart', () => {
        it('moves the slot and keeps its length', () => {
            expect(withStart(slot(9, 0, 12, 15), 13 * 60)).toEqual(slot(13, 0, 16, 15));
        });

        it('keeps the slot within the day', () => {
            expect(withStart(slot(9, 0, 12, 0), 22 * 60)).toEqual({
                startsAt: d(2024, 5, 10, 21),
                endsAt: d(2024, 5, 11),
            });
        });
    });

    describe('withEnd', () => {
        it('sets the end and keeps the start', () => {
            expect(withEnd(slot(9, 0, 10, 0), 12 * 60 + 5)).toEqual(slot(9, 0, 12, 5));
        });

        it('treats 00:00 as midnight', () => {
            expect(withEnd(slot(22, 0, 23, 0), 0)).toEqual({ startsAt: d(2024, 5, 10, 22), endsAt: d(2024, 5, 11) });
        });

        it('ends 10 minutes after the start when given an end before it', () => {
            expect(withEnd(slot(9, 0, 10, 0), 8 * 60)).toEqual(slot(9, 0, 9, 10));
        });
    });
});

describe('fitLesson', () => {
    it('snaps the length and keeps the other fields', () => {
        const slot = { type: 'time_off', startsAt: d(2024, 5, 10, 10), endsAt: d(2024, 5, 10, 10, 30) };

        expect(fitLesson(slot)).toEqual({
            type: 'time_off',
            startsAt: d(2024, 5, 10, 10),
            endsAt: d(2024, 5, 10, 10, 25),
        });
    });

    it('caps long slots at 1h50', () => {
        const slot = { startsAt: d(2024, 5, 10, 9), endsAt: d(2024, 5, 10, 12) };

        expect(fitLesson(slot)).toEqual({ startsAt: d(2024, 5, 10, 9), endsAt: d(2024, 5, 10, 10, 50) });
    });

    it('moves the start earlier when the lesson would run past midnight', () => {
        const slot = { startsAt: d(2024, 5, 10, 23, 50), endsAt: d(2024, 5, 11) };

        expect(fitLesson(slot)).toEqual({ startsAt: d(2024, 5, 10, 23, 35), endsAt: d(2024, 5, 11) });
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

describe('unavailableSlotsForDay', () => {
    const availability = (id: number, dayOfWeek: number, startTime = '08:00', endTime = '12:00') => ({
        id,
        dayOfWeek,
        startTime,
        endTime,
    });
    const wholeDay = [{ startMin: 0, endMin: MINUTES_PER_DAY }];

    it('returns the time before and after an availability', () => {
        const slots = unavailableSlotsForDay([availability(1, 1, '08:30', '12:15')], d(2024, 1, 15));

        expect(slots).toEqual([
            { startMin: 0, endMin: 510 },
            { startMin: 735, endMin: MINUTES_PER_DAY },
        ]);
    });

    it('returns the gap between two availabilities, whatever their order', () => {
        const availabilities = [availability(1, 1, '14:00', '18:00'), availability(2, 1, '08:00', '12:00')];

        expect(unavailableSlotsForDay(availabilities, d(2024, 1, 15))).toEqual([
            { startMin: 0, endMin: 480 },
            { startMin: 720, endMin: 840 },
            { startMin: 1080, endMin: MINUTES_PER_DAY },
        ]);
    });

    it('merges overlapping availabilities', () => {
        const availabilities = [availability(1, 1, '08:00', '12:00'), availability(2, 1, '10:00', '11:00')];

        expect(unavailableSlotsForDay(availabilities, d(2024, 1, 15))).toEqual([
            { startMin: 0, endMin: 480 },
            { startMin: 720, endMin: MINUTES_PER_DAY },
        ]);
    });

    it('returns nothing when the whole day is available', () => {
        const slots = unavailableSlotsForDay([availability(1, 1, '00:00', '24:00')], d(2024, 1, 15));

        expect(slots).toEqual([]);
    });

    it('returns the whole day when availabilities are on other days', () => {
        expect(unavailableSlotsForDay([availability(1, 2)], d(2024, 1, 15))).toEqual(wholeDay);
    });

    it('returns the whole day when there are no availabilities', () => {
        expect(unavailableSlotsForDay([], d(2024, 1, 15))).toEqual(wholeDay);
    });

    it('matches Sunday as day 7', () => {
        const slots = unavailableSlotsForDay([availability(1, 7)], d(2024, 1, 21));

        expect(slots).toHaveLength(2);
    });

    it('accepts times without seconds', () => {
        const slots = unavailableSlotsForDay([availability(1, 1, '08:00', '12:00')], d(2024, 1, 15));

        expect(slots).toEqual([
            { startMin: 0, endMin: 480 },
            { startMin: 720, endMin: MINUTES_PER_DAY },
        ]);
    });
});

describe('availabilitySideLinesForDay', () => {
    const availability = (dayOfWeek: number, startTime = '08:00', endTime = '12:00') => ({
        id: dayOfWeek,
        dayOfWeek,
        startTime,
        endTime,
    });
    const tuesday = d(2024, 1, 16);
    const morning = { startMin: 480, endMin: 720 };

    it('draws both sides when the neighbouring days are not available', () => {
        expect(availabilitySideLinesForDay([availability(2)], tuesday)).toEqual({
            left: [morning],
            right: [morning],
        });
    });

    it('links days available during the same hours', () => {
        const availabilities = [availability(1), availability(2), availability(3)];

        expect(availabilitySideLinesForDay(availabilities, tuesday)).toEqual({ left: [], right: [] });
    });

    it('only draws the hours the neighbouring day does not cover', () => {
        const availabilities = [availability(2), availability(3, '10:00', '14:00')];

        expect(availabilitySideLinesForDay(availabilities, tuesday).right).toEqual([{ startMin: 480, endMin: 600 }]);
    });

    it('draws both sides when the neighbouring day is available at other hours', () => {
        const availabilities = [availability(2), availability(3, '14:00', '18:00')];

        expect(availabilitySideLinesForDay(availabilities, tuesday).right).toEqual([morning]);
    });

    it('closes Monday on the left even when Sunday is available', () => {
        const availabilities = [availability(1), availability(7)];

        expect(availabilitySideLinesForDay(availabilities, d(2024, 1, 15)).left).toEqual([morning]);
    });

    it('closes Sunday on the right even when Monday is available', () => {
        const availabilities = [availability(1), availability(7)];

        expect(availabilitySideLinesForDay(availabilities, d(2024, 1, 21)).right).toEqual([morning]);
    });

    it('returns nothing for a day without availabilities', () => {
        expect(availabilitySideLinesForDay([availability(3)], tuesday)).toEqual({ left: [], right: [] });
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
