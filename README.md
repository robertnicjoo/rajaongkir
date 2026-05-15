# Rajaongkir Laravel

Laravel package converted from the WooCommerce Cekongkir/RajaOngkir plugin.

## Install in a Laravel App

Add this package as a path repository in your Laravel app `composer.json`:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../rajaongkir-laravel"
    }
  ],
  "require": {
    "nicxonsolutions/rajaongkir": "*"
  }
}
```

Then run:

```bash
composer update nicxonsolutions/rajaongkir
php artisan vendor:publish --tag=rajaongkir-config
```

Set your environment:

```dotenv
RAJAONGKIR_API_KEY=your-api-key
RAJAONGKIR_ORIGIN=31555
RAJAONGKIR_ACCOUNT_TYPE=starter
```

## Usage

```php
use Nicxonsolutions\Rajaongkir\Facades\Rajaongkir;

$destinations = Rajaongkir::searchDomesticDestination('Jakarta');

$rates = Rajaongkir::calculateDomestic([
    'origin' => '31555',
    'destination' => '31711',
    'weight' => 1200,
    'courier' => ['jne', 'jnt', 'pos'],
]);
```

To enable optional package routes:

```dotenv
RAJAONGKIR_ROUTES_ENABLED=true
RAJAONGKIR_ROUTES_PREFIX=api/rajaongkir
```

Routes:

- `GET /api/rajaongkir/destinations/domestic?search=Jakarta`
- `GET /api/rajaongkir/destinations/international?search=Singapore`
- `POST /api/rajaongkir/costs/domestic`
- `POST /api/rajaongkir/costs/international`

## Notes

This is a Laravel-native package, not a WooCommerce compatibility layer. Cart integration should be wired into your Laravel checkout flow using `quote()` or `calculateDomestic()` / `calculateInternational()`.
