import { usePage } from '@inertiajs/vue3';
import { CalendarDays, House, Users } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';
import { calendar, dashboard, students } from '@/routes/teacher';
import type { NavDefinition } from '@/types/NavDefinition';
import type { NavItem } from '@/types/NavItem';

const definitions: NavDefinition[] = [
    { key: 'home', href: dashboard(), icon: House, exact: true },
    { key: 'students', href: students(), icon: Users },
    { key: 'calendar', href: calendar(), icon: CalendarDays },
];

const urlOf = ({ href }: NavDefinition): string =>
    typeof href === 'string' ? href : href.url;

export function useUserNavigation() {
    const page = usePage();
    const path = computed(() => page.url.split('?')[0]);

    const items = computed<NavItem[]>(() =>
        definitions.map((definition) => ({
            ...definition,
            label: trans(`navigation.${definition.key}.label`),
            title: trans(`navigation.${definition.key}.title`),
            description: trans(`navigation.${definition.key}.subtitle`),
        })),
    );

    const isActive = (item: NavDefinition): boolean => {
        const url = urlOf(item);

        return item.exact
            ? path.value === url
            : path.value === url || path.value.startsWith(`${url}/`);
    };

    const current = computed(() => items.value.find(isActive));

    return { items, isActive, current };
}
