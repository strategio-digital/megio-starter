import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
    resolve: {
        alias: {
            '@/assets': '/assets',
        }
    },
    plugins: [
        laravel({
            publicDirectory: 'www',
            buildDirectory: 'temp',
            hotFile: 'temp/vite.hot',
            input: ['assets/app.ts', 'assets/panel.ts'],
            refresh: ['assets/**', 'view/**']
        })
    ]
})
