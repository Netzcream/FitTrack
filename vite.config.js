import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/student.css','resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        // ApexCharts se carga en lazy chunk y pesa ~580 kB minificado.
        // Subimos el umbral para evitar warning falso-positivo del bundle principal.
        chunkSizeWarningLimit: 600,
    },
    server: {
        cors: true,
    },
});
