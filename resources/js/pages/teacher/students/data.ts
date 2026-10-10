import { Student } from '@/types/Student';

export const base: Student[] = [
    {
        id: 1,
        avatarImg: 'https://i.pravatar.cc/100?img=1',
        firstName: 'Emma',
        lastName: 'Johnson',
        email: 'emma.johnson@example.com',
        phoneNumber: '+1 555 0101',
    },
    {
        id: 2,
        avatarImg: 'https://i.pravatar.cc/100?img=2',
        firstName: 'Lucas',
        lastName: 'Martin',
        email: 'lucas.martin@example.com',
        phoneNumber: '+33 6 12 34 56 78',
    },
    {
        id: 3,
        avatarImg: '', // empty on purpose, to check the initials fallback
        firstName: 'Sofia',
        lastName: 'García',
        email: 'sofia.garcia@example.com',
        phoneNumber: '+34 600 123 456',
    },
]

const extra: Student[] = Array.from({ length: 25 }, (_, i) => ({
    id: 100 + i,
    avatarImg: `https://i.pravatar.cc/100?img=${(i % 70) + 1}`,
    firstName: `Student${i + 1}`,
    lastName: `Test`,
    email: `student${i + 1}@example.com`,
    phoneNumber: '+00 000 000 000',
}))

export const students: Student[] = [...base, ...extra]
