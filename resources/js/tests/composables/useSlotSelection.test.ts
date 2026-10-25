import { beforeEach, describe, expect, it, Mock, vi } from 'vitest';
import { percentOfDay } from '@/lib/calendar-utils';
import { useSlotSelection } from '@/composables/useSlotSelection';
import { SlotSelection } from '@/types/calendar';

const DAY = new Date(2024, 0, 15);
const TOP = 100; // column's top offset in the viewport
const HEIGHT = 1440; // 1px === 1 minute, which keeps the maths readable

const at = (minutes: number, day = DAY) => new Date(day.getFullYear(), day.getMonth(), day.getDate(), 0, minutes);

/**
 * clientY that lands 5 minutes into the bucket starting at `minute`.
 * (Staying mid-bucket avoids float noise from `y / height * 1440` on exact snap boundaries.)
 */
const yFor = (minute: number) => TOP + minute + 5;

function makeColumn(top = TOP, height = HEIGHT) {
    return {
        getBoundingClientRect: () => ({ top, height }),
        setPointerCapture: vi.fn(),
    } as unknown as HTMLElement & { setPointerCapture: ReturnType<typeof vi.fn> };
}

function pointerEvent(
    column: HTMLElement,
    clientY: number,
    overrides: { button?: number; pointerId?: number; target?: unknown } = {},
) {
    return {
        button: 0,
        pointerId: 1,
        clientY,
        currentTarget: column,
        target: { closest: vi.fn(() => null) },
        ...overrides,
    } as unknown as PointerEvent;
}

