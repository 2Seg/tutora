export type EventType = 'lesson' | 'unavailable';
export type LessonStatus = 'draft' | 'requested' | 'planned' | 'cancelled';

interface BaseEvent {
    id: string | number;
    startTime: string | Date;
    endTime: string | Date;
    type: EventType;
    title?: string;
}

export interface LessonEvent extends BaseEvent {
    type: 'lesson';
    status: LessonStatus;
}

export interface BlockEvent extends BaseEvent {
    type: 'unavailable';
}

export type CalendarEvent = LessonEvent | BlockEvent;

export interface SlotSelection {
    startTime: Date;
    endTime: Date;
}

export interface StatusBadge {}
