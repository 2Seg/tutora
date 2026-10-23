import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, DefineComponent, h } from 'vue';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { resolveLayout } from '@/layouts/resolveLayout';
import { i18nVue } from 'laravel-vue-i18n';

createInertiaApp({
    resolve: async (name) => {
        const page: any = await resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        );

        if (page.default.layout === undefined) {
            page.default.layout = resolveLayout(name);
        }

        return page;
    },

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18nVue, {
                lang: props.initialPage.props.locale as string,
                fallbackLang: 'en',
                resolve: async (lang: string) => {
                    const langs = import.meta.glob<{
                        default: Record<string, string>;
                    }>('../../lang/*.json');
                    return await langs[`../../lang/php_${lang}.json`]();
                },
            })
            .mount(el);
    },
});
