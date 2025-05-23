import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'node:path';
import { defineConfig } from 'vite';
import * as path from 'node:path';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.tsx', 'resources/js/pages/MainPage.tsx'],
      refresh: true,
    }),
    react(),
    tailwindcss(),
  ],
  esbuild: {
    jsx: 'automatic',
  },
  resolve: {
    alias: {
      'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
      'react-helmet-async': path.resolve(__dirname, 'node_modules/react-helmet-async'),
    },
  },
  optimizeDeps: {
    include: ['react-helmet-async', 'react-markdown', 'react-simplemde-editor'],
  },
  build: {
    // rollupOptions: {
    //   external: ['react-helmet-async', 'react-markdown', 'react-simplemde-editor'],
    // },
    outDir: 'dist',
    emptyOutDir: true,
  },
  base: process.env.ASSET_URL ?? '/',
  server: {
    host: '127.0.0.1',
    port: 5173,
    hmr: {
      host: '127.0.0.1',
    },
  },
});
