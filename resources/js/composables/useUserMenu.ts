import { LogOut, Settings, User as UserIcon } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';
import { logout } from '@/routes/login';
import type { NavDefinition } from '@/types/navigation/NavDefinition';
import type { NavItem } from '@/types/navigation/NavItem';

const definitions: NavDefinition[] = [
    { key: 'profile', href: '/profile', icon: UserIcon },
    { key: 'settings', href: '/settings', icon: Settings },
    { key: 'logout', href: logout(), icon: LogOut, method: 'post' },
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
