// @ts-check
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  // Ubah ke './tests' atau lokasi tempat kamu menyimpan file .spec.js
  testDir: './tests', 

  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: 'html',

  use: {
    baseURL: 'http://127.0.0.1:8000/',
    trace: 'on-first-retry',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],

  /* Sesuaikan command server ke Laravel (php artisan serve) */
  webServer: {
    command: 'php artisan serve',
    url: 'http://127.0.0.1:8000/',
    reuseExistingServer: !process.env.CI,
  },
});