import { resolve } from 'node:path';
import { defineConfig } from 'vitest/config';

export default defineConfig({
  resolve: {
    alias: {
      '@assets': resolve(import.meta.dirname, 'assets'),
    },
  },
  test: {
    environment: 'jsdom',
    include: ['tests/Js/**/*.spec.ts'],
    globals: false,
    restoreMocks: true,
    unstubGlobals: true,
    coverage: {
      provider: 'v8',
      include: ['assets/controllers/**/*.js', 'assets/utils/**/*.js'],
      reporter: ['text', 'html'],
      reportsDirectory: 'coverage-js',
    },
  },
});
