import { Student } from '@/types/Student';

export type EventType = 'lesson' | 'blocked';
export type LessonStatus = 'draft' | 'requested' | 'planned' | 'cancelled';

interface Event {
    id: string | number;
    startTime: string | Date;
    endTime: string | Date;
    type: EventType;
    deletedAt?: string | Date;
}

export interface Lesson extends Event {
    type: 'lesson';
    status: LessonStatus;
    student: Student;
    title: string;
    meetingUrl: string;
}

export interface Unavailability extends Event {
    type: 'blocked';
}

export type CalendarEvent = Lesson | Unavailability;

export interface SlotSelection {
    startTime: Date;
    endTime: Date;
}
