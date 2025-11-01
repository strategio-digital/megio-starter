import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    resolve: {
        alias: {
            '@/assets': '/assets',
        }
    },
    plugins: [
        vue(),
        tailwindcss(),
        laravel({
            publicDirectory: 'www',
            buildDirectory: 'temp',
            hotFile: 'temp/vite.hot',
            input: ['assets/app.ts', 'assets/panel.ts'],
            refresh: ['assets/**', 'view/**']
        })
    ]
})
