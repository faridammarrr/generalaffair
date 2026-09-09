import { test, expect } from '@playwright/test';

// Dijalankan otomatis sebelum setiap test dimulai
test.beforeEach(async ({ page }) => {
  await page.goto('http://127.0.0.1:8000');
});

test('Membuka dashboard', async ({ page }) => {
  await expect(page).toHaveURL('http://127.0.0.1:8000/');
});

test('Button refresh berfungsi', async ({ page }) => {
  // Sekarang aman diklik karena halaman sudah terbuka
  await page.getByText('Segarkan').click();
});

test('Pidah halaman: Request', async ({ page }) => {
  await page.getByRole('link', { name: 'Request' }).click();
  await expect(page).toHaveURL(/.*request/);
});

test('Pidah halaman: Response', async ({ page }) => {
  await page.getByRole('link', { name: 'Response' }).click();
  await expect(page).toHaveURL(/.*response/);
});