<?php

namespace Nicxonsolutions\Rajaongkir\Facades;

use Illuminate\Support\Facades\Facade;

class Rajaongkir extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Nicxonsolutions\Rajaongkir\Rajaongkir::class;
    }
}
