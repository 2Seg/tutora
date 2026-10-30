import { onBeforeUnmount, ref, type Ref } from 'vue';

import { atMinutes, MINUTES_PER_DAY, minutesOfDay, SNAP_MINUTES } from '@/lib/calendar-utils';
import type { SlotSelection } from '@/types/calendar';

const clamp = (value: number, min: number, max: number) => Math.min(Math.max(value, min), max);

/**
 * Press the draft (marked with `data-draft`) and drag it to move it. It keeps its length, snaps to 15 minutes
 * and jumps to the day column under the pointer (columns are marked with `data-day`).
 *
 * Bind `onPointerDown` on the element that contains the day columns. While `isMoving` is true, cover the page
 * with a layer that carries the cursor: the draft changes column while it moves, so its element can't hold it.
 */
export function useDraftMove(options: {
    body: Ref<HTMLElement | null>;
    days: () => Date[];
    draft: Ref<SlotSelection | null>;
}) {
    const { body, days, draft } = options;
    const isMoving = ref(false);

    /** Minutes between the start of the draft and the point where it was grabbed. */
    let grab = 0;
    /** Length of the draft, in minutes. */
    let duration = 0;

    /** Day column and (fractional) minute of the day under the pointer. */
    function locate(e: PointerEvent) {
        const rects = Array.from(body.value?.querySelectorAll<HTMLElement>('[data-day]') ?? []).map((column) =>
            column.getBoundingClientRect(),
        );
        if (!rects.length) return null;

        let index = rects.findIndex((rect) => e.clientX >= rect.left && e.clientX < rect.right);
        if (index === -1) index = e.clientX < rects[0].left ? 0 : rects.length - 1;

        const { top, height } = rects[index];
        return { index, minute: ((e.clientY - top) / height) * MINUTES_PER_DAY };
    }

    function onPointerDown(e: PointerEvent) {
        if (e.button !== 0 || !draft.value || !(e.target as HTMLElement).closest('[data-draft]')) return;

        const where = locate(e);
        if (!where) return;

        grab = where.minute - minutesOfDay(draft.value.startTime);
        duration = (draft.value.endTime.getTime() - draft.value.startTime.getTime()) / 60_000;
        isMoving.value = true;

        window.addEventListener('pointermove', onPointerMove);
        window.addEventListener('pointerup', stop);
        window.addEventListener('pointercancel', stop);
    }

    function onPointerMove(e: PointerEvent) {
        const current = draft.value;
        // The draft was discarded while moving (Escape, for example).
        if (!current) return stop();

        const where = locate(e);
        if (!where) return;

        const startMin = clamp(
            Math.round((where.minute - grab) / SNAP_MINUTES) * SNAP_MINUTES,
            0,
            MINUTES_PER_DAY - duration,
        );
        const day = days()[where.index];
        const startTime = atMinutes(day, startMin);
        const endTime = atMinutes(day, startMin + duration);

        if (startTime.getTime() !== current.startTime.getTime() || endTime.getTime() !== current.endTime.getTime()) {
            draft.value = { startTime, endTime };
        }
    }

    function stop() {
        isMoving.value = false;
        window.removeEventListener('pointermove', onPointerMove);
        window.removeEventListener('pointerup', stop);
        window.removeEventListener('pointercancel', stop);
    }

    onBeforeUnmount(stop);

    return { isMoving, onPointerDown };
}
