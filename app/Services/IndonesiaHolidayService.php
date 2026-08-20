<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class IndonesiaHolidayService
{
    public function forYear(int $year): Collection
    {
        $holidays = Cache::remember("indonesia-public-holidays:v2:{$year}", now()->addHours(12), function () use ($year): array {
            try {
                $response = Http::timeout(5)
                    ->retry(2, 100)
                    ->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/ID");

                if ($response->failed() || ! is_array($response->json())) {
                    return [];
                }

                return collect($response->json())
                    ->filter(fn (array $holiday) => ! empty($holiday['date']))
                    ->mapWithKeys(fn (array $holiday) => [
                        $holiday['date'] => $holiday['localName'] ?: ($holiday['name'] ?? 'Hari Libur'),
                    ])
                    ->all();
            } catch (\Throwable) {
                return [];
            }
        });

        return collect(is_array($holidays) ? $holidays : []);
    }
}
