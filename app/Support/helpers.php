<?php

use Illuminate\Http\Request;
use Illuminate\Support\Str;

if (!function_exists('extractResourceName')) {
    function extractResourceName(Request $request): string {
        $segments = $request->segments();

        if (isset($segments[1])) {
            return Str::singular(str_replace('-', ' ', $segments[1]));
        }

        if (isset($segments[0])) {
            return Str::singular(str_replace('-', ' ', $segments[0]));
        }

        return 'resource';
    }
}

if (!function_exists('determineAction')) {
    function determineAction(string $method): string {
        return match ($method) {
            'GET' => 'index',
            'POST' => 'store',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'destroy',
            default => 'operation',
        };
    }
}