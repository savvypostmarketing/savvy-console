<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoLocationService
{
    /**
     * Cache TTL in seconds (24 hours)
     */
    private const CACHE_TTL = 86400;

    /**
     * Country code to name mapping for common countries
     */
    private const COUNTRY_NAMES = [
        'US' => 'United States',
        'CA' => 'Canada',
        'MX' => 'Mexico',
        'GB' => 'United Kingdom',
        'DE' => 'Germany',
        'FR' => 'France',
        'ES' => 'Spain',
        'IT' => 'Italy',
        'BR' => 'Brazil',
        'AR' => 'Argentina',
        'CO' => 'Colombia',
        'CL' => 'Chile',
        'PE' => 'Peru',
        'VE' => 'Venezuela',
        'EC' => 'Ecuador',
        'GT' => 'Guatemala',
        'CU' => 'Cuba',
        'DO' => 'Dominican Republic',
        'HN' => 'Honduras',
        'SV' => 'El Salvador',
        'NI' => 'Nicaragua',
        'CR' => 'Costa Rica',
        'PA' => 'Panama',
        'PR' => 'Puerto Rico',
        'UY' => 'Uruguay',
        'PY' => 'Paraguay',
        'BO' => 'Bolivia',
        'AU' => 'Australia',
        'NZ' => 'New Zealand',
        'JP' => 'Japan',
        'CN' => 'China',
        'IN' => 'India',
        'KR' => 'South Korea',
        'RU' => 'Russia',
        'ZA' => 'South Africa',
        'NG' => 'Nigeria',
        'EG' => 'Egypt',
        'NL' => 'Netherlands',
        'BE' => 'Belgium',
        'CH' => 'Switzerland',
        'AT' => 'Austria',
        'PT' => 'Portugal',
        'SE' => 'Sweden',
        'NO' => 'Norway',
        'DK' => 'Denmark',
        'FI' => 'Finland',
        'PL' => 'Poland',
        'IE' => 'Ireland',
        'IL' => 'Israel',
        'AE' => 'United Arab Emirates',
        'SA' => 'Saudi Arabia',
        'SG' => 'Singapore',
        'MY' => 'Malaysia',
        'PH' => 'Philippines',
        'TH' => 'Thailand',
        'ID' => 'Indonesia',
        'VN' => 'Vietnam',
        'TW' => 'Taiwan',
        'HK' => 'Hong Kong',
    ];

    /**
     * Get geolocation data for an IP address
     *
     * @param string $ip The IP address to lookup
     * @return array{country: string|null, country_name: string|null, city: string|null, region: string|null, latitude: float|null, longitude: float|null, timezone: string|null}
     */
    public function lookup(string $ip): array
    {
        // Skip for localhost/private IPs
        if ($this->isPrivateIp($ip)) {
            return $this->emptyResult();
        }

        // Check cache first
        $cacheKey = "geolocation:{$ip}";
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Try primary API (country.is - simple, free, no key needed)
        $result = $this->lookupCountryIs($ip);

        // If we only got country, try to get more details from ip-api.com
        if ($result['country'] && !$result['city']) {
            $detailed = $this->lookupIpApi($ip);
            if ($detailed['city']) {
                $result = array_merge($result, $detailed);
            }
        }

        // Cache the result
        if ($result['country']) {
            Cache::put($cacheKey, $result, self::CACHE_TTL);
        }

        return $result;
    }

    /**
     * Lookup using country.is API (simple, free, unlimited)
     */
    private function lookupCountryIs(string $ip): array
    {
        try {
            $response = Http::timeout(3)
                ->retry(2, 100)
                ->get("https://api.country.is/{$ip}");

            if ($response->successful()) {
                $data = $response->json();
                $countryCode = $data['country'] ?? null;

                return [
                    'country' => $countryCode,
                    'country_name' => $this->getCountryName($countryCode),
                    'city' => null,
                    'region' => null,
                    'latitude' => null,
                    'longitude' => null,
                    'timezone' => null,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('GeoLocation: country.is lookup failed', [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->emptyResult();
    }

    /**
     * Lookup using ip-api.com (more detailed, 45 req/min free)
     */
    private function lookupIpApi(string $ip): array
    {
        try {
            $response = Http::timeout(3)
                ->retry(2, 100)
                ->get("http://ip-api.com/json/{$ip}", [
                    'fields' => 'status,country,countryCode,region,regionName,city,lat,lon,timezone',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (($data['status'] ?? '') === 'success') {
                    return [
                        'country' => $data['countryCode'] ?? null,
                        'country_name' => $data['country'] ?? null,
                        'city' => $data['city'] ?? null,
                        'region' => $data['regionName'] ?? null,
                        'latitude' => $data['lat'] ?? null,
                        'longitude' => $data['lon'] ?? null,
                        'timezone' => $data['timezone'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('GeoLocation: ip-api.com lookup failed', [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->emptyResult();
    }

    /**
     * Get country name from country code
     */
    private function getCountryName(?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        return self::COUNTRY_NAMES[strtoupper($code)] ?? $code;
    }

    /**
     * Check if IP is private/local
     */
    private function isPrivateIp(string $ip): bool
    {
        // Check for localhost
        if ($ip === '127.0.0.1' || $ip === '::1' || $ip === 'localhost') {
            return true;
        }

        // Check for private ranges
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }

    /**
     * Return empty result array
     */
    private function emptyResult(): array
    {
        return [
            'country' => null,
            'country_name' => null,
            'city' => null,
            'region' => null,
            'latitude' => null,
            'longitude' => null,
            'timezone' => null,
        ];
    }

    /**
     * Get location summary string
     */
    public function getLocationSummary(string $ip): string
    {
        $data = $this->lookup($ip);

        $parts = array_filter([
            $data['city'],
            $data['region'],
            $data['country_name'],
        ]);

        return implode(', ', $parts) ?: 'Unknown';
    }
}
