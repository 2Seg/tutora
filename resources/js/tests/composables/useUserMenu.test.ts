import { beforeEach, describe, expect, it, vi } from 'vitest';
import { LogOut, Settings, User as UserIcon } from '@lucide/vue';
import { useUserMenu } from '@/composables/useUserMenu';
import { trans } from 'laravel-vue-i18n';

vi.mock('laravel-vue-i18n', () => ({
    trans: vi.fn((key: string) => `translated:${key}`),
}));

describe('useUserMenu', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('returns the three menu items in order', () => {
        const items = useUserMenu();

        expect(items.value.map((item) => item.key)).toEqual([
            'profile',
            'settings',
            'logout',
        ]);
    });

    it('keeps href and icon from the definitions', () => {
        const items = useUserMenu();

        expect(items.value).toMatchObject([
            { href: '/profile', icon: UserIcon },
            { href: '/settings', icon: Settings },
            { href: '/logout', icon: LogOut },
        ]);
    });

    it('only sets method on logout', () => {
        const items = useUserMenu();

        expect(items.value[0].method).toBeUndefined();
        expect(items.value[1].method).toBeUndefined();
        expect(items.value[2].method).toBe('post');
    });

    it('translates label and title for each item', () => {
        const items = useUserMenu();

        expect(items.value[0]).toMatchObject({
            label: 'translated:navigation.profile.label',
            title: 'translated:navigation.profile.title',
        });
        expect(trans).toHaveBeenCalledWith('navigation.logout.label');
        expect(trans).toHaveBeenCalledWith('navigation.logout.title');
    });

    it('re-translates when the locale changes', () => {
        vi.mocked(trans).mockImplementation((key: string) => `fr:${key}`);

        const items = useUserMenu();

        expect(items.value[0].label).toBe('fr:navigation.profile.label');
    });
});
