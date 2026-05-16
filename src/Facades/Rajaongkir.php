<?php

namespace Nicxonsolutions\Rajaongkir\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array couriers(string $zone = 'all', ?string $accountType = null)
 * @method static array|null courier(string $filter = 'all', string $zone = 'all', ?string $accountType = null)
 * @method static array searchDomesticDestination(string $query)
 * @method static array searchInternationalDestination(string $query)
 * @method static array provinces()
 * @method static array|null province(int|string $provinceId)
 * @method static array cities(int|string $provinceId)
 * @method static array|null city(int|string $provinceId, int|string $cityId)
 * @method static array|null findCity(int|string $cityId)
 * @method static array districts(int|string $cityId)
 * @method static array|null district(int|string $cityId, int|string $districtId)
 * @method static array subDistricts(int|string $districtId)
 * @method static array|null subDistrict(int|string $districtId, int|string $subDistrictId)
 * @method static array calculateDomestic(array $params)
 * @method static array calculateDistrictDomestic(array $params)
 * @method static array calculateInternational(array $params)
 * @method static array calculate(string $zone, array $params)
 * @method static array trackWaybill(string $awb, string $courier, int|string|null $lastPhoneNumber = null)
 * @method static array quote(array $cart, array $destination, array $options = [])
 * @method static bool validateApiKey()
 *
 * @see \Nicxonsolutions\Rajaongkir\Rajaongkir
 */
class Rajaongkir extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Nicxonsolutions\Rajaongkir\Rajaongkir::class;
    }
}
