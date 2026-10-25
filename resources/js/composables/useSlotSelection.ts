import { computed, ref } from 'vue';

import { atMinutes, MINUTES_PER_DAY } from '@/lib/calendar-utils';
import type { SlotSelection } from '@/types/calendar';

const SNAP_MINUTES = 15;
const DEFAULT_SLOT_MINUTES = 50;

/**
 * Click or drag on an empty part of a day column to select a time slot.
 * Bind the returned handlers on the column element.
 */
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

        const start = Math.min(selection.anchor, selection.current);
        const isClick = selection.anchor === selection.current;
        const end = Math.min(
            isClick ? start + DEFAULT_SLOT_MINUTES : Math.max(selection.anchor, selection.current) + SNAP_MINUTES,
            MINUTES_PER_DAY,
        );

        onSelect({ startTime: atMinutes(day(), start), endTime: atMinutes(day(), end) });
    }

    function onPointerCancel() {
        drag.value = null;
    }

    /** The range being dragged, in minutes since midnight, or null when nothing is being selected. */
    const selection = computed(() => {
        if (!drag.value) return null;
        return {
            startMin: Math.min(drag.value.anchor, drag.value.current),
            endMin: Math.max(drag.value.anchor, drag.value.current) + SNAP_MINUTES,
        };
    });

    return { selection, onPointerDown, onPointerMove, onPointerUp, onPointerCancel };
}
