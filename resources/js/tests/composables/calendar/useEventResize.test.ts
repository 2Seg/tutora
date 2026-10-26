// @vitest-environment jsdom
import { describe, expect, it, vi } from 'vitest';
import { ref } from 'vue';

import { useEventResize } from '@/composables/calendar/useEventResize';
import type { CalendarEvent } from '@/types/calendar';

// Older jsdom versions have no PointerEvent.
const PointerEventImpl: typeof PointerEvent =
    globalThis.PointerEvent ??
    (class extends MouseEvent {
        pointerId: number;

        constructor(type: string, init: PointerEventInit = {}) {
            super(type, init);
            this.pointerId = init.pointerId ?? 0;
        }
    } as unknown as typeof PointerEvent);

const DAY = new Date(2026, 9, 14); // Wednesday, local midnight
const HOUR_PX = 40;
const COLUMN_HEIGHT = 24 * HOUR_PX;

const yOf = (minutes: number) => (minutes / 60) * HOUR_PX;
const at = (hours: number, minutes = 0) => new Date(2026, 9, 14, hours, minutes);
const minutes = (hours: number, mins = 0) => hours * 60 + mins;

const lesson = (start: Date, end: Date): CalendarEvent => ({
    id: 'draft-1',
    type: 'lesson',
    status: 'draft',
    startTime: start,
    endTime: end,
});

function setup(event: CalendarEvent = lesson(at(10), at(11)), { hasColumn = true } = {}) {
    // The column is 24 rows of 40px, starting at the top of the viewport.
    const column = document.createElement('div');
    column.getBoundingClientRect = () => ({
        x: 0,
        y: 0,
        top: 0,
        left: 0,
        right: 100,
        bottom: COLUMN_HEIGHT,
        width: 100,
        height: COLUMN_HEIGHT,
        toJSON: () => ({}),
    });

    const handle = document.createElement('div');
    handle.setPointerCapture = vi.fn();

    const onCommit = vi.fn();
    const { resizing, onResizeStart } = useEventResize(ref(hasColumn ? column : null), () => DAY, onCommit);

    // Same wiring as in the template: the handle starts the resize.
    handle.addEventListener('pointerdown', (e) => onResizeStart(event, e as PointerEvent));

    const fire = (type: string, init: PointerEventInit = {}) =>
        handle.dispatchEvent(new PointerEventImpl(type, { pointerId: 1, button: 0, ...init }));

    return {
        event,
        handle,
        resizing,
        onCommit,
        press: (button = 0) => fire('pointerdown', { button }),
        moveTo: (minute: number) => fire('pointermove', { clientY: yOf(minute) }),
        release: () => fire('pointerup'),
        cancel: () => fire('pointercancel'),
    };
}

describe('useEventResize', () => {
    describe('starting', () => {
        it('captures the pointer on the handle', () => {
            const { handle, press } = setup();

            press();

            expect(handle.setPointerCapture).toHaveBeenCalledWith(1);
        });

        it('starts from the current end of the event', () => {
            const { resizing, press } = setup();

            press();

            expect(resizing.value).toEqual({ id: 'draft-1', endMin: minutes(11) });
        });

        it('treats an end at the next midnight as the end of the day', () => {
            const { resizing, press } = setup(lesson(at(22), new Date(2026, 9, 15)));

            press();

            expect(resizing.value?.endMin).toBe(minutes(24));
        });

        it('ignores buttons other than the main one', () => {
            const { handle, resizing, press } = setup();

            press(2);

            expect(resizing.value).toBeNull();
            expect(handle.setPointerCapture).not.toHaveBeenCalled();
        });

        it('does nothing when the column is not mounted', () => {
            const { resizing, press } = setup(undefined, { hasColumn: false });

            press();

            expect(resizing.value).toBeNull();
        });
    });

    describe('moving', () => {
        it.each([
            ['rounds down below the middle of a step', minutes(12, 7), minutes(12)],
            ['rounds up above the middle of a step', minutes(12, 8), minutes(12, 15)],
            ['rounds down when shrinking', minutes(11, 22), minutes(11, 15)],
            ['rounds up when shrinking', minutes(11, 23), minutes(11, 30)],
            ['lands exactly on a line', minutes(14), minutes(14)],
        ])('snaps to 15 minutes: %s', (_name, pointerMinute, expectedEnd) => {
            const { resizing, press, moveTo } = setup();

            press();
            moveTo(pointerMinute);

            expect(resizing.value?.endMin).toBe(expectedEnd);
        });

        it('never goes above start + one step', () => {
            const { resizing, press, moveTo } = setup(); // starts at 10:00

            press();
            moveTo(minutes(10, 5));
            expect(resizing.value?.endMin).toBe(minutes(10, 15));

            moveTo(0);
            expect(resizing.value?.endMin).toBe(minutes(10, 15));
        });

        it('stops at midnight', () => {
            const { resizing, press, moveTo } = setup();

            press();
            moveTo(minutes(30)); // below the bottom of the column

            expect(resizing.value?.endMin).toBe(minutes(24));
        });

        it('does not commit while moving', () => {
            const { onCommit, press, moveTo } = setup();

            press();
            moveTo(minutes(13));

            expect(onCommit).not.toHaveBeenCalled();
        });
    });

    describe('releasing', () => {
        it('commits the new end on the displayed day and clears the state', () => {
            const { event, resizing, onCommit, press, moveTo, release } = setup();

            press();
            moveTo(minutes(12));
            release();

            expect(onCommit).toHaveBeenCalledTimes(1);
            expect(onCommit).toHaveBeenCalledWith(event, at(12));
            expect(resizing.value).toBeNull();
        });

        it('commits the end at midnight as the start of the next day', () => {
            const { onCommit, press, moveTo, release } = setup();

            press();
            moveTo(minutes(30));
            release();

            expect(onCommit).toHaveBeenCalledWith(expect.anything(), new Date(2026, 9, 15));
        });

        it('commits the unchanged end when the pointer did not move', () => {
            const { onCommit, press, release } = setup();

            press();
            release();

            expect(onCommit).toHaveBeenCalledWith(expect.anything(), at(11));
        });

        it('ignores moves and releases that come after it', () => {
            const { resizing, onCommit, press, moveTo, release } = setup();

            press();
            release();
            moveTo(minutes(15));
            release();

            expect(onCommit).toHaveBeenCalledTimes(1);
            expect(resizing.value).toBeNull();
        });
    });

    describe('cancelling', () => {
        it('clears the state without committing', () => {
            const { resizing, onCommit, press, moveTo, cancel } = setup();

            press();
            moveTo(minutes(13));
            cancel();

            expect(onCommit).not.toHaveBeenCalled();
            expect(resizing.value).toBeNull();
        });

        it('stops following the pointer afterwards', () => {
            const { resizing, press, moveTo, cancel } = setup();

            press();
            cancel();
            moveTo(minutes(13));

            expect(resizing.value).toBeNull();
        });
    });
});
