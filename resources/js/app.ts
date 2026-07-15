import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent} from 'vue';
import { createApp, h } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: async (name) => {
        const page = await resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        );
        const pageComponent = page.default;

        if (
            pageComponent.layout &&
            typeof pageComponent.layout === 'object' &&
            !('render' in pageComponent.layout) &&
            !('setup' in pageComponent.layout)
        ) {
            const layoutConfig = pageComponent.layout;

            if (name.startsWith('auth/')) {
                pageComponent.layout = (h: any, page: any) =>
                    h(AuthLayout, layoutConfig, () => page);
            } else if (name.startsWith('settings/')) {
                pageComponent.layout = (h: any, page: any) =>
                    h(AppLayout, layoutConfig, () =>
                        h(SettingsLayout, () => page),
                    );
            } else {
                pageComponent.layout = (h: any, page: any) =>
                    h(AppLayout, layoutConfig, () => page);
            }
        }

        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
