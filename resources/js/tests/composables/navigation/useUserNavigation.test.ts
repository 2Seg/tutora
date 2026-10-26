import { beforeEach, describe, expect, it, vi } from 'vitest';
import { reactive } from 'vue';
import { CalendarDays, House, Users } from '@lucide/vue';
import { useUserNavigation } from '@/composables/navigation/useUserNavigation';
import { calendar, dashboard, students } from '@/routes/teacher';

const mocks = vi.hoisted(() => ({ page: { url: '/teacher' } }));

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => mocks.page,
}));

vi.mock('laravel-vue-i18n', () => ({
    trans: vi.fn((key: string) => `translated:${key}`),
}));

describe('useUserNavigation', () => {
    beforeEach(() => {
        mocks.page = reactive({ url: '/teacher' });
    });

    describe('items', () => {
        it('returns the three items in order', () => {
            const { items } = useUserNavigation();

            expect(items.value.map((item) => item.key)).toEqual(['home', 'students', 'calendar']);
        });

        it('keeps href, icon and exact from the definitions', () => {
            const { items } = useUserNavigation();

            expect(items.value).toMatchObject([
                { href: dashboard(), icon: House, exact: true },
                { href: students(), icon: Users },
                { href: calendar(), icon: CalendarDays },
            ]);
            expect(items.value[1].exact).toBeUndefined();
        });

        it('adds translated label, title and description', () => {
            const { items } = useUserNavigation();

            expect(items.value[1]).toMatchObject({
                label: 'translated:navigation.students.label',
                title: 'translated:navigation.students.title',
                description: 'translated:navigation.students.subtitle',
            });
        });
    });

    describe('isActive', () => {
        it.each([
            ['/teacher', 'home', true],
            ['/teacher?tab=1', 'home', true],
            ['/teacher/students', 'home', false],
            ['/teacher/students', 'students', true],
            ['/teacher/students/42', 'students', true],
            ['/teacher/students?page=2', 'students', true],
            ['/teacher/students-archive', 'students', false],
            ['/teacher/calendar/2026-10', 'calendar', true],
            ['/other', 'home', false],
        ])('url %s → %s active: %s', (url, key, expected) => {
            mocks.page.url = url;
            const { items, isActive } = useUserNavigation();
            const item = items.value.find((i) => i.key === key)!;

            expect(isActive(item)).toBe(expected);
        });
    });

    describe('current', () => {
        it('returns the item matching the current url', () => {
            mocks.page.url = '/teacher/calendar';
            const { current } = useUserNavigation();

            expect(current.value?.key).toBe('calendar');
        });

        it('returns undefined when nothing matches', () => {
            mocks.page.url = '/somewhere-else';
            const { current } = useUserNavigation();

            expect(current.value).toBeUndefined();
        });

        it('updates when the url changes', () => {
            const { current } = useUserNavigation();
            expect(current.value?.key).toBe('home');

            mocks.page.url = '/teacher/students';
            expect(current.value?.key).toBe('students');
        });
    });
});
