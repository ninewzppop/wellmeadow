@extends('layouts.app')

@section('title', $isEdit ? __('Edit Requisition') . ' ' . $requisition->Wd_Req_No : __('New Requisition'))

@section('content')
<div class="mx-auto max-w-5xl">
    <div class="rounded-2xl bg-white p-8 shadow-sm">
        <h1 class="mb-1 text-2xl font-bold text-slate-900">
            {{ $isEdit ? __('Edit Requisition') . ' — ' . $requisition->Wd_Req_No : __('New Requisition') }}
        </h1>
        <p class="mb-6 text-sm text-slate-500">
            @if ($isEdit)
                {{ __('Requisition No.') }}: <span class="font-semibold text-slate-700">{{ $requisition->Wd_Req_No }}</span>
                <span class="text-slate-400">({{ __('Cannot be changed after creation') }})</span>
            @else
                {{ __('Requisition number will be generated as WR1, WR2…') }}
            @endif
        </p>

        <form method="POST" action="{{ $isEdit ? route('requisitions.update', $requisition) : route('requisitions.store') }}" id="req-form">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                    <ul class="list-inside list-disc text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Ward') }} <span class="text-red-500">*</span></label>
                    <select name="Wd_No" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">{{ __('Select Ward') }}</option>
                        @foreach ($wards as $ward)
                            <option value="{{ $ward->Wd_No }}" {{ old('Wd_No', $requisition->Wd_No) == $ward->Wd_No ? 'selected' : '' }}>{{ $ward->Wd_Name }} ({{ $ward->Wd_No }})</option>
                        @endforeach
                    </select>
                    @error('Wd_No')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Requested by') }} <span class="text-red-500">*</span></label>
                    <select name="Stf_No" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">{{ __('Select Staff') }}</option>
                        @foreach ($staff as $member)
                            <option value="{{ $member->Stf_No }}" {{ old('Stf_No', $requisition->Stf_No) == $member->Stf_No ? 'selected' : '' }}>{{ $member->full_name }} ({{ $member->Stf_No }})</option>
                        @endforeach
                    </select>
                    @error('Stf_No')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Date Ordered') }}</label>
                    <input type="date" name="DateOrd" value="{{ old('DateOrd', $requisition->DateOrd?->format('Y-m-d') ?? now()->toDateString()) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="mt-8">
                <h3 class="mb-3 text-sm font-semibold text-slate-700">{{ __('Items to requisition') }}</h3>
                <div id="items-wrap" class="space-y-3"></div>
                <button type="button" id="add-item"
                        class="mt-3 flex w-full items-center justify-center gap-1.5 rounded-xl border border-dashed border-slate-300 py-2.5 text-xs font-semibold text-slate-600 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">
                    <span class="text-sm">+</span> {{ __('Add item') }}
                </button>
                <div id="total-box" class="mt-4 hidden rounded-xl bg-slate-900 px-4 py-3 text-sm text-white">
                    <div class="flex justify-between"><span>{{ __('Total cost') }}</span><span id="total-cost" class="font-bold">—</span></div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6">
                <a href="{{ route('requisitions.index') }}" class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">{{ $isEdit ? __('Save changes') : __('Submit requisition') }}</button>
            </div>
        </form>

        <template id="item-template">
            <div data-row class="grid gap-2 rounded-xl border border-slate-200 bg-white p-3 sm:grid-cols-12">
                <div class="sm:col-span-6">
                    <label class="mb-1 block text-[11px] font-medium text-slate-500">{{ __('Item / Drug') }} *</label>
                    <select name="items[__INDEX__][ref]" required data-ref
                            class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs focus:border-blue-500 focus:outline-none">
                        <option value="">—</option>
                        <optgroup label="{{ __('surgical') }}">
                            @foreach ($supplies->where('ItemType','surgical') as $s)
                                <option value="ITEM:{{ $s->Item_No }}" data-cost="{{ $s->CostPerUnit ?? 0 }}" data-qty="{{ $s->QtyInStock ?? 0 }}" data-name="{{ $s->Name }}" data-desc="{{ $s->Description }}" data-dosage="" data-method="">{{ $s->Name }} ({{ $s->Item_No }}) — {{ __('Stock') }}: {{ $s->QtyInStock ?? 0 }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="{{ __('non-surgical') }}">
                            @foreach ($supplies->where('ItemType','non-surgical')->merge($supplies->whereNotIn('ItemType',['surgical','non-surgical'])) as $s)
                                <option value="ITEM:{{ $s->Item_No }}" data-cost="{{ $s->CostPerUnit ?? 0 }}" data-qty="{{ $s->QtyInStock ?? 0 }}" data-name="{{ $s->Name }}" data-desc="{{ $s->Description }}">{{ $s->Name }} ({{ $s->Item_No }}) — {{ __('Stock') }}: {{ $s->QtyInStock ?? 0 }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="{{ __('Pharmaceutical') }}">
                            @foreach ($drugs as $d)
                                <option value="DRUG:{{ $d->Drug_No }}" data-cost="{{ $d->CostPerUnit ?? 0 }}" data-qty="{{ $d->QtyInStock ?? 0 }}" data-name="{{ $d->Name }}" data-desc="{{ $d->Description }}" data-dosage="{{ $d->Dosage }}" data-method="{{ $d->AdminMethod }}">{{ $d->Name }} ({{ $d->Drug_No }}) — {{ __('Stock') }}: {{ $d->QtyInStock ?? 0 }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                    <p data-info class="mt-1 hidden text-[11px] text-slate-400"></p>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-[11px] font-medium text-slate-500">{{ __('Qty required') }} *</label>
                    <input type="number" name="items[__INDEX__][QtyReq]" min="1" step="1" required data-qty
                           class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs focus:border-blue-500 focus:outline-none">
                    <p data-stock-warn class="mt-1 hidden text-[11px] font-medium text-amber-600"></p>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-[11px] font-medium text-slate-500">{{ __('Cost / unit') }}</label>
                    <input type="text" data-cost-display readonly
                           class="w-full rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs text-slate-600">
                    <p data-subtotal class="mt-1 text-[11px] font-medium text-slate-700"></p>
                </div>
                <div class="flex items-end sm:col-span-2">
                    <button type="button" data-remove title="{{ __('Remove') }}" class="ml-auto rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const wrap = document.getElementById('items-wrap');
    const tmpl = document.getElementById('item-template');
    const addBtn = document.getElementById('add-item');
    const totalBox = document.getElementById('total-box');
    const totalCost = document.getElementById('total-cost');
    let idx = 0;

    function addRow(prefill){
        const html = tmpl.innerHTML.replace(/__INDEX__/g, idx++);
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const row = temp.firstElementChild;
        wrap.appendChild(row);
        bindRow(row);
        if(prefill){
            const sel = row.querySelector('[data-ref]');
            sel.value = prefill.ref;
            row.querySelector('[data-qty]').value = prefill.qty;
            sel.dispatchEvent(new Event('change'));
        }
        refreshTotal();
    }
    function bindRow(row){
        const sel = row.querySelector('[data-ref]');
        const qtyInput = row.querySelector('[data-qty]');
        const costDisplay = row.querySelector('[data-cost-display]');
        const info = row.querySelector('[data-info]');
        const warn = row.querySelector('[data-stock-warn]');
        const subtotal = row.querySelector('[data-subtotal]');
        function update(){
            const opt = sel.selectedOptions[0];
            if(opt && opt.value){
                const cost = parseFloat(opt.dataset.cost || 0);
                const qtyStock = parseInt(opt.dataset.qty || 0);
                const name = opt.dataset.name;
                const desc = opt.dataset.desc;
                const dosage = opt.dataset.dosage;
                const method = opt.dataset.method;
                costDisplay.value = cost.toFixed(2);
                let infoText = name;
                if(desc) infoText += ' — ' + desc;
                if(dosage) infoText += ' | Dosage: ' + dosage;
                if(method) infoText += ' | ' + method;
                infoText += ' | Stock: ' + qtyStock;
                info.textContent = infoText;
                info.classList.remove('hidden');
                const need = parseInt(qtyInput.value || 0);
                if(need > qtyStock){
                    warn.textContent = 'Only ' + qtyStock + ' left in stock';
                    warn.classList.remove('hidden');
                } else {
                    warn.classList.add('hidden');
                }
                if(need){
                    subtotal.textContent = 'Subtotal: ' + (cost * need).toFixed(2);
                } else {
                    subtotal.textContent = '';
                }
            } else {
                costDisplay.value = '';
                info.classList.add('hidden');
                warn.classList.add('hidden');
                subtotal.textContent = '';
            }
            refreshTotal();
        }
        sel.addEventListener('change', update);
        qtyInput.addEventListener('input', update);
        qtyInput.addEventListener('change', update);
        row.querySelector('[data-remove]').addEventListener('click', function(){
            row.remove();
            if(!wrap.children.length) addRow();
            refreshTotal();
        });
    }
    function refreshTotal(){
        let total = 0;
        let has = false;
        wrap.querySelectorAll('[data-row]').forEach(function(row){
            const opt = row.querySelector('[data-ref]').selectedOptions[0];
            const qty = parseInt(row.querySelector('[data-qty]').value || 0);
            const cost = opt ? parseFloat(opt.dataset.cost || 0) : 0;
            if(opt && opt.value && qty){
                total += cost * qty;
                has = true;
            }
        });
        if(has){
            totalBox.classList.remove('hidden');
            totalCost.textContent = total.toFixed(2);
        } else {
            totalBox.classList.add('hidden');
        }
    }
    // preload existing for edit
    @if ($isEdit)
        @php
            $existing = [];
            foreach($requisition->itemRequests as $ir){ $existing[] = ['ref' => 'ITEM:'.$ir->Item_No, 'qty' => $ir->QtyReq]; }
            foreach($requisition->drugRequests as $dr){ $existing[] = ['ref' => 'DRUG:'.$dr->Drug_No, 'qty' => $dr->QtyReq]; }
        @endphp
        const existing = @json($existing);
        if(existing.length){ existing.forEach(function(r){ addRow(r); }); } else { addRow(); }
    @else
        const oldItems = @json(old('items', []));
        if(oldItems.length){ oldItems.forEach(function(r){ addRow({ref: r.ref, qty: r.QtyReq}); }); } else { addRow(); }
    @endif
    addBtn.addEventListener('click', function(){ addRow(); });
    // reindex before submit
    document.getElementById('req-form').addEventListener('submit', function(){
        const rows = wrap.querySelectorAll('[data-row]');
        rows.forEach(function(row, i){
            row.querySelectorAll('select, input').forEach(function(el){
                const name = el.getAttribute('name');
                if(name) el.setAttribute('name', name.replace(/items\[\d+\]/, 'items['+i+']').replace('__INDEX__', i));
            });
        });
        // strip empty rows
        rows.forEach(function(row){
            const ref = row.querySelector('[data-ref]').value;
            if(!ref){
                row.querySelectorAll('select, input').forEach(function(el){ el.removeAttribute('name'); el.removeAttribute('required'); });
                row.style.display='none';
            }
        });
    });
});
</script>
@endpush
