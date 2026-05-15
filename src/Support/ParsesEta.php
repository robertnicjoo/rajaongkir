<?php

namespace Nicxonsolutions\Rajaongkir\Support;

class ParsesEta
{
    public static function parse(null|string|int $etd): string
    {
        $value = trim((string) $etd);

        if ($value === '') {
            return '';
        }

        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim(str_ireplace(['hari', 'day', 'days'], '', $value));
    }
}
