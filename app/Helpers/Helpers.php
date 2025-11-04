<?php

use Illuminate\Support\Str;

if (!function_exists('active')) {
    function active($names) {
        $route = request()->route() ? request()->route()->getName() : '';
        foreach ((array)$names as $n) {
            if (Str::startsWith($route, $n) || $route === $n) return 'active';
        }
        return '';
    }
}
