import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { CalendarDays, House, Users } from '@lucide/vue';
import { NavDefinition } from '@/types/NavDefinition';
import { NavItem } from '@/types/NavItem';

const definitions: NavDefinition[] = [
    { key: 'home', href: '/teacher', icon: House, exact: true },
    { key: 'students', href: '/teacher/students', icon: Users },
    { key: 'calendar', href: '/teacher/calendar', icon: CalendarDays },
];

export function useUserNavigation() {
    const page = usePage();
    const path = computed(() => page.url.split('?')[0]);

    // Evaluated lazily at render time, and re-evaluated when the language loads or changes
    const items = computed<NavItem[]>(() =>
        definitions.map((definition) => ({
            ...definition,
            label: trans(`navigation.${definition.key}.label`),
            title: trans(`navigation.${definition.key}.title`),
            description: trans(`navigation.${definition.key}.subtitle`),
        })),
    );

    const isActive = (item: NavDefinition): boolean =>
        item.exact
            ? path.value === item.href
            : path.value === item.href ||
              path.value.startsWith(`${item.href}/`);

    const current = computed(() => items.value.find(isActive));

    return { items, isActive, current };
}
