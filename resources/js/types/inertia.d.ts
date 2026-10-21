import { SharedData } from '@/types/SharedData';

declare module '@inertiajs/core' {
    interface PageProps extends SharedData {
    }
}
