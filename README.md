# RajaOngkir for Laravel

Laravel package for RajaOngkir/Komerce destination lookup and shipping cost calculation.

This package provides a Laravel-native API client, facade, optional API routes, courier metadata, and helpers for domestic and international shipping rates.

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

Use the facade:

```php
use Nicxonsolutions\Rajaongkir\Facades\Rajaongkir;

$destinations = Rajaongkir::searchDomesticDestination('Jakarta');
```

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
POST /api/rajaongkir/costs/domestic
POST /api/rajaongkir/costs/international
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

## Production Notes

- Keep `RAJAONGKIR_API_KEY` private and never expose it in frontend JavaScript.
- Use your own controller endpoints for checkout flows when you need authentication, rate limiting, cart validation, or custom business rules.
- Cache destination search results if your checkout receives heavy traffic.
- Re-run `php artisan config:cache` during deployment if your application uses cached config.

## License

GPL-2.0-or-later.

## Support

- Issues: https://github.com/robertnicjoo/rajaongkir/issues
- Source: https://github.com/robertnicjoo/rajaongkir
