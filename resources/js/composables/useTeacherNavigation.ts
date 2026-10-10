import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { CalendarDays, CircleDollarSign, House, Mails, Users } from '@lucide/vue';

const items = [
    {
        label: 'Home',
        href: '/teacher',
        icon: House,
        exact: true,
        title: 'Dashboard',
        description: 'A quick overview of your teacher activity.',
    },
    {
        label: 'Students',
        href: '/teacher/students',
        icon: Users,
        title: 'Your students',
        description: 'A simple list of students learning with you.',
    },
    {
        label: 'Calendar',
        href: '/teacher/calendar',
        icon: CalendarDays,
        title: 'Your calendar',
        description: 'A easy view of what happens next.',
    },
    {
        label: 'Messages',
        href: '/teacher/messages',
        icon: Mails,
        title: 'Your messages',
        description: 'A great way of keeping in touch with your students.',
    },
];

type NavItem = (typeof items)[number];

export function useTeacherNavigation() {
    const page = usePage();
    const path = computed(() => page.url.split('?')[0]);

    const isActive = (item: NavItem): boolean =>
        item.exact
            ? path.value === item.href
            : path.value === item.href || path.value.startsWith(`${item.href}/`);

    const current = computed(() => items.find(isActive));

    return { items, isActive, current };
}
