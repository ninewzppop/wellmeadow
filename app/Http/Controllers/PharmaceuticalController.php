<?php

namespace App\Http\Controllers;

use App\Models\Pharmaceutical;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PharmaceuticalController extends InventoryController
{
    protected function itemClass(): string
    {
        return Pharmaceutical::class;
    }

    protected function viewPrefix(): string
    {
        return 'pharmaceuticals';
    }

    protected function routeName(): string
    {
        return 'pharmacy';
    }

    protected function tableName(): string
    {
        return 'Pharmaceutical';
    }

    protected function codeColumn(): string
    {
        return 'Drug_No';
    }

    protected function codePrefix(): string
    {
        return 'DR';
    }

    protected function searchColumns(): array
    {
        return ['Drug_No', 'Name', 'Description'];
    }

    protected function validateItem(Request $request, $existing = null): array
    {
        return array_merge(parent::validateItem($request, $existing), $request->validate([
            'Dosage' => ['nullable', 'string', 'max:30'],
            'AdminMethod' => ['nullable', 'string', 'max:30'],
            'ExpiryDate' => ['nullable', 'date'],
        ]));
    }

    protected function applyExtraFilters(Builder $query, Request $request): Builder
    {
        if (in_array($request->query('expiry'), [Pharmaceutical::EXPIRY_NEAR, Pharmaceutical::EXPIRY_EXPIRED, Pharmaceutical::EXPIRY_OK], true)) {
            $query->expiryStatus($request->query('expiry'));
        }

        return $query;
    }

    protected function extraCounts(): array
    {
        $class = $this->itemClass();

        return [
            'expired' => (clone $class::query())->expiryStatus(Pharmaceutical::EXPIRY_EXPIRED)->count(),
        ];
    }
}
