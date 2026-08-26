<?php

namespace App\Http\Controllers;

use App\Models\Stf;
use App\Models\StfRota;
use App\Models\Wd;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RotaController extends Controller
{
    public function index(Request $request): View
    {
        $weekBeginning = $request->validate(['date' => ['nullable', 'date']])['date'] ?? null;
        $editId = $request->validate(['edit' => ['nullable', 'string']])['edit'] ?? null;

        $rotas = StfRota::query()
            ->with(['stf.positions.pos', 'wd'])
            ->when($weekBeginning, fn ($query) => $query->whereDate('WkBegin', $weekBeginning))
            ->get()
            ->sortBy(fn (StfRota $rota) => (($rota->stf?->full_name ?? '~zz').'|'.($rota->WkBegin?->toDateString() ?? '')))
            ->values();

        $conflicts = $this->conflictingAssignments($rotas);
        $staff = Stf::orderBy('LastName')->orderBy('FirstName')->get();
        $wards = Wd::orderBy('Wd_Name')->get();

        return view('rota.index', compact('rotas', 'weekBeginning', 'conflicts', 'staff', 'wards', 'editId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($this->hasConflict($data['Stf_No'], $data['WkBegin'])) {
            return back()
                ->withInput()
                ->withErrors(['Stf_No' => $this->conflictMessage($data['Stf_No'], $data['WkBegin'])]);
        }

        StfRota::create([
            'StfRota_No' => 'R'.Str::upper(Str::random(9)),
            ...$data,
        ]);

        return redirect()->route('rota.index')
            ->with('status', __('Allocation recorded.'));
    }

    public function update(Request $request, StfRota $allocation): RedirectResponse
    {
        $data = $this->validated($request);

        if ($this->hasConflict($data['Stf_No'], $data['WkBegin'], $allocation->StfRota_No)) {
            return redirect()
                ->route('rota.index', ['edit' => $allocation->StfRota_No])
                ->withInput()
                ->withErrors(['Stf_No' => $this->conflictMessage($data['Stf_No'], $data['WkBegin'])]);
        }

        $allocation->update($data);

        return redirect()->route('rota.index')
            ->with('status', __('Allocation updated.'));
    }

    public function destroy(StfRota $allocation): RedirectResponse
    {
        $allocation->delete();

        return redirect()->route('rota.index')
            ->with('status', __('Allocation removed.'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'Stf_No' => ['required', 'exists:Stf,Stf_No'],
            'Wd_No' => ['required', 'exists:Wd,Wd_No'],
            'WkBegin' => ['required', 'date'],
            'Shift' => ['required', 'in:Morning,Evening,Night'],
        ]);
    }

    /**
     * A staff member may hold only one roster entry per week.
     */
    private function hasConflict(string $stfNo, string $wkBegin, ?string $ignoreNo = null): bool
    {
        return StfRota::query()
            ->where('Stf_No', $stfNo)
            ->whereDate('WkBegin', $wkBegin)
            ->when($ignoreNo, fn ($query) => $query->where('StfRota_No', '!=', $ignoreNo))
            ->exists();
    }

    private function conflictMessage(string $stfNo, string $wkBegin): string
    {
        $staff = Stf::find($stfNo);

        return __(':staff already has an assignment in the week beginning :date. Please edit or cancel.', [
            'staff' => $staff?->full_name ?? $stfNo,
            'date' => Carbon::parse($wkBegin)->format('d M Y'),
        ]);
    }

    /**
     * Staff members holding more than one roster entry in the same week,
     * keyed by "Stf_No|week-begin date".
     *
     * @param  Collection<int, StfRota>  $rotas
     * @return Collection<string, Collection<int, StfRota>>
     */
    private function conflictingAssignments(Collection $rotas): Collection
    {
        return $rotas
            ->filter(fn (StfRota $rota) => $rota->Stf_No !== null)
            ->groupBy(fn (StfRota $rota) => $rota->Stf_No.'|'.($rota->WkBegin?->toDateString() ?? ''))
            ->filter(fn (Collection $group) => $group->count() > 1);
    }
}
