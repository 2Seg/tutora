import { Message } from '@/types/Message';
import { Student } from '@/types/Student';

export const authUserId = 1;

export const students: Student[] = [
    {
        id: 1,
        firstName: 'Maya',
        lastName: 'Chen',
        email: 'maya.chen@email.com',
        phone: '+33123456781',
    },
    {
        id: 2,
        firstName: 'Oliver',
        lastName: 'James',
        email: 'oliver.james@email.com',
        phone: '+33123456782',
    },
    {
        id: 3,
        firstName: 'Sophie',
        lastName: 'Martin',
        email: 'sophie.martin@email.com',
        phone: '+33123456783',
    },
    {
        id: 4,
        firstName: 'Lucas',
        lastName: 'Rossi',
        email: 'lucas.rossi@email.com',
        phone: '+33123456784',
    },
    {
        id: 5,
        firstName: 'Amira',
        lastName: 'Patel',
        email: 'amira.patel@email.com',
        phone: '+33123456785',
    },
    {
        id: 6,
        firstName: 'Noah',
        lastName: 'Williams',
        email: 'noah.williams@email.com',
        phone: '+33123456786',
    },
    {
        id: 7,
        firstName: 'Noah',
        lastName: 'Williams',
        email: 'noah.williams@email.com',
        phone: '+33123456786',
    },
    {
        id: 8,
        firstName: 'Noah',
        lastName: 'Williams',
        email: 'noah.williams@email.com',
        phone: '+33123456786',
    },
    {
        id: 9,
        firstName: 'Noah',
        lastName: 'Williams',
        email: 'noah.williams@email.com',
        phone: '+33123456786',
    },
    {
        id: 10,
        firstName: 'Noah',
        lastName: 'Williams',
        email: 'noah.williams@email.com',
        phone: '+33123456786',
    },
    {
        id: 11,
        firstName: 'Noah',
        lastName: 'Williams',
        email: 'noah.williams@email.com',
        phone: '+33123456786',
    },
    {
        id: 12,
        firstName: 'Noah',
        lastName: 'Williams',
        email: 'noah.williams@email.com',
        phone: '+33123456786',
    },
    {
        id: 13,
        firstName: 'Noah',
        lastName: 'Williams',
        email: 'noah.williams@email.com',
        phone: '+33123456786',
    },
    {
        id: 14,
        firstName: 'Noah',
        lastName: 'Williams',
        email: 'noah.williams@email.com',
        phone: '+33123456786',
    },
];

let counter = 0;
const message = (
    fromUserId: number,
    toUserId: number,
    body: string,
): Message => {
    counter++;
    return {
        id: `message-${counter}`,
        fromUserId,
        toUserId,
        body,
        createdAt: new Date(Date.UTC(2026, 9, 12, 9, counter)).toISOString(),
    };
};

const T = authUserId;

// Oldest first (MessageThread reverses them)
export const messagesByStudent: Record<number, Message[]> = {
    2: [
        message(
            2,
            T,
            'Hi Naëlle, how about we plan the next course in about two weeks time? I would love to keep practicing!',
        ),
    ],
    3: [
        message(T, 3, 'Hello Oliver, are you free on Monday morning?'),
        message(3, T, 'Yes, 10:00 works for me.'),
        message(T, 3, 'Great, I will send you the Meet link.'),
        message(3, T, 'Thanks, see you on Monday!'),
    ],
    4: [
        message(
            4,
            T,
            'Hi Naëlle, how about we plan the next course in about two weeks time? I would love to keep practicing!',
        ),
        message(T, 4, 'Yes of course. I am available Tuesday and Friday.'),
        message(T, 4, 'What do you prefer?'),
        message(4, T, 'Friday sounds perfect!'),
    ],
    5: [message(5, T, 'Could we move the lesson to 5pm?')],
    6: Array.from({ length: 40 }, (_, i) =>
        i % 2 === 0
            ? message(
                  T,
                  7,
                  `Message #${i + 1}: Bonjour Noah, comment ça va aujourd'hui ?`,
              )
            : message(7, T, `Message #${i + 1}: Très bien, merci beaucoup !`),
    ),
};
