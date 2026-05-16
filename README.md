# RajaOngkir Komerce API for Laravel

Laravel package for the latest RajaOngkir Komerce API, including the current 2026 RajaOngkir API endpoints for destination lookup, shipping cost calculation, district-level shipping costs, and AWB tracking.

This package provides a Laravel-native API client, facade, optional API routes, courier metadata, and helpers for domestic and international shipping workflows.

Built and maintained by [PT. Nicxon International Solutions](https://nicxonsolutions.com).

## Table of Contents

- [Why This Package Exists](#why-this-package-exists)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Basic Usage](#basic-usage)
- [Search Destinations](#search-destinations)
- [Calculate Shipping Costs](#calculate-shipping-costs)
- [Track Shipments](#track-shipments)
- [Checkout Quote Helper](#checkout-quote-helper)
- [Optional API Routes](#optional-api-routes)
- [API Key Validation](#api-key-validation)
- [Error Handling](#error-handling)
- [Courier Metadata](#courier-metadata)
- [Production Notes](#production-notes)
- [License](#license)
- [Support](#support)

## Why This Package Exists

RajaOngkir is widely used in Indonesian ecommerce, but the Laravel ecosystem has lacked an up-to-date package for the current RajaOngkir Komerce API. RajaOngkir itself does not provide an official Laravel package, and many existing community packages target older API versions or outdated endpoint structures.

I at [PT. Nicxon International Solutions](https://nicxonsolutions.com) created this package to fill that gap: a production-ready Laravel integration for the modern RajaOngkir Komerce API, with support for the latest destination hierarchy, domestic and international rates, district-level shipping cost calculation, and package tracking.

All rights reserved by PT. Nicxon International Solutions.

## Requirements

- PHP `^8.2`
- Laravel / Illuminate `6.x` through `13.x`
- RajaOngkir/Komerce API key

## Installation

Install the package with Composer:

```bash
composer require nicxonsolutions/rajaongkir
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=rajaongkir-config
```

Add your RajaOngkir credentials and defaults to `.env`:

```dotenv
RAJAONGKIR_API_KEY=your-api-key
RAJAONGKIR_BASE_URL=https://rajaongkir.komerce.id/api
RAJAONGKIR_ACCOUNT_TYPE=starter
RAJAONGKIR_ORIGIN=31555
RAJAONGKIR_ORIGIN_LABEL="Your Store Origin"
RAJAONGKIR_TIMEOUT=10
```

Clear cached config when deploying or changing environment values:

```bash
php artisan config:clear
```

## Configuration

The published config file is available at `config/rajaongkir.php`.

Important options:

```php
return [
    'api_key' => env('RAJAONGKIR_API_KEY'),
    'base_url' => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api'),
    'account_type' => env('RAJAONGKIR_ACCOUNT_TYPE', 'starter'),
    'origin' => env('RAJAONGKIR_ORIGIN'),
    'base_weight' => (int) env('RAJAONGKIR_BASE_WEIGHT', 0),
    'sort_shipping' => env('RAJAONGKIR_SORT_SHIPPING', 'no'),
];
```

Supported `sort_shipping` values:

- `no`
- `cost`
- `cost_desc`
- `name`
- `name_desc`

Enabled couriers and services are configured in `selected_couriers`.

## Basic Usage

Use the facade when calling methods statically:

```php
use Nicxonsolutions\Rajaongkir\Facades\Rajaongkir;

$destinations = Rajaongkir::searchDomesticDestination('Jakarta');
```

Make sure you import `Nicxonsolutions\Rajaongkir\Facades\Rajaongkir` for static-style calls. If you import `Nicxonsolutions\Rajaongkir\Rajaongkir`, use dependency injection instead.

Or inject the service:

```php
use Nicxonsolutions\Rajaongkir\Rajaongkir;

class CheckoutController
{
    public function shipping(Rajaongkir $rajaongkir)
    {
        return $rajaongkir->searchDomesticDestination('Bandung');
    }
}
```

## Search Destinations

### Search-Based Lookup

Domestic destination search:

```php
$destinations = Rajaongkir::searchDomesticDestination('Jakarta');
```

Example response item:

```php
[
    'id' => 31711,
    'text' => 'Gambir, Jakarta Pusat, DKI Jakarta 10110',
    'label' => 'Gambir, Jakarta Pusat, DKI Jakarta 10110',
    'subdistrict_name' => 'Gambir',
    'district_name' => 'Gambir',
    'city_name' => 'Jakarta Pusat',
    'province_name' => 'DKI Jakarta',
    'zip_code' => '10110',
]
```

International destination search:

```php
$countries = Rajaongkir::searchInternationalDestination('Singapore');
```

### Province, City, District, and Sub-District Lookup

You can also use the structured Indonesian location hierarchy endpoints.

Get all provinces:

```php
$provinces = Rajaongkir::provinces();
```

Get one province by province ID:

```php
$province = Rajaongkir::province($provinceId);
```

Get cities by province ID:

```php
$cities = Rajaongkir::cities($provinceId);
```

Get one city by province ID and city ID:

```php
$city = Rajaongkir::city($provinceId, $cityId);
```

If you only have the city ID, you can search all provinces:

```php
$city = Rajaongkir::findCity($cityId);
```

Get districts by city ID:

```php
$districts = Rajaongkir::districts($cityId);
```

Get one district by city ID and district ID:

```php
$district = Rajaongkir::district($cityId, $districtId);
```

Get sub-districts by district ID:

```php
$subDistricts = Rajaongkir::subDistricts($districtId);
```

Get one sub-district by district ID and sub-district ID:

```php
$subDistrict = Rajaongkir::subDistrict($districtId, $subDistrictId);
```

The package normalizes each item with common keys:

```php
[
    'id' => 1,
    'text' => 'Bali',
    'name' => 'Bali',
    // Original API fields are preserved as well.
]
```

## Calculate Shipping Costs

Domestic rates:

```php
$rates = Rajaongkir::calculateDomestic([
    'origin' => '31555',
    'destination' => '31711',
    'weight' => 1200,
    'courier' => ['jne', 'jnt', 'pos'],
]);
```

International rates:

```php
$rates = Rajaongkir::calculateInternational([
    'origin' => '31555',
    'destination' => '702',
    'weight' => 1200,
    'courier' => ['jne', 'pos'],
]);
```

District-level domestic rates:

```php
$rates = Rajaongkir::calculateDistrictDomestic([
    'origin' => '4570',
    'destination' => '4571',
    'weight' => 1200,
    'courier' => ['jne', 'jnt', 'pos'],
]);
```

Example parsed rate:

```php
[
    'courier' => 'jne',
    'courier_label' => 'JNE',
    'service' => 'REG',
    'description' => 'Layanan Reguler',
    'cost' => 18000,
    'currency' => 'IDR',
    'etd' => '2-3',
    'note' => '',
    'cost_conversion' => false,
]
```

The calculation methods return:

```php
[
    'parsed' => [...],
    'raw' => [...],
]
```

## Track Shipments

Track a shipment by AWB number and courier:

```php
$tracking = Rajaongkir::trackWaybill(
    awb: 'YOUR_AWB_NUMBER',
    courier: 'jne',
);
```

Some couriers require the last 5 digits of the recipient phone number for validation, including JNE:

```php
$tracking = Rajaongkir::trackWaybill(
    awb: 'YOUR_AWB_NUMBER',
    courier: 'jne',
    lastPhoneNumber: '12345',
);
```

The API response contains shipment summary, details, delivery status, and manifest history when available:

```php
[
    'delivered' => true,
    'summary' => [...],
    'details' => [...],
    'delivery_status' => [...],
    'manifest' => [...],
]
```

## Checkout Quote Helper

For application carts, you can use `quote()` and pass the cart weight plus destination data:

```php
$quote = Rajaongkir::quote(
    cart: ['weight' => 1200],
    destination: [
        'country' => 'ID',
        'id' => '31711',
    ],
);
```

You may override default origin or request parameters:

```php
$quote = Rajaongkir::quote(
    cart: ['weight' => 1200],
    destination: ['country' => 'ID', 'id' => '31711'],
    options: [
        'origin' => '31555',
        'params' => [
            'courier' => ['jne', 'sicepat'],
        ],
    ],
);
```

## Optional API Routes

Package routes are disabled by default. Enable them in `.env`:

```dotenv
RAJAONGKIR_ROUTES_ENABLED=true
RAJAONGKIR_ROUTES_PREFIX=api/rajaongkir
```

Available routes:

```text
GET  /api/rajaongkir/destinations/domestic?search=Jakarta
GET  /api/rajaongkir/destinations/international?search=Singapore
GET  /api/rajaongkir/destinations/provinces
GET  /api/rajaongkir/destinations/provinces/{provinceId}
GET  /api/rajaongkir/destinations/cities/{provinceId}
GET  /api/rajaongkir/destinations/cities/{provinceId}/{cityId}
GET  /api/rajaongkir/destinations/districts/{cityId}
GET  /api/rajaongkir/destinations/districts/{cityId}/{districtId}
GET  /api/rajaongkir/destinations/sub-districts/{districtId}
GET  /api/rajaongkir/destinations/sub-districts/{districtId}/{subDistrictId}
POST /api/rajaongkir/costs/domestic
POST /api/rajaongkir/costs/international
POST /api/rajaongkir/costs/district/domestic
POST /api/rajaongkir/track/waybill
```

Cost request body:

```json
{
  "origin": "31555",
  "destination": "31711",
  "weight": 1200,
  "courier": ["jne", "jnt", "pos"]
}
```

District domestic cost requests use the same body shape, but `origin` and `destination` should be district IDs.

Tracking request body:

```json
{
  "awb": "YOUR_AWB_NUMBER",
  "courier": "jne",
  "last_phone_number": "12345"
}
```

`last_phone_number` is optional for the package request, but certain couriers may require it.

## API Key Validation

You can perform a simple API key check:

```php
$valid = Rajaongkir::validateApiKey();
```

This performs a small domestic destination search request.

## Error Handling

The package throws `Nicxonsolutions\Rajaongkir\Exceptions\RajaongkirException` for missing configuration, invalid parameters, or API errors.

```php
use Nicxonsolutions\Rajaongkir\Exceptions\RajaongkirException;
use Nicxonsolutions\Rajaongkir\Facades\Rajaongkir;

try {
    $rates = Rajaongkir::calculateDomestic([...]);
} catch (RajaongkirException $e) {
    report($e);

    return back()->withErrors([
        'shipping' => $e->getMessage(),
    ]);
}
```

## Courier Metadata

Courier and service metadata is stored in `config/couriers.php`. The package includes domestic and international services ported from the original WooCommerce plugin.

Get available couriers:

```php
$all = Rajaongkir::couriers();
$domestic = Rajaongkir::couriers('domestic');
$international = Rajaongkir::couriers('international');
```

Use `courier()` when you want to fetch all couriers, one courier, or couriers matching a service code:

```php
$all = Rajaongkir::courier('all');
$jne = Rajaongkir::courier('jne');
$regularServices = Rajaongkir::courier('REG');
```

When the filter matches a courier code, response code, or courier label, one courier is returned. When the filter matches a service code or service label, matching couriers are returned with a `matched_services` key.

## Production Notes

- Keep `RAJAONGKIR_API_KEY` private and never expose it in frontend JavaScript.
- Use your own controller endpoints for checkout flows when you need authentication, rate limiting, cart validation, or custom business rules.
- Cache destination search results if your checkout receives heavy traffic.
- Re-run `php artisan config:cache` during deployment if your application uses cached config.

## License

GPL-2.0-or-later.

Copyright (c) PT. Nicxon International Solutions. All rights reserved.

## Support

- Website: https://nicxonsolutions.com
- Issues: https://github.com/robertnicjoo/rajaongkir/issues
- Source: https://github.com/robertnicjoo/rajaongkir
