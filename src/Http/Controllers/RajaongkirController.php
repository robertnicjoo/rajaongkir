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
}
