export interface NextLesson {
    id: number;
    title: string;
    status: 'requested' | 'planned';
    startsAt: string;
    endsAt: string;
}

export interface Student {
    id: number;
    userId: number;
    email: string;
    firstName: string;
    lastName: string;
    fullName: string;
    initials: string;
    phone: string;
    avatarImg?: string;
    timezone: string;
    createdAt: string;
    updatedAt: string;
    nextLesson?: NextLesson | null;
}

export type StudentSummary = Pick<Student, 'id' | 'fullName' | 'initials' | 'avatarImg' | 'timezone'>;
