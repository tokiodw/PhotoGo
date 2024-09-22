import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/js/app.ts",
                "resources/css/app.css",
                "resources/sass/app.scss",
                "resources/js/bootstrap.js",
                "resources/js/home.ts",
                "resources/js/map.ts",
                "resources/sass/map.scss",
                "resources/images/logo.png",
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
    // build: {
    //     assetsDir: 'assets',
    //     manifest: true,
    // }
});
