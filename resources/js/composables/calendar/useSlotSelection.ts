import { computed, ref } from 'vue';

import { atMinutes, DEFAULT_SLOT_MINUTES, lessonRange, MINUTES_PER_DAY, SNAP_MINUTES } from '@/lib/calendar-utils';
import type { SlotSelection } from '@/types/calendar';

/** New slots are lessons: the dragged range, from its earlier cell, with its length snapped to a lesson duration. */
function draggedLesson(anchor: number, current: number) {
    const start = Math.min(anchor, current);
    return lessonRange(start, Math.max(anchor, current) + SNAP_MINUTES - start);
}

export function useSlotSelection(day: () => Date, onSelect: (selection: SlotSelection) => void) {
    const drag = ref<{ anchor: number; current: number } | null>(null);

    function minuteAt(e: PointerEvent, column: HTMLElement) {
        const rect = column.getBoundingClientRect();
        const raw = ((e.clientY - rect.top) / rect.height) * MINUTES_PER_DAY;
        const snapped = Math.floor(raw / SNAP_MINUTES) * SNAP_MINUTES;
        return Math.min(Math.max(snapped, 0), MINUTES_PER_DAY - SNAP_MINUTES);
    }

    function onPointerDown(e: PointerEvent) {
        if (e.button !== 0 || (e.target as HTMLElement).closest('[data-event]')) return;
        const column = e.currentTarget as HTMLElement;
        column.setPointerCapture(e.pointerId);
        const minute = minuteAt(e, column);
        drag.value = { anchor: minute, current: minute };
    }

    function onPointerMove(e: PointerEvent) {
        if (!drag.value) return;
        drag.value.current = minuteAt(e, e.currentTarget as HTMLElement);
    }

    function onPointerUp() {
        const selection = drag.value;
        if (!selection) return;
        drag.value = null;

        const isClick = selection.anchor === selection.current;
        const { startMin, endMin } = isClick
            ? lessonRange(selection.anchor, DEFAULT_SLOT_MINUTES)
            : draggedLesson(selection.anchor, selection.current);

        onSelect({ startsAt: atMinutes(day(), startMin), endsAt: atMinutes(day(), endMin) });
    }

    function onPointerCancel() {
        drag.value = null;
    }

    /**
     * The lesson being dragged, in minutes since midnight: its length snaps to a lesson duration.
     * Null until the pointer leaves the cell it was pressed in.
     */
    const selection = computed(() => {
        if (!drag.value || drag.value.anchor === drag.value.current) return null;
        return draggedLesson(drag.value.anchor, drag.value.current);
    });

    return { selection, onPointerDown, onPointerMove, onPointerUp, onPointerCancel };
}
