import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import { Notify, Quasar } from "quasar";

// Import icon libraries
import "@quasar/extras/material-icons/material-icons.css";

// Import Quasar css
import "quasar/src/css/index.sass";
import keyPress from "@/Directives/keyPress.js";
import clickOutside from "@/Directives/clickOutside.js";
import infiniteScroll from "@/Directives/infiniteScroll.js";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
        let page = pages[`./Pages/${name}.vue`];
        page.default.layout = page.default.layout || AuthenticatedLayout;
        return page;
    },
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(Quasar, { plugins: [Notify] })
            .directive("key-press", keyPress)
            .directive("click-outside", clickOutside)
            .directive("infinite-scroll", infiniteScroll)
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});
