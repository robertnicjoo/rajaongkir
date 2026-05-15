<?php

namespace Nicxonsolutions\Rajaongkir\Support;

class Couriers
{
    public static function all(): array
    {
        return require __DIR__ . '/../../config/couriers.php';
    }

    public static function forZone(string $zone, string $accountType = 'all'): array
    {
        return array_filter(self::all(), function (array $courier) use ($zone, $accountType) {
            $servicesKey = $zone === 'international' ? 'services_international' : 'services_domestic';
            $accountKey = $zone === 'international' ? 'account_international' : 'account_domestic';

            if (empty($courier[$servicesKey])) {
                return false;
            }

            return $accountType === 'all' || in_array($accountType, $courier[$accountKey], true);
        });
    }

    public static function findByResponseCode(string $code): ?array
    {
        foreach (self::all() as $courier) {
            if ($courier['code'] === $code || $courier['response_code'] === $code) {
                return $courier;
            }
        }

        return null;
    }
}
