import { User } from '@/types/User';

export function getFullName(user: User): string {
    return `${user.firstName} ${user.lastName}`;
}

export function getInitials(user: User): string {
    return `${user.firstName[0] ?? ''}${user.lastName[0] ?? ''}`.toUpperCase();
}
