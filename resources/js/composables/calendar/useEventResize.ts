import { ref, type Ref } from 'vue';

import { atMinutes, isSameDay, MINUTES_PER_DAY, minutesOfDay, SNAP_MINUTES, toDate } from '@/lib/calendar-utils';
import type { CalendarEvent } from '@/types/calendar';

/**
 * Drag the bottom handle of an event to change its end time (snapped, within the same day).
 * While dragging, `resizing` holds the new end so the column can preview it.
 * `onCommit` is only called on release.
 */
export function useEventResize(
    column: Ref<HTMLElement | null>,
    day: () => Date,
    onCommit: (event: CalendarEvent, endTime: Date) => void,
) {
    const resizing = ref<{ id: CalendarEvent['id']; endMin: number } | null>(null);

    function onResizeStart(event: CalendarEvent, e: PointerEvent) {
        if (e.button !== 0 || !column.value) return;

        // The handle keeps the pointer (and its cursor) until release, even outside the column.
        const handle = e.currentTarget as HTMLElement;
        handle.setPointerCapture(e.pointerId);

        const start = toDate(event.startTime);
        const end = toDate(event.endTime);
        const startMin = minutesOfDay(start);
        resizing.value = { id: event.id, endMin: isSameDay(end, day()) ? minutesOfDay(end) : MINUTES_PER_DAY };

        const onMove = (move: PointerEvent) => {
            if (!resizing.value || !column.value) return;
            const rect = column.value.getBoundingClientRect();
            const minute = ((move.clientY - rect.top) / rect.height) * MINUTES_PER_DAY;
            const snapped = Math.round(minute / SNAP_MINUTES) * SNAP_MINUTES;
            resizing.value.endMin = Math.min(Math.max(snapped, startMin + SNAP_MINUTES), MINUTES_PER_DAY);
        };

        const finish = (commit: boolean) => {
            handle.removeEventListener('pointermove', onMove);
            handle.removeEventListener('pointerup', onUp);
            handle.removeEventListener('pointercancel', onCancel);

            const endMin = resizing.value?.endMin;
            resizing.value = null;
            if (commit && endMin !== undefined) onCommit(event, atMinutes(day(), endMin));
        };
        const onUp = () => finish(true);
        const onCancel = () => finish(false);

        handle.addEventListener('pointermove', onMove);
        handle.addEventListener('pointerup', onUp);
        handle.addEventListener('pointercancel', onCancel);
    }

    return { resizing, onResizeStart };
}
