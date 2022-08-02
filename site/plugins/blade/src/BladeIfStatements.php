<?php

namespace Leitsch\Blade;

use Illuminate\Support\Facades\Blade;

class BladeIfStatements
{
    public static function register()
    {
        foreach (option('leitsch.blade.ifs', []) as $statement => $callback) {
            Blade::if($statement, $callback);
        }
    }
}
