<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Collection;

class Helpers
{
    static function averageArrayKey($array, $key)
    {
        return ($array) ? array_sum(array_map(function ($item) use ($key) {
            return $item[$key];
        }, $array)) / count($array) : 0;
    }
}
