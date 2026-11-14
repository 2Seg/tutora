import { User } from '@/types/User';

export interface Auth {
    user: User;
}

export interface SharedData {
    auth: Auth;
}

declare module '@inertiajs/core' {
    interface PageProps extends SharedData {}
}
