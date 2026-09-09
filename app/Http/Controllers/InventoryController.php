<?php

namespace App\Http\Controllers;

use App\Models\CentralStock;
use App\Models\Drugrequest;
use App\Models\Itemrequest;
use App\Models\Pharmaceutical;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

abstract class InventoryController extends Controller
{
    protected const URGENT_LIMIT = 8;

    public function index(Request $request): View
    {
        $class = $this->itemClass();

        $query = $this->filteredQuery($request);

        return view($this->viewPrefix().'.index', [
            'items' => $query->paginate(15)->withQueryString(),
            'counts' => $this->dashboardCounts(),
            'urgent' => $class::query()->needsRestock()->limit(self::URGENT_LIMIT)->get(),
            'suppliers' => Supplier::orderBy('Name')->get(),
            'search' => $request->query('search', ''),
            'status' => $request->query('status', ''),
        ]);
    }

    public function create(): View|RedirectResponse
    {
        if (! auth()->user()->isMedicalDirector()) {
            return redirect()->route('forbidden');
        }

        return view($this->viewPrefix().'.form', [
            'item' => new ($this->itemClass()),
            'suppliers' => Supplier::orderBy('Name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()->isMedicalDirector()) {
            return redirect()->route('forbidden');
        }

        $data = $this->validateItem($request);
        $code = $this->nextCode();
        $data[$this->codeColumn()] = $code;

        $item = $this->itemClass()::create($data);

        if ((int) ($data['QtyInStock'] ?? 0) > 0) {
            $this->recordMovement($item, (int) $data['QtyInStock'], __('Initial stock'));
        }

        return redirect()->route($this->routeName().'.index')
            ->with('status', __('Created :name.', ['name' => $item->Name]));
    }

    public function edit(string $code): View|RedirectResponse
    {
        if (! auth()->user()->isMedicalDirector()) {
            return redirect()->route('forbidden');
        }

        return view($this->viewPrefix().'.form', [
            'item' => $this->findItem($code),
            'suppliers' => Supplier::orderBy('Name')->get(),
        ]);
    }

    public function update(Request $request, string $code): RedirectResponse
    {
        if (! $request->user()->isMedicalDirector()) {
            return redirect()->route('forbidden');
        }

        $item = $this->findItem($code);
        $item->update($this->validateItem($request, $item));

        return redirect()->route($this->routeName().'.index')
            ->with('status', __('Updated :name.', ['name' => $item->Name]));
    }

    public function destroy(string $code): RedirectResponse
    {
        if (! auth()->user()->isMedicalDirector()) {
            return redirect()->route('forbidden');
        }

        $item = $this->findItem($code);

        try {
            DB::transaction(function () use ($item) {
                // For supplies / drugs the stock history and ward requisition lines are safe to clean;
                // keep the guard for medical history (Medications / allergies) which must stay.
                if ($item instanceof CentralStock) {
                    StockMovement::where('Item_No', $item->getKey())->delete();
                    Itemrequest::where('Item_No', $item->getKey())->delete();
                } elseif ($item instanceof Pharmaceutical) {
                    StockMovement::where('Drug_No', $item->getKey())->delete();
                    Drugrequest::where('Drug_No', $item->getKey())->delete();
                }

                $item->delete();
            });
        } catch (QueryException $e) {
            $code = $e->errorInfo[1] ?? null;
            $msg = $e->getMessage();
            // MySQL: 1451, SQLite: 19 with FOREIGN KEY message
            if ($code == 1451 || $code == 19 || str_contains($msg, 'FOREIGN KEY') || str_contains($msg, 'foreign key')) {
                return back()->with('error', __(':name is still referenced by other records and cannot be deleted.', ['name' => $item->Name]));
            }

            throw $e;
        }

        return redirect()->route($this->routeName().'.index')
            ->with('status', __('Deleted :name.', ['name' => $item->Name]));
    }

    public function restock(Request $request, string $code): RedirectResponse
    {
        if (! $request->user()->isMedicalDirector()) {
            return redirect()->route('forbidden');
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $item = $this->findItem($code);

        DB::transaction(function () use ($item, $validated): void {
            $item->update(['QtyInStock' => ($item->QtyInStock ?? 0) + $validated['quantity']]);
            $this->recordMovement($item, $validated['quantity'], $validated['note'] ?? null);
        });

        return back()->with('status', __('Restocked :name (+:qty).', ['name' => $item->Name, 'qty' => $validated['quantity']]));
    }

    public function adjust(Request $request, string $code): RedirectResponse
    {
        if (! $request->user()->isMedicalDirector()) {
            return redirect()->route('forbidden');
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['required', 'string', 'max:255'],
        ]);

        $item = $this->findItem($code);

        if (($item->QtyInStock ?? 0) < $validated['quantity']) {
            return back()->with('error', __('Cannot remove :qty — only :stock left in stock.', [
                'qty' => $validated['quantity'], 'stock' => $item->QtyInStock ?? 0,
            ]));
        }

        DB::transaction(function () use ($item, $validated): void {
            $item->update(['QtyInStock' => ($item->QtyInStock ?? 0) - $validated['quantity']]);
            $this->recordMovement($item, -$validated['quantity'], $validated['note']);
        });

        return back()->with('status', __('Adjusted :name (-:qty).', ['name' => $item->Name, 'qty' => $validated['quantity']]));
    }

    public function history(string $code): View
    {
        $item = $this->findItem($code);

        return view($this->viewPrefix().'.history', [
            'item' => $item,
            'movements' => $item->movements()->with('user')->latest('MoveDate')->paginate(20),
        ]);
    }

    abstract protected function itemClass(): string;

    abstract protected function viewPrefix(): string;

    abstract protected function routeName(): string;

    abstract protected function tableName(): string;

    abstract protected function codeColumn(): string;

    abstract protected function codePrefix(): string;

    abstract protected function searchColumns(): array;

    protected function validateItem(Request $request, ?Model $existing = null): array
    {
        return $request->validate([
            'Name' => ['required', 'string', 'max:100'],
            'Description' => ['nullable', 'string', 'max:255'],
            'QtyInStock' => ['required', 'integer', 'min:0'],
            'ReorderLvl' => ['required', 'integer', 'min:0'],
            'CostPerUnit' => ['nullable', 'numeric', 'min:0'],
            'Suppl_No' => ['nullable', 'exists:Supplier,Suppl_No'],
        ]);
    }

    protected function findItem(string $code): Model
    {
        return $this->itemClass()::query()->where($this->codeColumn(), $code)->firstOrFail();
    }

    protected function nextCode(): string
    {
        $prefix = $this->codePrefix();
        $column = $this->codeColumn();
        $existing = DB::table($this->tableName())->where($column, 'like', $prefix.'%')->pluck($column);

        $maxSuffix = (int) $existing
            ->map(fn ($id) => (int) substr((string) $id, strlen($prefix)))
            ->max();

        for ($next = $maxSuffix + 1; $next < $maxSuffix + 101; $next++) {
            $candidate = $prefix.str_pad((string) $next, max(2, strlen((string) $next)), '0', STR_PAD_LEFT);

            if (! $existing->contains($candidate)) {
                return $candidate;
            }
        }

        return substr($prefix.substr((string) time(), -8), 0, 10);
    }

    protected function recordMovement(Model $item, int $change, ?string $note): void
    {
        StockMovement::create([
            $item instanceof CentralStock ? 'Item_No' : 'Drug_No' => $item->{$this->codeColumn()},
            'QtyChange' => $change,
            'Note' => $note !== null ? substr($note, 0, 255) : null,
            'Moved_By' => auth()->id(),
            'MoveDate' => now(),
        ]);
    }

    protected function applyExtraFilters(Builder $query, Request $request): Builder
    {
        return $query;
    }

    protected function extraCounts(): array
    {
        return [];
    }

    private function filteredQuery(Request $request): Builder
    {
        /** @var Builder $query */
        $query = $this->itemClass()::query()->with('supplier');

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function (Builder $q) use ($search) {
                foreach ($this->searchColumns() as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        $status = $request->query('status');
        $class = $this->itemClass();
        if (in_array($status, [$class::STOCK_LOW, $class::STOCK_OUT, $class::STOCK_NORMAL], true)) {
            $query->stockStatus($status);
        }

        $query = $this->applyExtraFilters($query, $request);

        return $query->orderBy($this->codeColumn());
    }

    protected function dashboardCounts(): array
    {
        $class = $this->itemClass();
        $counts = $class::stockCounts();

        return array_merge($counts, $this->extraCounts());
    }
}
