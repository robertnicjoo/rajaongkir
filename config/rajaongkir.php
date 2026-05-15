<?php

return [
    'base_url' => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api'),
    'api_key' => env('RAJAONGKIR_API_KEY'),
    'account_type' => env('RAJAONGKIR_ACCOUNT_TYPE', 'starter'),
    'origin' => env('RAJAONGKIR_ORIGIN'),
    'origin_label' => env('RAJAONGKIR_ORIGIN_LABEL'),
    'timeout' => (int) env('RAJAONGKIR_TIMEOUT', 10),
    'show_eta' => (bool) env('RAJAONGKIR_SHOW_ETA', true),
    'base_weight' => (int) env('RAJAONGKIR_BASE_WEIGHT', 0),
    'sort_shipping' => env('RAJAONGKIR_SORT_SHIPPING', 'no'),
    'routes' => [
        'enabled' => (bool) env('RAJAONGKIR_ROUTES_ENABLED', false),
        'prefix' => env('RAJAONGKIR_ROUTES_PREFIX', 'api/rajaongkir'),
        'middleware' => ['api'],
    ],
    'selected_couriers' => [
        'domestic' => [
            'jne' => ['CTC', 'CTCYES', 'CTCSPS', 'OKE', 'REG', 'YES', 'JTR<130', 'JTR>130', 'JTR>200', 'JTR>2000'],
            'jnt' => ['EZ'],
            'pos' => ['Pos Reguler', 'Pos Nextday', 'PAKETPOS DANGEROUS GOODS', 'PAKETPOS VALUABLE GOODS', 'Pos Sameday', 'POS KARGO'],
            'sicepat' => ['REG', 'BEST', 'GOKIL', 'SIUNT'],
            'tiki' => ['ECO', 'HDS', 'ONS', 'REG', 'SDS', 'TRC'],
        ],
        'international' => [
            'jne' => ['INTL Service'],
            'pos' => ['PAKETPOS BIASA LN', 'EMS BARANG', 'ePacket LP APP', 'POS EKSPOR', 'PAKETPOS CEPAT LN', 'R LN'],
            'tiki' => ['WDX', 'WPX'],
        ],
    ],
];
