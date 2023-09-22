import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    resolve: {
        alias: {
            '@/assets': '/assets',
            '@/saas': '/vendor/strategio/saas/vue',
            './vue': '/vendor/strategio/saas/vue'
        }
    },
    plugins: [
        vue(),
        laravel({
            publicDirectory: 'www',
            buildDirectory: 'temp',
            hotFile: 'temp/vite.hot',
            input: ['assets/app.ts', 'assets/saas.ts'],
            refresh: ['assets/**', 'view/**']
        })
    ]
})
