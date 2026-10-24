import { SharedData } from '@/types/inertia/SharedData';

declare module '@inertiajs/core' {
    interface PageProps extends SharedData {}
}
