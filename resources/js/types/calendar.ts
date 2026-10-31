import { Student } from '@/types/Student';

export type EventType = 'lesson' | 'time_off';
export type LessonStatus = 'draft' | 'requested' | 'planned' | 'cancelled';

interface Event {
    id: string | number;
    startsAt: string | Date;
    endsAt: string | Date;
    type: EventType;
    createdAt?: string | Date;
    updatedAt?: string | Date;
    deletedAt?: string | Date;
}

export interface Lesson extends Event {
    type: 'lesson';
    status: LessonStatus;
    student?: Student;
    title?: string;
    meetingUrl?: string;
}

export interface TimeOff extends Event {
    type: 'time_off';
}

export type CalendarEvent = Lesson | TimeOff;

export interface SlotSelection {
    startsAt: Date;
    endsAt: Date;
}
