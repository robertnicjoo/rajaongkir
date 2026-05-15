<?php

namespace Nicxonsolutions\Rajaongkir;

use Nicxonsolutions\Rajaongkir\Api\Client;
use Nicxonsolutions\Rajaongkir\Exceptions\RajaongkirException;
use Nicxonsolutions\Rajaongkir\Support\Couriers;
use Nicxonsolutions\Rajaongkir\Support\ParsesEta;

class Rajaongkir
{
    public function __construct(
        private readonly Client $client,
        private readonly array $config = []
    ) {
    }

    public function couriers(string $zone = 'all', ?string $accountType = null): array
    {
        if ($zone === 'all') {
            return Couriers::all();
        }

        return Couriers::forZone($zone, $accountType ?? $this->accountType());
    }

    public function searchDomesticDestination(string $query): array
    {
        return array_map(fn (array $item) => [
            'id' => $item['id'] ?? null,
            'text' => $item['label'] ?? null,
            'label' => $item['label'] ?? null,
            'subdistrict_name' => $item['subdistrict_name'] ?? null,
            'district_name' => $item['district_name'] ?? null,
            'city_name' => $item['city_name'] ?? null,
            'province_name' => $item['province_name'] ?? null,
            'zip_code' => $item['zip_code'] ?? null,
        ], $this->client->get('/v1/destination/domestic-destination', ['search' => $query]));
    }

    public function searchInternationalDestination(string $query): array
    {
        return array_map(fn (array $item) => [
            'id' => $item['country_id'] ?? null,
            'text' => $item['country_name'] ?? null,
            'country_id' => $item['country_id'] ?? null,
            'country_name' => $item['country_name'] ?? null,
        ], $this->client->get('/v1/destination/international-destination', ['search' => $query]));
    }

    public function provinces(): array
    {
        return array_map(
            fn (array $item) => $this->normalizeLocationItem($item, ['province_id', 'id'], ['province_name', 'province', 'name']),
            $this->client->get('/v1/destination/province')
        );
    }

    public function cities(int|string $provinceId): array
    {
        return array_map(
            fn (array $item) => $this->normalizeLocationItem($item, ['city_id', 'id'], ['city_name', 'city', 'name']),
            $this->client->get("/v1/destination/city/{$provinceId}")
        );
    }

    public function districts(int|string $cityId): array
    {
        return array_map(
            fn (array $item) => $this->normalizeLocationItem($item, ['district_id', 'id'], ['district_name', 'district', 'name']),
            $this->client->get("/v1/destination/district/{$cityId}")
        );
    }

    public function subDistricts(int|string $districtId): array
    {
        return array_map(
            fn (array $item) => $this->normalizeLocationItem($item, ['subdistrict_id', 'sub_district_id', 'id'], ['subdistrict_name', 'sub_district_name', 'sub_district', 'name']),
            $this->client->get("/v1/destination/sub-district/{$districtId}")
        );
    }

    public function calculateDomestic(array $params): array
    {
        return $this->calculate('domestic', $params);
    }

    public function calculateDistrictDomestic(array $params): array
    {
        return $this->calculateUsingEndpoint('domestic', '/v1/calculate/district/domestic-cost', $params);
    }

    public function calculateInternational(array $params): array
    {
        return $this->calculate('international', $params);
    }

    public function trackWaybill(string $awb, string $courier, int|string|null $lastPhoneNumber = null): array
    {
        $params = [
            'awb' => $awb,
            'courier' => $courier,
        ];

        if ($lastPhoneNumber !== null && $lastPhoneNumber !== '') {
            $params['last_phone_number'] = (string) $lastPhoneNumber;
        }

        return $this->client->post('/v1/track/waybill', $params);
    }

    public function calculate(string $zone, array $params): array
    {
        if (!in_array($zone, ['domestic', 'international'], true)) {
            throw new RajaongkirException('Shipping zone must be domestic or international.');
        }

        $params = $this->normalizeParams($zone, $params);
        $endpoint = $zone === 'international'
            ? '/v1/calculate/international-cost'
            : '/v1/calculate/domestic-cost';

        return $this->calculateUsingEndpoint($zone, $endpoint, $params);
    }

    public function quote(array $cart, array $destination, array $options = []): array
    {
        $country = strtoupper($destination['country'] ?? 'ID');
        $zone = $country === 'ID' ? 'domestic' : 'international';
        $weight = max((int) ($cart['weight'] ?? 0), (int) ($this->config['base_weight'] ?? 0));

        $params = array_merge([
            'origin' => $options['origin'] ?? $this->config['origin'] ?? null,
            'destination' => $destination['id'] ?? $destination['destination'] ?? null,
            'weight' => $weight,
            'courier' => array_keys($this->selectedCouriers($zone)),
        ], $options['params'] ?? []);

        return $this->calculate($zone, $params);
    }

