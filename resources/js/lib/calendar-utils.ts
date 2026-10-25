export const MINUTES_PER_DAY: number = 24 * 60;

export function toDate(value: string | Date): Date {
    return value instanceof Date ? value : new Date(value);
}

/** Monday-based week start, at local midnight. */
export function startOfWeek(date: Date, weekStartsOn: 0 | 1 = 1): Date {
    const diff = (date.getDay() - weekStartsOn + 7) % 7;
    return new Date(date.getFullYear(), date.getMonth(), date.getDate() - diff);
}

export function addDays(date: Date, days: number): Date {
    return new Date(date.getFullYear(), date.getMonth(), date.getDate() + days);
}

export function isSameDay(a: Date, b: Date): boolean {
    return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
}

/** Vertical position of a minute of the day, as a CSS percentage of the day column. */
export const percentOfDay = (minutes: number): string => `${(minutes / MINUTES_PER_DAY) * 100}%`;

/** Wall-clock minutes since local midnight. */
export function minutesOfDay(date: Date): number {
    return date.getHours() * 60 + date.getMinutes();
}

/** Local date at `minutes` after midnight of `day` (1440 gives the next midnight). */
export function atMinutes(day: Date, minutes: number): Date {
    return new Date(day.getFullYear(), day.getMonth(), day.getDate(), 0, minutes);
}

/** The part of [start, end) that falls on `day`, as wall-clock minutes, or null if none. */
export function segmentForDay(start: Date, end: Date, day: Date): { startMin: number; endMin: number } | null {
    const dayStart = atMinutes(day, 0).getTime();
    const dayEnd = atMinutes(day, MINUTES_PER_DAY).getTime();
    if (end.getTime() <= dayStart || start.getTime() >= dayEnd) return null;

    const startMin = start.getTime() <= dayStart ? 0 : minutesOfDay(start);
    const endMin = end.getTime() >= dayEnd ? MINUTES_PER_DAY : minutesOfDay(end);
    return endMin > startMin ? { startMin, endMin } : null;
}

/**
 * Google Calendar style overlap layout: events that overlap are split into
 * side-by-side columns, each cluster of overlapping events on its own.
 */
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

/** "Paris time · CEST (UTC+2)" for the browser's timezone. */
export function describeTimezone(at: Date = new Date()): string {
    const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
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
