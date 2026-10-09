import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, DefineComponent, h } from 'vue';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import TeacherLayout from '@/layouts/TeacherLayout.vue';

createInertiaApp({
    resolve: async (name) => {
        const page: any = await resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        )

        page.default.layout ??= resolveLayout(name);

        return page;
    },

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)
    },
})

function resolveLayout(name: string) {
    if (name.startsWith('teacher/')) return TeacherLayout;

    return undefined;
}
