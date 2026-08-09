import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { defineConfig } from 'vitest/config';

export default defineConfig({
    plugins: [vue()],
    test: {
        environment: 'jsdom',
        include: ['resources/js/**/*.test.ts'],
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, '..'),
            '~': path.resolve(__dirname, '../../css'),
        },
    },
});
