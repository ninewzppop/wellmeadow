@props(['patients', 'selected' => null])

@php
    $patientOptions = $patients->map(fn ($p) => ['id' => $p->Pt_No, 'label' => $p->full_name.' ('.$p->Pt_No.')']);
    $currentPatient = $selected ? $patients->firstWhere('Pt_No', $selected) : null;
@endphp

<div data-patient-select>
    <input type="hidden" {{ $attributes }} value="{{ $selected }}">
    <div class="relative">
        <input type="text" data-patient-search autocomplete="off"
               data-patient-options="{{ json_encode($patientOptions) }}"
               value="{{ $currentPatient?->full_name ? $currentPatient->full_name.' ('.$currentPatient->Pt_No.')' : '' }}"
               placeholder="{{ __('Type patient name or ID...') }}"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        <div data-patient-list
             class="absolute z-20 mt-1 hidden max-h-60 w-full overflow-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"></div>
    </div>
    <p class="mt-1 text-xs text-slate-400">{{ __('Type to search by name or patient ID.') }}</p>
</div>

@push('scripts')
    <script>
        if (!window.__patientSelectInit) {
            window.__patientSelectInit = true;

            document.querySelectorAll('[data-patient-search]').forEach(function (input) {
                var scope = input.closest('[data-patient-select]');
                var list = scope.querySelector('[data-patient-list]');
                var hidden = scope.querySelector('input[type="hidden"]');
                var patients = JSON.parse(input.dataset.patientOptions || '[]');

                function render(query) {
                    query = query.trim().toLowerCase();
                    var matches = patients.filter(function (p) {
                        return p.label.toLowerCase().indexOf(query) !== -1;
                    }).slice(0, 50);

                    list.innerHTML = '';

                    if (!matches.length) {
                        var empty = document.createElement('div');
                        empty.className = 'px-3 py-2 text-sm text-slate-400';
                        empty.textContent = @json(__('No matching patient.'));
                        list.appendChild(empty);
                    }

                    matches.forEach(function (p) {
                        var option = document.createElement('button');
                        option.type = 'button';
                        option.className = 'block w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100';
                        option.textContent = p.label;
                        option.addEventListener('mousedown', function (e) {
                            e.preventDefault();
                            hidden.value = p.id;
                            input.value = p.label;
                            list.classList.add('hidden');
                        });
                        list.appendChild(option);
                    });

                    list.classList.remove('hidden');
                }

                input.addEventListener('focus', function () {
                    render(input.value);
                });
                input.addEventListener('input', function () {
                    hidden.value = '';
                    render(input.value);
                });
                input.addEventListener('blur', function () {
                    setTimeout(function () {
                        list.classList.add('hidden');
                    }, 120);
                });
            });
        }
    </script>
@endpush
