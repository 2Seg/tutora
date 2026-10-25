import { addDays, startOfWeek } from '@/lib/calendar-utils';
import type { CalendarEvent, LessonStatus } from '@/types/calendar';

// [day of the week (0 = Monday), from, to, student, status]
type LessonSpec = [number, string, string, string, LessonStatus];
// [day of the week (0 = Monday), from, to]
type TimeOffSpec = [number, string, string];

const THIS_WEEK_LESSONS: LessonSpec[] = [
    [0, '10:00', '11:00', 'Maya Chen', 'planned'],
    [1, '14:00', '15:00', 'Oliver James', 'planned'],
    [2, '10:00', '11:00', 'Sophie Martin', 'planned'],
    [3, '11:00', '12:00', 'Lucas Rossi', 'cancelled'],
    [4, '10:00', '11:00', 'Amira Patel', 'planned'],
    [5, '10:00', '11:00', 'Maya Chen', 'cancelled'],
    [6, '09:00', '10:30', 'Amira Patel', 'planned'],
];
const THIS_WEEK_UNAVAILABILITY: TimeOffSpec[] = [
    [2, '12:00', '14:00'],
    [4, '13:00', '17:00'],
    [6, '13:00', '17:00'],
];

const NEXT_WEEK_LESSONS: LessonSpec[] = [
    [0, '09:00', '10:00', 'Lucas Rossi', 'planned'],
    [0, '15:00', '16:00', 'Sophie Martin', 'draft'],
    [1, '10:00', '11:00', 'Amira Patel', 'requested'],
    [2, '14:00', '15:00', 'Oliver James', 'planned'],
    [3, '10:00', '11:00', 'Maya Chen', 'cancelled'],
    [4, '16:00', '17:00', 'Sophie Martin', 'requested'],
    [5, '11:00', '12:30', 'Lucas Rossi', 'planned'],
];
const NEXT_WEEK_UNAVAILABILITY: TimeOffSpec[] = [
    [3, '13:00', '17:00'],
    [5, '14:00', '18:00'],
];

export function buildFakeEvents(today: Date = new Date()): CalendarEvent[] {
    const thisMonday = startOfWeek(today);
    const nextMonday = addDays(thisMonday, 7);

    return [
        ...buildWeek(thisMonday, THIS_WEEK_LESSONS, THIS_WEEK_UNAVAILABILITY),
        ...buildWeek(nextMonday, NEXT_WEEK_LESSONS, NEXT_WEEK_UNAVAILABILITY),
    ];
}

function buildWeek(monday: Date, lessons: LessonSpec[], timeOff: TimeOffSpec[]): CalendarEvent[] {
    let counter = 0;
    const id = () => `fake-${monday.getTime()}-${counter++}`;

    const at = (day: number, time: string) => {
        const [hours, minutes] = time.split(':').map(Number);
        const date = addDays(monday, day);
        date.setHours(hours, minutes, 0, 0);
        return date;
    };

    return [
        // Unavailable every night
        ...[0, 1, 2, 3, 4, 5, 6].flatMap((day): CalendarEvent[] => [
            { id: id(), type: 'unavailability', startTime: at(day, '00:00'), endTime: at(day, '06:00') },
            { id: id(), type: 'unavailability', startTime: at(day, '22:00'), endTime: at(day, '24:00') },
        ]),

        ...timeOff.map(([day, from, to]): CalendarEvent => ({
            id: id(),
            type: 'unavailability',
            startTime: at(day, from),
            endTime: at(day, to),
        })),

        ...lessons.map(([day, from, to, student, status]): CalendarEvent => ({
            id: id(),
            type: 'lesson',
            status,
            title: student,
            subtitle: 'French',
            startTime: at(day, from),
            endTime: at(day, to),
        })),
    ];
}
