export type EventType = 'lesson' | 'unavailability';
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
    type: 'unavailability';
}

export type CalendarEvent = LessonEvent | BlockEvent;

export interface SlotSelection {
    startTime: Date;
    endTime: Date;
}
