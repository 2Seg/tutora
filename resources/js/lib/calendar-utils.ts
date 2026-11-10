import type { Availability, SlotSelection } from '@/types/calendar';

export const MINUTES_PER_DAY: number = 24 * 60;
export const SNAP_MINUTES = 10;
export const DEFAULT_SLOT_MINUTES = 50;
export const LESSON_DURATIONS = [25, 50, 80, 110] as const;

export const CALENDAR_TIME_ZONE = 'UTC';

export function toDate(value: string | Date): Date {
    return value instanceof Date ? value : new Date(value);
}

function timeZoneOffsetMs(instant: Date, timeZone: string): number {
    const parts = Object.fromEntries(
        new Intl.DateTimeFormat('en-US', {
            timeZone,
            hourCycle: 'h23',
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
            second: 'numeric',
        })
            .formatToParts(instant)
            .map(({ type, value }) => [type, Number(value)]),
    );
    const wallClockAsUtc = Date.UTC(parts.year, parts.month - 1, parts.day, parts.hour, parts.minute, parts.second);

    return wallClockAsUtc - Math.floor(instant.getTime() / 1000) * 1000;
}

export function toWallClock(instant: Date, timeZone: string): Date {
    return new Date(instant.getTime() + timeZoneOffsetMs(instant, timeZone));
}

export function fromWallClock(wallClock: Date, timeZone: string): Date {
    const approximate = new Date(wallClock.getTime() - timeZoneOffsetMs(wallClock, timeZone));
    return new Date(wallClock.getTime() - timeZoneOffsetMs(approximate, timeZone));
}

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

export const percentOfDay = (minutes: number): string => `${(minutes / MINUTES_PER_DAY) * 100}%`;

export function minutesOfDay(date: Date): number {
    return date.getUTCHours() * 60 + date.getUTCMinutes();
}

export function atMinutes(day: Date, minutes: number): Date {
    return new Date(Date.UTC(day.getUTCFullYear(), day.getUTCMonth(), day.getUTCDate(), 0, minutes));
}

export function nearestLessonDuration(minutes: number, max = Infinity): number {
    const fitting = LESSON_DURATIONS.filter((duration) => duration <= max);
    const candidates: number[] = fitting.length ? fitting : [LESSON_DURATIONS[0]];

    return candidates.reduce((best, duration) =>
        Math.abs(duration - minutes) < Math.abs(best - minutes) ? duration : best,
    );
}

export function lessonRange(startMin: number, minutes: number): MinuteRange {
    const duration = nearestLessonDuration(minutes);
    const start = Math.min(startMin, MINUTES_PER_DAY - duration);

    return { startMin: start, endMin: start + duration };
}

export function minutesToTime(minutes: number): string {
    const inDay = minutes % MINUTES_PER_DAY;
    return `${String(Math.floor(inDay / 60)).padStart(2, '0')}:${String(inDay % 60).padStart(2, '0')}`;
}

export function slotMinutes(slot: SlotSelection): MinuteRange {
    const startMin = minutesOfDay(slot.startsAt);
    return { startMin, endMin: startMin + (slot.endsAt.getTime() - slot.startsAt.getTime()) / 60_000 };
}

function onSameDay(slot: SlotSelection, { startMin, endMin }: MinuteRange): SlotSelection {
    return { startsAt: atMinutes(slot.startsAt, startMin), endsAt: atMinutes(slot.startsAt, endMin) };
}

export function fitLesson<T extends SlotSelection>(slot: T): T {
    const { startMin, endMin } = slotMinutes(slot);
    return { ...slot, ...onSameDay(slot, lessonRange(startMin, endMin - startMin)) };
}

export function withLessonStart(slot: SlotSelection, startMin: number): SlotSelection {
    const { startMin: start, endMin } = slotMinutes(slot);
    return onSameDay(slot, lessonRange(startMin, endMin - start));
}

export function withLessonDuration(slot: SlotSelection, minutes: number): SlotSelection {
    return onSameDay(slot, lessonRange(slotMinutes(slot).startMin, minutes));
}

export function withStart(slot: SlotSelection, startMin: number): SlotSelection {
    const { startMin: start, endMin } = slotMinutes(slot);
    const length = endMin - start;
    const newStart = Math.min(Math.max(startMin, 0), MINUTES_PER_DAY - length);
    return onSameDay(slot, { startMin: newStart, endMin: newStart + length });
}

export function withEnd(slot: SlotSelection, endMin: number): SlotSelection {
    const { startMin } = slotMinutes(slot);
    const end = endMin === 0 ? MINUTES_PER_DAY : endMin;
    return onSameDay(slot, { startMin, endMin: Math.min(Math.max(end, startMin + SNAP_MINUTES), MINUTES_PER_DAY) });
}

export interface DaySegment {
    startMin: number;
    endMin: number;
    continuesBefore: boolean;
    continuesAfter: boolean;
}

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

export function timeToMinutes(time: string): number {
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

export function describeTimezone(at: Date = new Date(), tz: string = CALENDAR_TIME_ZONE): string {
    const part = (timeZoneName: 'longGeneric' | 'longOffset') =>
        new Intl.DateTimeFormat('en-GB', { timeZone: tz, timeZoneName })
            .formatToParts(at)
            .find((p) => p.type === 'timeZoneName')?.value ?? '';

    const name = part('longGeneric');
    const match = /GMT([+-])(\d{2}):(\d{2})/.exec(part('longOffset'));
    const offset = match ? `UTC${match[1]}${Number(match[2])}${match[3] !== '00' ? `:${match[3]}` : ''}` : 'UTC';
    const hasName = name !== '' && !/^(GMT|UTC)/.test(name);

    return hasName ? `${name} (${offset})` : offset;
}
