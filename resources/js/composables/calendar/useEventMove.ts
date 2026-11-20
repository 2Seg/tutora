import { onBeforeUnmount, ref, type Ref } from 'vue';

import { atMinutes, MINUTES_PER_DAY, minutesOfDay, SNAP_MINUTES } from '@/lib/calendar-utils';
import type { SlotSelection } from '@/types/calendar';

const clamp = (value: number, min: number, max: number) => Math.min(Math.max(value, min), max);

const isSamePosition = (a: SlotSelection, b: SlotSelection) =>
    a.startsAt.getTime() === b.startsAt.getTime() && a.endsAt.getTime() === b.endsAt.getTime();

export function useEventMove<T extends SlotSelection>(options: {
    body: Ref<HTMLElement | null>;
    days: () => Date[];
    grab: (target: HTMLElement) => T | null;
    onMove: (moved: T) => void;
    onRelease?: (moved: T, original: T) => void;
    onCancel?: () => void;
    isStillHeld?: () => boolean;
}) {
    const { body, days, grab, onMove, onRelease, onCancel, isStillHeld = () => true } = options;
    const isMoving = ref(false);

    let original: T | null = null;
    let current: T | null = null;
    let grabOffsetMinutes = 0;
    let durationMinutes = 0;

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
        if (e.button !== 0) return;

        const grabbed = grab(e.target as HTMLElement);
        if (!grabbed) return;

        const where = locate(e);
        if (!where) return;

        original = current = grabbed;
        grabOffsetMinutes = where.minute - minutesOfDay(grabbed.startsAt);
        durationMinutes = (grabbed.endsAt.getTime() - grabbed.startsAt.getTime()) / 60_000;

        window.addEventListener('pointermove', onPointerMove);
        window.addEventListener('pointerup', release);
        window.addEventListener('pointercancel', cancel);
    }

    function onPointerMove(e: PointerEvent) {
        if (!current || !isStillHeld()) return stop();

        const where = locate(e);
        if (!where) return;

        const startMin = clamp(
            Math.round((where.minute - grabOffsetMinutes) / SNAP_MINUTES) * SNAP_MINUTES,
            0,
            MINUTES_PER_DAY - durationMinutes,
        );
        const day = days()[where.index];
        const moved = { ...current, startsAt: atMinutes(day, startMin), endsAt: atMinutes(day, startMin + durationMinutes) };

        if (!isSamePosition(moved, current)) {
            current = moved;
            isMoving.value = true;
            onMove(moved);
        }
    }

    function release() {
        const moved = current;
        const grabbed = original;
        stop();
        if (moved && grabbed && !isSamePosition(moved, grabbed)) onRelease?.(moved, grabbed);
    }

    function cancel() {
        stop();
        onCancel?.();
    }

    function stop() {
        isMoving.value = false;
        original = current = null;
        window.removeEventListener('pointermove', onPointerMove);
        window.removeEventListener('pointerup', release);
        window.removeEventListener('pointercancel', cancel);
    }

    onBeforeUnmount(stop);

    return { isMoving, onPointerDown };
}
