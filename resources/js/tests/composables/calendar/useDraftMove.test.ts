import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { ref, shallowRef, watch } from 'vue';

import { useDraftMove } from '@/composables/calendar/useDraftMove';
import type { SlotSelection } from '@/types/calendar';

// The composable registers an unmount hook, but there is no component here: collect the hooks to run them by hand.
const hooks = vi.hoisted(() => ({ unmount: [] as Array<() => void> }));

vi.mock('vue', async (importOriginal) => ({
    ...(await importOriginal<typeof import('vue')>()),
    onBeforeUnmount: (hook: () => void) => {
        hooks.unmount.push(hook);
    },
}));

const TOP = 100; // top of the day columns in the viewport
const HEIGHT = 1440; // 1px === 1 minute, which keeps the maths readable
const COLUMN_WIDTH = 100;
const COLUMN_COUNT = 7;

/** `minutes` after local midnight of the column's day (column 0 is Monday 12 October 2026). */
const at = (column: number, minutes: number) => new Date(Date.UTC(2026, 9, 12 + column, 0, minutes));
const DAYS = Array.from({ length: COLUMN_COUNT }, (_, column) => at(column, 0));

const xIn = (column: number) => column * COLUMN_WIDTH + COLUMN_WIDTH / 2;
const yAt = (minute: number) => TOP + minute;
const draftOf = (column: number, from: number, to: number): SlotSelection => ({
    startTime: at(column, from),
    endTime: at(column, to),
});

function makeBody() {
    const columns = DAYS.map((_, column) => ({
        getBoundingClientRect: () => ({
            left: column * COLUMN_WIDTH,
            right: (column + 1) * COLUMN_WIDTH,
            top: TOP,
            height: HEIGHT,
        }),
    }));

    return { querySelectorAll: vi.fn(() => columns) } as unknown as HTMLElement;
}

type Point = { x?: number; y?: number; button?: number };

/** The press that reaches the composable: `onDraft` says whether it landed on the draft. */
function press({ x = 0, y = 0, button = 0 }: Point = {}, onDraft = true) {
    return {
        button,
        clientX: x,
        clientY: y,
        target: { closest: vi.fn((selector: string) => (onDraft && selector === '[data-draft]' ? {} : null)) },
    } as unknown as PointerEvent;
}

let windowTarget: EventTarget;

/** Moves and releases are listened to on `window`. */
function fire(type: 'pointermove' | 'pointerup' | 'pointercancel', { x = 0, y = 0, button = 0 }: Point = {}) {
    windowTarget.dispatchEvent(Object.assign(new Event(type), { clientX: x, clientY: y, button }));
}

function setup(initial: SlotSelection | null = draftOf(1, 600, 660), body: HTMLElement | null = makeBody()) {
    const draft = ref<SlotSelection | null>(initial);
    const changes = vi.fn();
    watch(draft, changes, { flush: 'sync' });

    const { isMoving, onPointerDown } = useDraftMove({
        body: shallowRef(body),
        days: () => DAYS,
        draft,
    });

    return { draft, changes, isMoving, onPointerDown };
}

