import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/js/app.ts",
                "resources/css/app.css",
                "resources/sass/app.scss",
                "resources/js/app.jsx",
                "resources/js/bootstrap.js",
                "resources/js/home.ts",
            ],
            refresh: true,
        }),
    ],
    server: {
        host: "tokioubnt",
        hmr: {
            host: "tokioubnt",
        },
    },
});
