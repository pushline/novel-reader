import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny, google } from 'laravel-vite-plugin/fonts';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
                bunny('Open Sans', {
                    weights: [400, 500, 600, 700],
                }),
                google('Google Sans', {
                    weights: [400, 500, 600, 700],
                }),
                bunny('Roboto', {
                    weights: [400, 500, 700],
                }),
                bunny('Montserrat', {
                    weights: [400, 500, 600, 700],
                }),
                bunny('Nunito', {
                    weights: [400, 500, 600, 700],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
