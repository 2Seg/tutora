import { Student } from '@/types/Student';

export type EventType = 'lesson' | 'time_off';
export type LessonStatus = 'draft' | 'requested' | 'planned' | 'cancelled';
export type SelectLessonStatus = Extract<LessonStatus, 'requested' | 'planned' | 'cancelled'>;

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

export interface Availability {
    id: number;
    dayOfWeek: number;
    startTime: string;
    endTime: string;
}

export interface SlotSelection {
    startsAt: Date;
    endsAt: Date;
}

export interface Draft extends SlotSelection {
    type: EventType;
    title?: string;
    status?: SelectLessonStatus;
    studentId?: Student['id'];
}

export type DraftUpdate = Partial<Pick<Draft, 'type' | 'title' | 'status' | 'studentId' | 'startsAt' | 'endsAt'>>;
