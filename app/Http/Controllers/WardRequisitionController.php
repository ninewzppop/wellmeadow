<?php

namespace App\Http\Controllers;

use App\Models\CentralStock;
use App\Models\Drugrequest;
use App\Models\Itemrequest;
use App\Models\Pharmaceutical;
use App\Models\Stf;
use App\Models\StockMovement;
use App\Models\Wardrequisition;
use App\Models\Wd;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WardRequisitionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Wardrequisition::with(['ward', 'requester', 'itemRequests.item', 'drugRequests.drug'])
            ->whereIn('status', [Wardrequisition::STATUS_PENDING, Wardrequisition::STATUS_APPROVED])
            ->orderBy('DateOrd')
            ->orderBy('Wd_Req_No');

        if ($request->filled('ward')) {
            $query->where('Wd_No', $request->ward);
        }
        if ($request->filled('status') && in_array($request->status, [Wardrequisition::STATUS_PENDING, Wardrequisition::STATUS_APPROVED], true)) {
            $query->where('status', $request->status);
        }

        $requisitions = $query->paginate(15)->withQueryString();
        $wards = Wd::orderBy('Wd_Name')->get();

        return view('requisitions.index', compact('requisitions', 'wards'));
    }

    public function history(Request $request): View
    {
        $query = Wardrequisition::with(['ward', 'requester', 'receiver', 'itemRequests.item', 'drugRequests.drug'])
            ->where('status', Wardrequisition::STATUS_COMPLETED)
            ->orderByDesc('DateRecv')
            ->orderByDesc('DateOrd');

        if ($request->filled('ward')) {
            $query->where('Wd_No', $request->ward);
        }

        $requisitions = $query->paginate(15)->withQueryString();
        $wards = Wd::orderBy('Wd_Name')->get();

        return view('requisitions.history', compact('requisitions', 'wards'));
    }

    public function report(Request $request): View
    {
        $wards = Wd::orderBy('Wd_Name')->get();
        $selectedWard = $request->query('ward');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = Wardrequisition::with(['ward', 'itemRequests.item', 'drugRequests.drug'])
            ->where('status', Wardrequisition::STATUS_COMPLETED);

        if ($selectedWard) {
            $query->where('Wd_No', $selectedWard);
        }
        if ($dateFrom) {
            $query->where('DateRecv', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('DateRecv', '<=', $dateTo);
        }

        $requisitions = $query->orderBy('DateRecv')->get();

        // low stock report for store
        $lowItems = CentralStock::whereRaw('QtyInStock <= ReorderLvl')->get();
        $lowDrugs = Pharmaceutical::whereRaw('QtyInStock <= ReorderLvl')->get();

        return view('requisitions.report', compact('requisitions', 'wards', 'selectedWard', 'dateFrom', 'dateTo', 'lowItems', 'lowDrugs'));
    }

    public function create(): View
    {
        return view('requisitions.form', [
            'requisition' => new Wardrequisition(['DateOrd' => now()->toDateString(), 'status' => Wardrequisition::STATUS_PENDING]),
            'wards' => Wd::orderBy('Wd_Name')->get(),
            'staff' => Stf::orderBy('LastName')->get(),
            'supplies' => CentralStock::orderBy('Name')->get(),
            'drugs' => Pharmaceutical::orderBy('Name')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRequisition($request);

        $items = $this->filterItems($request->input('items', []));

        if (empty($items)) {
            throw ValidationException::withMessages(['items' => __('At least one item is required.')]);
        }

        $wdReqNo = DB::transaction(function () use ($data, $items) {
            $req = Wardrequisition::create([
                'Wd_Req_No' => Wardrequisition::nextNo(),
                'Stf_No' => $data['Stf_No'],
                'Wd_No' => $data['Wd_No'],
                'DateOrd' => $data['DateOrd'] ?? now()->toDateString(),
                'status' => Wardrequisition::STATUS_PENDING,
            ]);

            foreach ($items as $row) {
                if (str_starts_with($row['ref'], 'ITEM:')) {
                    $itemNo = substr($row['ref'], 5);
                    Itemrequest::create(['Wd_Req_No' => $req->Wd_Req_No, 'Item_No' => $itemNo, 'QtyReq' => (int) $row['QtyReq']]);
                } else {
                    $drugNo = substr($row['ref'], 5);
                    Drugrequest::create(['Wd_Req_No' => $req->Wd_Req_No, 'Drug_No' => $drugNo, 'QtyReq' => (int) $row['QtyReq']]);
                }
            }

            return $req->Wd_Req_No;
        });

        return redirect()->route('requisitions.show', $wdReqNo)
            ->with('status', __('Created requisition :no.', ['no' => $wdReqNo]));
    }

    public function show(Wardrequisition $requisition): View
    {
        $requisition->load(['ward', 'requester', 'receiver', 'itemRequests.item.supplier', 'drugRequests.drug.supplier']);

        $totalCost = 0;
        foreach ($requisition->itemRequests as $ir) {
            $totalCost += ($ir->item?->CostPerUnit ?? 0) * ($ir->QtyReq ?? 0);
        }
        foreach ($requisition->drugRequests as $dr) {
            $totalCost += ($dr->drug?->CostPerUnit ?? 0) * ($dr->QtyReq ?? 0);
        }

        return view('requisitions.show', compact('requisition', 'totalCost'));
    }

    public function edit(Wardrequisition $requisition): View
    {
        abort_unless($requisition->status === Wardrequisition::STATUS_PENDING, 403, __('Only pending requisitions can be edited.'));

        $requisition->load(['itemRequests', 'drugRequests']);

        return view('requisitions.form', [
            'requisition' => $requisition,
            'wards' => Wd::orderBy('Wd_Name')->get(),
            'staff' => Stf::orderBy('LastName')->get(),
            'supplies' => CentralStock::orderBy('Name')->get(),
            'drugs' => Pharmaceutical::orderBy('Name')->get(),
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Wardrequisition $requisition): RedirectResponse
    {
        abort_unless($requisition->status === Wardrequisition::STATUS_PENDING, 403);

        $data = $this->validateRequisition($request);
        $items = $this->filterItems($request->input('items', []));

        if (empty($items)) {
            throw ValidationException::withMessages(['items' => __('At least one item is required.')]);
        }

        DB::transaction(function () use ($requisition, $data, $items) {
            $requisition->update([
                'Stf_No' => $data['Stf_No'],
                'Wd_No' => $data['Wd_No'],
                'DateOrd' => $data['DateOrd'] ?? $requisition->DateOrd,
            ]);

            Itemrequest::where('Wd_Req_No', $requisition->Wd_Req_No)->delete();
            Drugrequest::where('Wd_Req_No', $requisition->Wd_Req_No)->delete();

            foreach ($items as $row) {
                if (str_starts_with($row['ref'], 'ITEM:')) {
                    Itemrequest::create(['Wd_Req_No' => $requisition->Wd_Req_No, 'Item_No' => substr($row['ref'], 5), 'QtyReq' => (int) $row['QtyReq']]);
                } else {
                    Drugrequest::create(['Wd_Req_No' => $requisition->Wd_Req_No, 'Drug_No' => substr($row['ref'], 5), 'QtyReq' => (int) $row['QtyReq']]);
                }
            }
        });

        return redirect()->route('requisitions.show', $requisition)
            ->with('status', __('Updated requisition :no.', ['no' => $requisition->Wd_Req_No]));
    }

    public function approve(Request $request, Wardrequisition $requisition): RedirectResponse
    {
        abort_unless($requisition->status === Wardrequisition::STATUS_PENDING, 400);

        $requisition->load(['itemRequests.item', 'drugRequests.drug']);

        try {
            DB::transaction(function () use ($requisition) {
                // collect needs and lock rows
                $itemNos = $requisition->itemRequests->pluck('Item_No')->filter()->values();
                $drugNos = $requisition->drugRequests->pluck('Drug_No')->filter()->values();

                $supplies = $itemNos->isNotEmpty() ? CentralStock::whereIn('Item_No', $itemNos)->lockForUpdate()->get()->keyBy('Item_No') : collect();
                $drugs = $drugNos->isNotEmpty() ? Pharmaceutical::whereIn('Drug_No', $drugNos)->lockForUpdate()->get()->keyBy('Drug_No') : collect();

                foreach ($requisition->itemRequests as $ir) {
                    $stock = $supplies[$ir->Item_No] ?? null;
                    $avail = $stock?->QtyInStock ?? 0;
                    if ($avail < $ir->QtyReq) {
                        throw ValidationException::withMessages([
                            'queue' => __('Cannot approve :item — only :stock left, need :need.', [
                                'item' => $stock?->Name ?? $ir->Item_No, 'stock' => $avail, 'need' => $ir->QtyReq,
                            ]),
                        ]);
                    }
                }
                foreach ($requisition->drugRequests as $dr) {
                    $stock = $drugs[$dr->Drug_No] ?? null;
                    $avail = $stock?->QtyInStock ?? 0;
                    if ($avail < $dr->QtyReq) {
                        throw ValidationException::withMessages([
                            'queue' => __('Cannot approve :drug — only :stock left, need :need.', [
                                'drug' => $stock?->Name ?? $dr->Drug_No, 'stock' => $avail, 'need' => $dr->QtyReq,
                            ]),
                        ]);
                    }
                }

                foreach ($requisition->itemRequests as $ir) {
                    $stock = $supplies[$ir->Item_No];
                    $stock->update(['QtyInStock' => ($stock->QtyInStock ?? 0) - $ir->QtyReq]);
                    StockMovement::create([
                        'Item_No' => $ir->Item_No,
                        'QtyChange' => -$ir->QtyReq,
                        'Note' => __('Approved requisition :no for :ward', ['no' => $requisition->Wd_Req_No, 'ward' => $requisition->Wd_No]),
                        'Moved_By' => auth()->id(),
                        'MoveDate' => now(),
                    ]);
                }
                foreach ($requisition->drugRequests as $dr) {
                    $stock = $drugs[$dr->Drug_No];
                    $stock->update(['QtyInStock' => ($stock->QtyInStock ?? 0) - $dr->QtyReq]);
                    StockMovement::create([
                        'Drug_No' => $dr->Drug_No,
                        'QtyChange' => -$dr->QtyReq,
                        'Note' => __('Approved requisition :no for :ward', ['no' => $requisition->Wd_Req_No, 'ward' => $requisition->Wd_No]),
                        'Moved_By' => auth()->id(),
                        'MoveDate' => now(),
                    ]);
                }

                $requisition->update(['status' => Wardrequisition::STATUS_APPROVED]);
            });
        } catch (ValidationException $e) {
            throw $e;
        }

        return redirect()->route('requisitions.show', $requisition)
            ->with('status', __('Approved requisition :no and deducted stock.', ['no' => $requisition->Wd_Req_No]));
    }

    public function receive(Request $request, Wardrequisition $requisition): RedirectResponse
    {
        abort_unless($requisition->status === Wardrequisition::STATUS_APPROVED, 400);

        $data = $request->validate([
            'Received_By' => ['required', 'exists:Stf,Stf_No'],
            'DateRecv' => ['required', 'date'],
        ]);

        $requisition->update([
            'Received_By' => $data['Received_By'],
            'DateRecv' => $data['DateRecv'],
            'status' => Wardrequisition::STATUS_COMPLETED,
        ]);

        return redirect()->route('requisitions.show', $requisition)
            ->with('status', __('Received requisition :no.', ['no' => $requisition->Wd_Req_No]));
    }

    public function destroy(Wardrequisition $requisition): RedirectResponse
    {
        abort_unless($requisition->status === Wardrequisition::STATUS_PENDING, 403);

        $no = $requisition->Wd_Req_No;
        DB::transaction(function () use ($requisition) {
            Itemrequest::where('Wd_Req_No', $requisition->Wd_Req_No)->delete();
            Drugrequest::where('Wd_Req_No', $requisition->Wd_Req_No)->delete();
            $requisition->delete();
        });

        return redirect()->route('requisitions.index')
            ->with('status', __('Deleted requisition :no.', ['no' => $no]));
    }

    protected function validateRequisition(Request $request): array
    {
        return $request->validate([
            'Stf_No' => ['required', 'exists:Stf,Stf_No'],
            'Wd_No' => ['required', 'exists:Wd,Wd_No'],
            'DateOrd' => ['nullable', 'date'],
        ]);
    }

    private function filterItems(array $items): array
    {
        return array_values(array_filter($items, fn ($r) => ! empty($r['ref'] ?? null) && ! empty($r['QtyReq'] ?? null)));
    }
}