beforeEach(() => {
    windowTarget = new EventTarget();
    vi.stubGlobal('window', windowTarget);
    hooks.unmount.length = 0;
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('useDraftMove', () => {
    describe('pointer down', () => {
        it('starts moving when the draft is pressed', () => {
            const { onPointerDown, isMoving } = setup();

            expect(isMoving.value).toBe(false);

            onPointerDown(press({ x: xIn(1), y: yAt(630) }));

            expect(isMoving.value).toBe(true);
        });

        it('ignores presses that are not on the draft', () => {
            const { onPointerDown, isMoving, draft, changes } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(630) }, false));
            fire('pointermove', { x: xIn(3), y: yAt(900) });

            expect(isMoving.value).toBe(false);
            expect(draft.value).toEqual(draftOf(1, 600, 660));
            expect(changes).not.toHaveBeenCalled();
        });

        it('ignores buttons other than the main one', () => {
            const { onPointerDown, isMoving } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(630), button: 2 }));

            expect(isMoving.value).toBe(false);
        });

        it('ignores presses when there is no draft', () => {
            const { onPointerDown, isMoving } = setup(null);

            onPointerDown(press({ x: xIn(1), y: yAt(630) }));

            expect(isMoving.value).toBe(false);
        });
    });

    describe('moving within a column', () => {
        it('moves the draft by the distance the pointer travelled', () => {
            const { onPointerDown, draft } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(630) })); // grabbed 30 minutes into the draft
            fire('pointermove', { x: xIn(1), y: yAt(720) });

            expect(draft.value).toEqual(draftOf(1, 690, 750));
        });

        it.each([
            [727, 690],
            [728, 705],
            [712, 675],
            [713, 690],
        ])('snaps to 15 minutes: pointer at minute %s puts the draft at %s', (pointerMinute, expectedStart) => {
            const { onPointerDown, draft } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(630) })); // grabbed 30 minutes into the draft
            fire('pointermove', { x: xIn(1), y: yAt(pointerMinute) });

            expect(draft.value).toEqual(draftOf(1, expectedStart, expectedStart + 60));
        });

        it('keeps the point where the draft was grabbed under the pointer', () => {
            const { onPointerDown, draft, changes } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(645) })); // grabbed 45 minutes into the draft

            fire('pointermove', { x: xIn(1), y: yAt(645) });
            expect(draft.value).toEqual(draftOf(1, 600, 660));
            expect(changes).not.toHaveBeenCalled();

            fire('pointermove', { x: xIn(1), y: yAt(660) });
            expect(draft.value).toEqual(draftOf(1, 615, 675));
        });

        it('only updates the draft when its position changes', () => {
            const { onPointerDown, changes } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(630) }));
            fire('pointermove', { x: xIn(1), y: yAt(722) });
            fire('pointermove', { x: xIn(1), y: yAt(725) }); // same 15 minute step

            expect(changes).toHaveBeenCalledTimes(1);
        });

        it('stops at the top of the day', () => {
            const { onPointerDown, draft } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(630) }));
            fire('pointermove', { x: xIn(1), y: yAt(-500) });

            expect(draft.value).toEqual(draftOf(1, 0, 60));
        });

        it('stops at midnight, with the end at the start of the next day', () => {
            const { onPointerDown, draft } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(630) }));
            fire('pointermove', { x: xIn(1), y: yAt(5000) });

            expect(draft.value).toEqual(draftOf(1, 1380, 1440));
            expect(draft.value?.endTime).toEqual(new Date(Date.UTC(2026, 9, 14, 0, 0)));
        });
    });

    describe('moving between columns', () => {
        it('jumps to the column under the pointer and keeps the time', () => {
            const { onPointerDown, draft } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(630) }));
            fire('pointermove', { x: xIn(4), y: yAt(630) });

            expect(draft.value).toEqual(draftOf(4, 600, 660));
        });

        it('keeps its length', () => {
            const { onPointerDown, draft } = setup(draftOf(2, 480, 570)); // 90 minutes

            onPointerDown(press({ x: xIn(2), y: yAt(500) })); // grabbed 20 minutes into the draft
            fire('pointermove', { x: xIn(5), y: yAt(800) });

            expect(draft.value).toEqual(draftOf(5, 780, 870));
        });

        it.each([
            [299, 2],
            [300, 3],
        ])('a pointer at x=%s belongs to column %s', (x, column) => {
            const { onPointerDown, draft } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(630) }));
            fire('pointermove', { x, y: yAt(630) });

            expect(draft.value).toEqual(draftOf(column, 600, 660));
        });

        it('uses the first column when the pointer is left of the grid', () => {
            const { onPointerDown, draft } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(630) }));
            fire('pointermove', { x: -50, y: yAt(630) });

            expect(draft.value).toEqual(draftOf(0, 600, 660));
        });

        it('uses the last column when the pointer is right of the grid', () => {
            const { onPointerDown, draft } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(630) }));
            fire('pointermove', { x: COLUMN_COUNT * COLUMN_WIDTH + 50, y: yAt(630) });

            expect(draft.value).toEqual(draftOf(6, 600, 660));
        });
    });

    describe('draft length', () => {
        it('keeps a 50 minute draft (the default slot) at 50 minutes and snaps only its start', () => {
            const { onPointerDown, draft } = setup(draftOf(1, 600, 650));

            onPointerDown(press({ x: xIn(1), y: yAt(620) })); // grabbed 20 minutes into the draft
            fire('pointermove', { x: xIn(1), y: yAt(700) });

            expect(draft.value).toEqual(draftOf(1, 675, 725));
        });

        it('ends exactly at midnight when a 50 minute draft is dragged to the bottom', () => {
            const { onPointerDown, draft } = setup(draftOf(1, 1300, 1350));

            onPointerDown(press({ x: xIn(1), y: yAt(1310) }));
            fire('pointermove', { x: xIn(1), y: yAt(5000) });

            // The start is not on a 15 minute line here: the length wins over the snapping.
            expect(draft.value).toEqual(draftOf(1, 1390, 1440));
        });

        it('moves a draft that already ends at midnight', () => {
            const { onPointerDown, draft } = setup(draftOf(1, 1380, 1440));

            onPointerDown(press({ x: xIn(1), y: yAt(1400) })); // grabbed 20 minutes into the draft
            fire('pointermove', { x: xIn(1), y: yAt(1000) });

            expect(draft.value).toEqual(draftOf(1, 975, 1035));
        });

        it('only changes the day of a draft that lasts the whole day', () => {
            const { onPointerDown, draft } = setup(draftOf(1, 0, 1440));

            onPointerDown(press({ x: xIn(1), y: yAt(300) }));
            fire('pointermove', { x: xIn(3), y: yAt(900) });

            expect(draft.value).toEqual(draftOf(3, 0, 1440));
        });
    });

    describe('consecutive moves', () => {
        it('measures the grab point again on every press', () => {
            const { onPointerDown, draft } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(630) })); // grabbed 30 minutes into the draft
            fire('pointermove', { x: xIn(1), y: yAt(720) });
            fire('pointerup');
            expect(draft.value).toEqual(draftOf(1, 690, 750));

            onPointerDown(press({ x: xIn(1), y: yAt(700) })); // now 10 minutes into the draft
            fire('pointermove', { x: xIn(1), y: yAt(760) });

            expect(draft.value).toEqual(draftOf(1, 750, 810));
        });
    });

    describe('without day columns', () => {
        it('does not start moving when the body is not mounted', () => {
            const { onPointerDown, isMoving, draft, changes } = setup(draftOf(1, 600, 660), null);

            onPointerDown(press({ x: xIn(1), y: yAt(630) }));
            fire('pointermove', { x: xIn(3), y: yAt(900) });

            expect(isMoving.value).toBe(false);
            expect(draft.value).toEqual(draftOf(1, 600, 660));
            expect(changes).not.toHaveBeenCalled();
        });
    });

    describe('stopping', () => {
        it.each(['pointerup', 'pointercancel'] as const)('%s ends the move and stops following the pointer', (type) => {
            const { onPointerDown, isMoving, draft, changes } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(630) }));
            fire(type);
            fire('pointermove', { x: xIn(4), y: yAt(900) });

            expect(isMoving.value).toBe(false);
            expect(draft.value).toEqual(draftOf(1, 600, 660));
            expect(changes).not.toHaveBeenCalled();
        });

        it('keeps the draft where it was dropped', () => {
            const { onPointerDown, draft } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(630) }));
            fire('pointermove', { x: xIn(3), y: yAt(720) });
            fire('pointerup');

            expect(draft.value).toEqual(draftOf(3, 690, 750));
        });

        it('can start a new move after one has ended', () => {
            const { onPointerDown, isMoving, draft } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(630) }));
            fire('pointerup');
            onPointerDown(press({ x: xIn(1), y: yAt(630) }));
            fire('pointermove', { x: xIn(2), y: yAt(630) });

            expect(isMoving.value).toBe(true);
            expect(draft.value).toEqual(draftOf(2, 600, 660));
        });

        it('ends when the draft is discarded during the move', () => {
            const { onPointerDown, isMoving, draft } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(630) }));
            draft.value = null; // Escape, for example
            fire('pointermove', { x: xIn(3), y: yAt(720) });

            expect(isMoving.value).toBe(false);
            expect(draft.value).toBeNull();
        });

        it('ends and stops following the pointer when the component is unmounted', () => {
            const { onPointerDown, isMoving, draft } = setup();

            onPointerDown(press({ x: xIn(1), y: yAt(630) }));
            hooks.unmount.forEach((hook) => hook());
            fire('pointermove', { x: xIn(3), y: yAt(720) });

            expect(isMoving.value).toBe(false);
            expect(draft.value).toEqual(draftOf(1, 600, 660));
        });
    });
});
