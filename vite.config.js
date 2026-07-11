import {
    defineConfig
} from 'vite';
import { networkInterfaces } from 'node:os';
import laravel from 'laravel-vite-plugin';
import { bunny, google } from 'laravel-vite-plugin/fonts';
import tailwindcss from "@tailwindcss/vite";

const lanHost = Object.values(networkInterfaces())
    .flat()
    .find((address) =>
        address?.family === 'IPv4'
        && !address.internal
        && /^(10\.|192\.168\.|172\.(1[6-9]|2\d|3[01])\.)/.test(address.address)
    )?.address ?? 'localhost';

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
        host: '0.0.0.0',
        origin: `http://${lanHost}:5173`,
        hmr: {
            host: lanHost,
        },
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
