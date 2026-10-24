import { NavDefinition } from '@/types/NavDefinition';
import { LogOut, Settings, User as UserIcon } from '@lucide/vue';
import { computed } from 'vue';
import { NavItem } from '@/types/NavItem';
import { trans } from 'laravel-vue-i18n';

const definitions: NavDefinition[] = [
    { key: 'profile', href: '/profile', icon: UserIcon },
    { key: 'settings', href: '/settings', icon: Settings },
    { key: 'logout', href: '/logout', icon: LogOut, method: 'post' },
];

export function useUserMenu() {
    return computed<NavItem[]>(() =>
        definitions.map((definition) => ({
            ...definition,
            label: trans(`navigation.${definition.key}.label`),
            title: trans(`navigation.${definition.key}.title`),
        })),
    );
}
