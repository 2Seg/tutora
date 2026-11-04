import type { Availability } from '@/types/calendar';

export const MINUTES_PER_DAY: number = 24 * 60;
export const SNAP_MINUTES = 10;
export const DEFAULT_SLOT_MINUTES = 50;

/**
 * The calendar shows every date in UTC for now. The date helpers below rely on it
 * (they use the UTC getters), so switching to the user's timezone means changing them too.
 */
export const CALENDAR_TIME_ZONE = 'UTC';

export function toDate(value: string | Date): Date {
    return value instanceof Date ? value : new Date(value);
}

/** Monday-based week start, at UTC midnight. */
export function startOfWeek(date: Date, weekStartsOn: 0 | 1 = 1): Date {
    const diff = (date.getUTCDay() - weekStartsOn + 7) % 7;
    return new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate() - diff));
}

export function addDays(date: Date, days: number): Date {
    return new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate() + days));
}

export function isSameDay(a: Date, b: Date): boolean {
    return (
        a.getUTCFullYear() === b.getUTCFullYear() &&
        a.getUTCMonth() === b.getUTCMonth() &&
        a.getUTCDate() === b.getUTCDate()
    );
}

/** Vertical position of a minute of the day, as a CSS percentage of the day column. */
export const percentOfDay = (minutes: number): string => `${(minutes / MINUTES_PER_DAY) * 100}%`;

/** Wall-clock minutes since UTC midnight. */
export function minutesOfDay(date: Date): number {
    return date.getUTCHours() * 60 + date.getUTCMinutes();
}

/** UTC date at `minutes` after midnight of `day` (1440 gives the next midnight). */
export function atMinutes(day: Date, minutes: number): Date {
    return new Date(Date.UTC(day.getUTCFullYear(), day.getUTCMonth(), day.getUTCDate(), 0, minutes));
}

export interface DaySegment {
    startMin: number;
    endMin: number;
    /** The event started on a previous day. */
    continuesBefore: boolean;
    /** The event ends on a later day. */
    continuesAfter: boolean;
}

/** The part of [start, end) that falls on `day`, as wall-clock minutes, or null if none. */
export function segmentForDay(start: Date, end: Date, day: Date): DaySegment | null {
    const dayStart = atMinutes(day, 0).getTime();
    const dayEnd = atMinutes(day, MINUTES_PER_DAY).getTime();
    if (end.getTime() <= dayStart || start.getTime() >= dayEnd) return null;

    const startMin = start.getTime() <= dayStart ? 0 : minutesOfDay(start);
    const endMin = end.getTime() >= dayEnd ? MINUTES_PER_DAY : minutesOfDay(end);
    if (endMin <= startMin) return null;

    return {
        startMin,
        endMin,
        continuesBefore: start.getTime() < dayStart,
        continuesAfter: end.getTime() > dayEnd,
    };
}

function timeToMinutes(time: string): number {
    const [hours, minutes] = time.split(':').map(Number);
    return hours * 60 + minutes;
}

interface MinuteRange {
    startMin: number;
    endMin: number;
}

const isoWeekdayOf = (day: Date): number => day.getUTCDay() || 7;

function availableRanges(availabilities: Availability[], isoWeekday: number): MinuteRange[] {
    return availabilities
        .filter((availability) => availability.dayOfWeek === isoWeekday)
        .map(({ startTime, endTime }) => ({ startMin: timeToMinutes(startTime), endMin: timeToMinutes(endTime) }))
        .sort((a, b) => a.startMin - b.startMin);
}

/** The parts of the day outside `ranges`, which must be sorted by start. */
function gapsBetween(ranges: MinuteRange[]): MinuteRange[] {
    const gaps: MinuteRange[] = [];
    let cursor = 0;

    for (const { startMin, endMin } of ranges) {
        if (startMin > cursor) gaps.push({ startMin: cursor, endMin: startMin });
        cursor = Math.max(cursor, endMin);
    }
    if (cursor < MINUTES_PER_DAY) gaps.push({ startMin: cursor, endMin: MINUTES_PER_DAY });

    return gaps;
}

function intersectRanges(first: MinuteRange[], second: MinuteRange[]): MinuteRange[] {
    return first.flatMap((a) =>
        second.flatMap((b) => {
            const startMin = Math.max(a.startMin, b.startMin);
            const endMin = Math.min(a.endMin, b.endMin);
            return startMin < endMin ? [{ startMin, endMin }] : [];
        }),
    );
}

export function unavailableSlotsForDay(availabilities: Availability[], day: Date): MinuteRange[] {
    return gapsBetween(availableRanges(availabilities, isoWeekdayOf(day)));
}

export function availabilitySideLinesForDay(availabilities: Availability[], day: Date) {
    const isoWeekday = isoWeekdayOf(day);
    const available = gapsBetween(unavailableSlotsForDay(availabilities, day));
    const uncoveredBy = (neighbourWeekday: number) =>
        intersectRanges(available, gapsBetween(availableRanges(availabilities, neighbourWeekday)));

    return { left: uncoveredBy(isoWeekday - 1), right: uncoveredBy(isoWeekday + 1) };
}

export function layoutOverlaps<T extends { startMin: number; endMin: number }>(
    items: T[],
): (T & { col: number; cols: number })[] {
    const sorted = [...items].sort((a, b) => a.startMin - b.startMin || b.endMin - a.endMin);
    const result: (T & { col: number; cols: number })[] = [];

    let cluster: T[] = [];
    let clusterEnd = -1;

    const flush = () => {
        const columnEnds: number[] = [];
        const placed = cluster.map((item) => {
            let col = columnEnds.findIndex((end) => end <= item.startMin);
            if (col === -1) col = columnEnds.length;
            columnEnds[col] = item.endMin;
            return { item, col };
        });
        for (const { item, col } of placed) result.push({ ...item, col, cols: columnEnds.length });
        cluster = [];
        clusterEnd = -1;
    };

    for (const item of sorted) {
        if (cluster.length && item.startMin >= clusterEnd) flush();
        cluster.push(item);
        clusterEnd = Math.max(clusterEnd, item.endMin);
    }
    if (cluster.length) flush();

    return result;
}

/** "Paris time · CEST (UTC+2)" for the given timezone (the calendar's by default). */
export function describeTimezone(at: Date = new Date(), tz: string = CALENDAR_TIME_ZONE): string {
    const city = tz.split('/').pop()!.replace(/_/g, ' ');

    const part = (timeZoneName: 'short' | 'longOffset') =>
        new Intl.DateTimeFormat('en-GB', { timeZone: tz, timeZoneName })
            .formatToParts(at)
            .find((p) => p.type === 'timeZoneName')?.value ?? '';

    const abbreviation = part('short');
    const match = /GMT([+-])(\d{2}):(\d{2})/.exec(part('longOffset'));
    const offset = match ? `UTC${match[1]}${Number(match[2])}${match[3] !== '00' ? `:${match[3]}` : ''}` : 'UTC';
    const hasAbbreviation = abbreviation !== '' && !/^(GMT|UTC)/.test(abbreviation);

    return `${city} time · ${hasAbbreviation ? `${abbreviation} (${offset})` : offset}`;
}
