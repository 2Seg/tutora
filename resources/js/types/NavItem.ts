import { NavDefinition } from '@/types/NavDefinition';

export type NavItem = NavDefinition & {
    label: string;
    title: string;
    description: string;
};
