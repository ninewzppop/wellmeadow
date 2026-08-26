<?php

namespace App\Http\Controllers;

use App\Models\CentralStock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class StockController extends InventoryController
{
    protected function itemClass(): string
    {
        return CentralStock::class;
    }

    protected function viewPrefix(): string
    {
        return 'stocks';
    }

    protected function routeName(): string
    {
        return 'stock';
    }

    protected function tableName(): string
    {
        return 'CentralStock';
    }

    protected function codeColumn(): string
    {
        return 'Item_No';
    }

    protected function codePrefix(): string
    {
        return 'IT';
    }

    protected function searchColumns(): array
    {
        return ['Item_No', 'Name', 'ItemType', 'Description'];
    }

    protected function validateItem(Request $request, $existing = null): array
    {
        return array_merge(parent::validateItem($request, $existing), $request->validate([
            'ItemType' => ['required', 'in:'.implode(',', [CentralStock::TYPE_SURGICAL, CentralStock::TYPE_NON_SURGICAL])],
        ]));
    }

    protected function applyExtraFilters(Builder $query, Request $request): Builder
    {
        $type = $request->query('type');
        if (in_array($type, [CentralStock::TYPE_SURGICAL, CentralStock::TYPE_NON_SURGICAL], true)) {
            $query->where('ItemType', $type);
        }

        return $query;
    }
}
