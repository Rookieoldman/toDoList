import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'; // <-- 1. Importa el plugin de Vue

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', // Asegúrate que tus puntos de entrada son correctos
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        vue({ // <-- 2. Añade el plugin de Vue a la lista de plugins
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    // (Puede que tengas otras configuraciones aquí, como resolve.alias)
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js',
        },
    },
});