describe('useSlotSelection', () => {
    let onSelect: Mock<(selection: SlotSelection) => void>;
    let column: ReturnType<typeof makeColumn>;

    beforeEach(() => {
        onSelect = vi.fn<(selection: SlotSelection) => void>();
        column = makeColumn();
    });

    const setup = (day: () => Date = () => DAY) => useSlotSelection(day, onSelect);

    describe('initial state', () => {
        it('has no selection preview', () => {
            expect(setup().selectionStyle.value).toBeNull();
        });
    });

    describe('pointer down', () => {
        it('starts a selection and captures the pointer', () => {
            const { onPointerDown, selectionStyle } = setup();

            onPointerDown(pointerEvent(column, yFor(600), { pointerId: 7 }));

            expect(column.setPointerCapture).toHaveBeenCalledWith(7);
            expect(selectionStyle.value).toEqual({
                top: percentOfDay(600),
                height: percentOfDay(15),
            });
        });

        it('ignores non-primary buttons', () => {
            const { onPointerDown, selectionStyle } = setup();

            onPointerDown(pointerEvent(column, yFor(600), { button: 2 }));

            expect(column.setPointerCapture).not.toHaveBeenCalled();
            expect(selectionStyle.value).toBeNull();
        });

        it('ignores presses that start on an existing event', () => {
            const { onPointerDown, selectionStyle } = setup();
            const closest = vi.fn((selector: string) => (selector === '[data-event]' ? {} : null));

            onPointerDown(pointerEvent(column, yFor(600), { target: { closest } }));

            expect(closest).toHaveBeenCalledWith('[data-event]');
            expect(column.setPointerCapture).not.toHaveBeenCalled();
            expect(selectionStyle.value).toBeNull();
        });
    });

    describe('snapping and clamping', () => {
        it.each([
            [0, 0],
            [14.9, 0],
            [614, 600],
            [629, 615],
            [1439, 1425],
        ])('snaps %s minutes down to %s', (offset, expectedStart) => {
            const { onPointerDown, selectionStyle } = setup();

            onPointerDown(pointerEvent(column, TOP + offset));

            expect(selectionStyle.value?.top).toBe(percentOfDay(expectedStart));
        });

        it('clamps pointers above the column to minute 0', () => {
            const { onPointerDown, selectionStyle } = setup();

            onPointerDown(pointerEvent(column, TOP - 200));

            expect(selectionStyle.value?.top).toBe(percentOfDay(0));
        });

        it('clamps pointers below the column to the last slot of the day', () => {
            const { onPointerDown, selectionStyle } = setup();

            onPointerDown(pointerEvent(column, TOP + 2000));

            expect(selectionStyle.value?.top).toBe(percentOfDay(1440 - 15));
        });

        it('scales with the column height', () => {
            const halfHeight = makeColumn(TOP, 720); // 1px === 2 minutes
            const { onPointerDown, selectionStyle } = setup();

            onPointerDown(pointerEvent(halfHeight, TOP + 302.5)); // ~605 minutes

            expect(selectionStyle.value?.top).toBe(percentOfDay(600));
        });
    });

    describe('click (press and release on the same slot)', () => {
        it('selects a default 50 minute slot', () => {
            const { onPointerDown, onPointerUp } = setup();

            onPointerDown(pointerEvent(column, yFor(600)));
            onPointerUp();

            expect(onSelect).toHaveBeenCalledTimes(1);
            expect(onSelect).toHaveBeenCalledWith({ startTime: at(600), endTime: at(650) });
        });

        it('treats movement within the same 15 minute slot as a click', () => {
            const { onPointerDown, onPointerMove, onPointerUp } = setup();

            onPointerDown(pointerEvent(column, TOP + 601));
            onPointerMove(pointerEvent(column, TOP + 612));
            onPointerUp();

            expect(onSelect).toHaveBeenCalledWith({ startTime: at(600), endTime: at(650) });
        });

        it('caps the default slot at the end of the day', () => {
            const { onPointerDown, onPointerUp } = setup();

            onPointerDown(pointerEvent(column, yFor(1425)));
            onPointerUp();

            expect(onSelect).toHaveBeenCalledWith({ startTime: at(1425), endTime: at(1440) });
        });

        it('selects midnight to 00:50 when clicking the top of the column', () => {
            const { onPointerDown, onPointerUp } = setup();

            onPointerDown(pointerEvent(column, TOP));
            onPointerUp();

            expect(onSelect).toHaveBeenCalledWith({ startTime: at(0), endTime: at(50) });
        });
    });

    describe('drag', () => {
        it('selects from the anchor to the end of the slot under the pointer (downwards)', () => {
            const { onPointerDown, onPointerMove, onPointerUp } = setup();

            onPointerDown(pointerEvent(column, yFor(600)));
            onPointerMove(pointerEvent(column, yFor(690)));
            onPointerUp();

            expect(onSelect).toHaveBeenCalledWith({ startTime: at(600), endTime: at(705) });
        });

        it('produces the same range when dragging upwards', () => {
            const { onPointerDown, onPointerMove, onPointerUp } = setup();

            onPointerDown(pointerEvent(column, yFor(690)));
            onPointerMove(pointerEvent(column, yFor(600)));
            onPointerUp();

            expect(onSelect).toHaveBeenCalledWith({ startTime: at(600), endTime: at(705) });
        });

        it('uses the latest pointer position', () => {
            const { onPointerDown, onPointerMove, onPointerUp } = setup();

            onPointerDown(pointerEvent(column, yFor(600)));
            onPointerMove(pointerEvent(column, yFor(900)));
            onPointerMove(pointerEvent(column, yFor(660)));
            onPointerUp();

            expect(onSelect).toHaveBeenCalledWith({ startTime: at(600), endTime: at(675) });
        });

        it('selects a single extra slot when dragging by one slot', () => {
            const { onPointerDown, onPointerMove, onPointerUp } = setup();

            onPointerDown(pointerEvent(column, yFor(600)));
            onPointerMove(pointerEvent(column, yFor(615)));
            onPointerUp();

            expect(onSelect).toHaveBeenCalledWith({ startTime: at(600), endTime: at(630) });
        });

        it('ends exactly at midnight when dragged past the bottom of the column', () => {
            const { onPointerDown, onPointerMove, onPointerUp } = setup();

            onPointerDown(pointerEvent(column, yFor(1290)));
            onPointerMove(pointerEvent(column, TOP + 5000));
            onPointerUp();

            expect(onSelect).toHaveBeenCalledWith({ startTime: at(1290), endTime: at(1440) });
        });

        it('starts at midnight when dragged past the top of the column', () => {
            const { onPointerDown, onPointerMove, onPointerUp } = setup();

            onPointerDown(pointerEvent(column, yFor(120)));
            onPointerMove(pointerEvent(column, TOP - 500));
            onPointerUp();

            expect(onSelect).toHaveBeenCalledWith({ startTime: at(0), endTime: at(135) });
        });
    });

    describe('selectionStyle', () => {
        it('updates as the pointer moves', () => {
            const { onPointerDown, onPointerMove, selectionStyle } = setup();

            onPointerDown(pointerEvent(column, yFor(600)));
            onPointerMove(pointerEvent(column, yFor(690)));

            expect(selectionStyle.value).toEqual({
                top: percentOfDay(600),
                height: percentOfDay(105),
            });
        });

        it('anchors the top at the earlier slot when dragging upwards', () => {
            const { onPointerDown, onPointerMove, selectionStyle } = setup();

            onPointerDown(pointerEvent(column, yFor(690)));
            onPointerMove(pointerEvent(column, yFor(600)));

            expect(selectionStyle.value).toEqual({
                top: percentOfDay(600),
                height: percentOfDay(105),
            });
        });

        it('is null again after the selection completes', () => {
            const { onPointerDown, onPointerUp, selectionStyle } = setup();

            onPointerDown(pointerEvent(column, yFor(600)));
            onPointerUp();

            expect(selectionStyle.value).toBeNull();
        });
    });

    describe('without an active drag', () => {
        it('pointer move is a no-op', () => {
            const { onPointerMove, selectionStyle } = setup();

            expect(() => onPointerMove(pointerEvent(column, yFor(600)))).not.toThrow();
            expect(selectionStyle.value).toBeNull();
        });

        it('pointer up does not call onSelect', () => {
            const { onPointerUp } = setup();

            onPointerUp();

            expect(onSelect).not.toHaveBeenCalled();
        });

        it('a second pointer up does not select again', () => {
            const { onPointerDown, onPointerUp } = setup();

            onPointerDown(pointerEvent(column, yFor(600)));
            onPointerUp();
            onPointerUp();

            expect(onSelect).toHaveBeenCalledTimes(1);
        });
    });

    describe('pointer cancel', () => {
        it('discards the selection without calling onSelect', () => {
            const { onPointerDown, onPointerCancel, onPointerUp, selectionStyle } = setup();

            onPointerDown(pointerEvent(column, yFor(600)));
            onPointerCancel();

            expect(selectionStyle.value).toBeNull();

            onPointerUp();
            expect(onSelect).not.toHaveBeenCalled();
        });

        it('is safe to call with no active drag', () => {
            const { onPointerCancel } = setup();

            expect(() => onPointerCancel()).not.toThrow();
        });

        it('allows a fresh selection afterwards', () => {
            const { onPointerDown, onPointerCancel, onPointerUp } = setup();

            onPointerDown(pointerEvent(column, yFor(600)));
            onPointerCancel();
            onPointerDown(pointerEvent(column, yFor(300)));
            onPointerUp();

            expect(onSelect).toHaveBeenCalledTimes(1);
            expect(onSelect).toHaveBeenCalledWith({ startTime: at(300), endTime: at(350) });
        });
    });

    describe('day getter', () => {
        it('is read when the selection completes, not when the composable is created', () => {
            let current = new Date(2024, 0, 15);
            const { onPointerDown, onPointerUp } = setup(() => current);

            onPointerDown(pointerEvent(column, yFor(600)));
            current = new Date(2024, 0, 16);
            onPointerUp();

            expect(onSelect).toHaveBeenCalledWith({
                startTime: at(600, new Date(2024, 0, 16)),
                endTime: at(650, new Date(2024, 0, 16)),
            });
        });

        it('yields the next midnight as end time when the slot reaches the end of the day', () => {
            const { onPointerDown, onPointerUp } = setup();

            onPointerDown(pointerEvent(column, yFor(1425)));
            onPointerUp();

            const { endTime } = onSelect.mock.calls[0][0];
            expect(endTime).toEqual(new Date(2024, 0, 16, 0, 0));
        });
    });
});
