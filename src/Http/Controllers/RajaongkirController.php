<?php

namespace Nicxonsolutions\Rajaongkir\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nicxonsolutions\Rajaongkir\Rajaongkir;

class RajaongkirController extends Controller
{
    public function __construct(private readonly Rajaongkir $rajaongkir)
    {
    }

    public function domesticDestinations(Request $request): JsonResponse
    {
        $data = $request->validate(['search' => ['required', 'string', 'min:2']]);

        return response()->json([
            'data' => $this->rajaongkir->searchDomesticDestination($data['search']),
        ]);
    }

    public function internationalDestinations(Request $request): JsonResponse
    {
        $data = $request->validate(['search' => ['required', 'string', 'min:2']]);

        return response()->json([
            'data' => $this->rajaongkir->searchInternationalDestination($data['search']),
        ]);
    }

    public function provinces(): JsonResponse
    {
        return response()->json([
            'data' => $this->rajaongkir->provinces(),
        ]);
    }

    public function cities(int|string $provinceId): JsonResponse
    {
        return response()->json([
            'data' => $this->rajaongkir->cities($provinceId),
        ]);
    }

    public function districts(int|string $cityId): JsonResponse
    {
        return response()->json([
            'data' => $this->rajaongkir->districts($cityId),
        ]);
    }

    public function subDistricts(int|string $districtId): JsonResponse
    {
        return response()->json([
            'data' => $this->rajaongkir->subDistricts($districtId),
        ]);
    }

    public function costs(string $zone, Request $request): JsonResponse
    {
        $data = $request->validate([
            'origin' => ['required'],
            'destination' => ['required'],
            'weight' => ['required', 'integer', 'min:1'],
            'courier' => ['required'],
            'length' => ['nullable', 'numeric'],
            'width' => ['nullable', 'numeric'],
            'height' => ['nullable', 'numeric'],
            'diameter' => ['nullable', 'numeric'],
        ]);

        return response()->json([
            'data' => $this->rajaongkir->calculate($zone, $data),
        ]);
    }

    public function districtDomesticCosts(Request $request): JsonResponse
    {
        $data = $request->validate([
            'origin' => ['required'],
            'destination' => ['required'],
            'weight' => ['required', 'integer', 'min:1'],
            'courier' => ['required'],
            'length' => ['nullable', 'numeric'],
            'width' => ['nullable', 'numeric'],
            'height' => ['nullable', 'numeric'],
            'diameter' => ['nullable', 'numeric'],
        ]);

        return response()->json([
            'data' => $this->rajaongkir->calculateDistrictDomestic($data),
        ]);
    }

    public function trackWaybill(Request $request): JsonResponse
    {
        $data = $request->validate([
            'awb' => ['required', 'string'],
            'courier' => ['required', 'string'],
            'last_phone_number' => ['nullable'],
        ]);

        return response()->json([
            'data' => $this->rajaongkir->trackWaybill(
                $data['awb'],
                $data['courier'],
                $data['last_phone_number'] ?? null
            ),
        ]);
    }
}
