<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

abstract class Controller
{
    protected function generateId(string $table, string $column, string $prefix): string
    {
        $existing = DB::table($table)->where($column, 'like', $prefix.'%')->pluck($column);

        $maxSuffix = $existing
            ->map(fn ($id) => (int) substr((string) $id, strlen($prefix)))
            ->max();

        $next = ((int) $maxSuffix) + 1;
        $width = 10 - strlen($prefix);

        for ($i = 0; $i < 100; $i++) {
            $candidate = $prefix.str_pad((string) $next, $width, '0', STR_PAD_LEFT);

            if (strlen($candidate) <= 10 && ! $existing->contains($candidate)) {
                return $candidate;
            }

            $next++;
        }

        return substr($prefix.substr((string) time(), -$width), 0, 10);
    }
}