    public function validateApiKey(): bool
    {
        return count($this->searchDomesticDestination('KIARA')) > 0;
    }

    private function normalizeParams(string $zone, array $params): array
    {
        foreach (['origin', 'destination', 'weight', 'courier'] as $key) {
            if (!isset($params[$key]) || $params[$key] === '' || $params[$key] === []) {
                throw new RajaongkirException("Missing required shipping parameter: {$key}.");
            }
        }

        if (!is_array($params['courier'])) {
            $params['courier'] = explode(':', (string) $params['courier']);
        }

        if ($zone === 'domestic') {
            $params['origin'] = (string) $params['origin'];
            $params['destination'] = (string) $params['destination'];
        }

        $params['weight'] = (int) $params['weight'];

        return $params;
    }

    private function calculateUsingEndpoint(string $zone, string $endpoint, array $params): array
    {
        $params = $this->normalizeParams($zone, $params);

        $raw = [];
        foreach (array_chunk((array) $params['courier'], 7) as $courierChunk) {
            $raw = array_merge($raw, $this->client->post($endpoint, array_merge($params, [
                'courier' => implode(':', $courierChunk),
            ])));
        }

        $parsed = $this->parseRates($zone, $raw);
        $parsed = $this->filterSelectedServices($zone, $parsed);
        $parsed = $this->sortRates($parsed, $this->config['sort_shipping'] ?? 'no');

        return ['parsed' => array_values($parsed), 'raw' => $raw];
    }

    private function normalizeLocationItem(array $item, array $idKeys, array $nameKeys): array
    {
        $id = $this->firstValue($item, $idKeys);
        $name = $this->firstValue($item, $nameKeys);

        return array_merge($item, [
            'id' => $id,
            'text' => $name,
            'name' => $name,
        ]);
    }

    private function firstValue(array $item, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $item) && $item[$key] !== null && $item[$key] !== '') {
                return $item[$key];
            }
        }

        return null;
    }

    private function parseRates(string $zone, array $raw): array
    {
        $rates = [];

        foreach ($raw as $result) {
            if (!is_array($result) || empty($result['code']) || empty($result['cost']) || empty($result['service'])) {
                continue;
            }

            $courier = Couriers::findByResponseCode((string) $result['code']);
            if (!$courier) {
                continue;
            }

            $services = $zone === 'international'
                ? $courier['services_international']
                : $courier['services_domestic'];

            $service = (string) $result['service'];
            $description = $result['description'] ?? $services[$service] ?? $service;

            $rates[] = [
                'courier' => $courier['code'],
                'courier_label' => $courier['label'],
                'service' => $service,
                'description' => $description,
                'cost' => (int) $result['cost'],
                'currency' => $result['currency'] ?? 'IDR',
                'etd' => ParsesEta::parse($result['etd'] ?? ''),
                'note' => $result['note'] ?? '',
                'cost_conversion' => $result['cost_conversion'] ?? false,
            ];
        }

        return $rates;
    }

    private function filterSelectedServices(string $zone, array $rates): array
    {
        $selected = $this->selectedCouriers($zone);

        if (!$selected) {
            return $rates;
        }

        return array_filter($rates, function (array $rate) use ($selected) {
            $services = $selected[$rate['courier']] ?? [];

            return $services === [] || in_array($rate['service'], $services, true);
        });
    }

    private function selectedCouriers(string $zone): array
    {
        return $this->config['selected_couriers'][$zone] ?? [];
    }

    private function accountType(): string
    {
        return $this->config['account_type'] ?? 'starter';
    }

    private function sortRates(array $rates, string $sort): array
    {
        match ($sort) {
            'cost' => usort($rates, fn ($a, $b) => $a['cost'] <=> $b['cost']),
            'cost_desc' => usort($rates, fn ($a, $b) => $b['cost'] <=> $a['cost']),
            'name' => usort($rates, fn ($a, $b) => strcmp($a['courier_label'] . $a['service'], $b['courier_label'] . $b['service'])),
            'name_desc' => usort($rates, fn ($a, $b) => strcmp($b['courier_label'] . $b['service'], $a['courier_label'] . $a['service'])),
            default => null,
        };

        return $rates;
    }
}
