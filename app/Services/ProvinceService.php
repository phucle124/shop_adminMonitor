<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProvinceService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getAllProvinces(): array{

        $cached = Cache::get('vn_provinces_v2_code_name');
        if (is_array($cached) && count($cached) > 0) {
            return $cached;
        }

        try {
            $pendingRequest = Http::timeout(15)->retry(2, 300)->acceptJson();

            if (app()->isLocal()) {
                $pendingRequest = $pendingRequest->withoutVerifying();
            }

            $response = $pendingRequest->get('https://provinces.open-api.vn/api/v2/');

            if (!$response->successful()) {
                return [];
            }

            $items = $response->json();
            if (!is_array($items)) {
                return [];
            }

            $provinces = collect($items)
                ->filter(fn ($row) => is_array($row) && isset($row['code'], $row['name']))
                ->map(fn ($row) => [
                    'code' => (int) $row['code'],
                    'name' => (string) $row['name'],
                ])
                ->values()
                ->all();

            if (count($provinces) > 0) {
                Cache::put('vn_provinces_v2_code_name', $provinces, now()->addHours(24));
            }

            return $provinces;
        } catch (\Exception $e) {
            Log::warning('Không lấy được danh sách tỉnh v2', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function findProvinceByCode(?int $code): ?array
    {
        if (!$code) {
            return null;
        }

        foreach ($this->getAllProvinces() as $province) {
            if ((int) $province['code'] === (int) $code) {
                return $province; // ['code' => ..., 'name' => ...]
            }
        }

        return null;
    }
}
