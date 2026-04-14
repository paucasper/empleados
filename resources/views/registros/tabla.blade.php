<x-app-layout>


    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Mensajes --}}
            @if (session('status'))
                <div class="p-3 rounded-lg bg-green-100 text-green-800 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-3 rounded-lg bg-red-100 text-red-800 text-sm">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
    
            {{-- =========================
                 FILTROS
            ========================== --}}

            <div class="flex items-center justify-between mb-4"> 

                <div> 

                    <h1 class="text-xl font-semibold text-gray-800"> 

                        {{ $cooperativa->nombre }} 

                    </h1> 

                    <p class="text-sm text-gray-500"> 

                        Centro {{ $cooperativa->centro }} · Cooperativa {{ $cooperativa->cod_cooperativa }} 

                    </p> 

                </div> 

            </div> 
            <form method="GET"
                  action="{{ route('cooperativas.tabla', $cooperativa) }}"
                  class="bg-white p-4 rounded-2xl shadow-sm ring-1 ring-gray-100">

                @php
                    $filterInputs = [
                        'zlgort' => 'Depósito',
                        'zmatnr' => 'Material',
                        'zmtart' => 'Tipo Mat.',
                        'zmaktx' => 'Descripción',
                        'zcharg' => 'Lote',
                        'zcamp'  => 'Campaña',
                        'calidad' => 'Calidad',
                        'status_cal' => 'Status',
                    ];
                @endphp

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-2">
                    @foreach($filterInputs as $key => $label)
                        <input
                            name="filters[{{ $key }}]"
                            value="{{ $filters[$key] ?? '' }}"
                            placeholder="{{ $label }}"
                            class="h-8 px-2 rounded-lg border-gray-300 text-xs focus:border-green-500 focus:ring-green-500"
                        />
                    @endforeach
                </div>

                <div class="mt-3 flex gap-2">
                    <button class="px-4 py-2 bg-slate-500 text-white rounded-lg text-sm hover:bg-slate-600">
                        Filtrar
                    </button>

                    <a href="{{ route('cooperativas.tabla', $cooperativa) }}"
                       class="px-4 py-2 border rounded-lg text-sm hover:bg-gray-50">
                        Limpiar
                    </a>
                </div>
            </form>

            {{-- =========================
                 ACCIONES
            ========================== --}}
                <div class="p-3 flex items-center justify-between gap-2">

                    {{-- Guardar cambios local --}}

                    <button
                        type="submit"
                        form="bulk-update-form"
                        class="px-4 py-2 rounded-lg bg-green-800 text-white hover:bg-green-900 text-sm transition-colors duration-150">
                        Guardar cambios (local)
                    </button>


                    {{-- Recargar desde SAP --}}
                    <form method="POST" action="{{ route('cooperativas.tabla.reloadSap', $cooperativa) }}">
                        @csrf
                        <button
                            class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 text-sm transition-colors duration-150">
                            Recargar desde SAP
                        </button>
                    </form>

                    {{-- Guardar en SAP --}}
                    <form method="POST" action="{{ route('cooperativas.tabla.saveToSap', $cooperativa) }}">
                        @csrf
                        <button
                            class="px-4 py-2 rounded-lg bg-stone-600 text-white hover:bg-stone-700 text-sm transition-colors duration-150">
                            Guardar en SAP
                        </button>
                    </form>

                    <div class="text-xs text-gray-500">
                        Tip: filas modificadas se resaltan en amarillo.
                    </div>

                </div>

            {{-- =========================
                 TABLA + EDICIÓN
            ========================== --}}
            <form id="bulk-update-form"
                  method="POST"
                  action="{{ route('cooperativas.tabla.bulkUpdate', $cooperativa) }}">
                @csrf
                @method('PUT')

                <div class="overflow-x-auto bg-white rounded-2xl shadow-sm ring-1 ring-gray-100">
                    <table class="min-w-[1400px] w-full table-fixed text-sm">
                        <thead class="sticky top-0 bg-gray-50 text-xs font-semibold text-gray-600 z-10">
                            <tr class="border-b">
                                <th class="px-3 py-2 w-20 text-left">Depósito</th>
                                <th class="px-3 py-2 w-28 text-left">Material</th>
                                <th class="px-3 py-2 w-20 text-left">Tipo</th>
                                <th class="px-3 py-2 w-[320px] text-left">Descripción</th>
                                <th class="px-3 py-2 w-28 text-left">Lote</th>
                                <th class="px-3 py-2 w-16 text-left">Ud.</th>
                                <th class="px-3 py-2 w-28 text-right">Stock</th>
                                <th class="px-3 py-2 w-28 text-left">Campaña</th>
                                <th class="px-3 py-2 w-20 text-left">Calidad</th>
                                <th class="px-3 py-2 w-24 text-left">Status</th>
                                <th class="px-3 py-2 w-28 text-left">F. Análisis</th>
                                <th class="px-3 py-2 w-32 text-left">Observ.</th>
                                <th class="px-3 py-2 w-40 text-left">F. Muestras</th>
                                <th class="px-3 py-2 w-56 text-left">Obs. Muestras</th>
                                <th class="px-3 py-2 w-56 text-left">Obs. Técnico</th>
                                <th class="px-3 py-2 w-32 text-left">Reserva</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @foreach($rows as $r)
                                <tr data-row="{{ $r->id }}"
                                    class="{{ $r->modificado ? 'bg-yellow-50' : '' }} hover:bg-gray-50">

                                    <td class="px-3 py-2">{{ $r->zlgort }}</td>
                                    <td class="px-3 py-2 font-mono">{{ $r->zmatnr }}</td>
                                    <td class="px-3 py-2">{{ $r->zmtart }}</td>
                                    <td class="px-3 py-2 break-words">{{ $r->zmaktx }}</td>
                                    <td class="px-3 py-2 font-mono">{{ $r->zcharg }}</td>
                                    <td class="px-3 py-2">{{ $r->zmeins }}</td>

                                    <td class="px-3 py-2 text-right">
                                        {{ is_numeric($r->zlabst02_kg)
                                            ? number_format((float)$r->zlabst02_kg, 3, ',', '.')
                                            : $r->zlabst02_kg }}
                                    </td>

                                    <td class="px-3 py-2">{{ $r->zcamp }}</td>
                                    <td class="px-3 py-2">{{ $r->calidad }}</td>

                                    <td class="px-3 py-2">
                                        @php $current = strtoupper((string) $r->status_cal); @endphp
                                        <select name="status_cal[{{ $r->id }}]"
                                                class="h-8 w-28 rounded-lg border-gray-300 text-xs px-2">
                                            <option value="" {{ $current === '' ? 'selected' : '' }}>-</option>
                                            <option value="SD" {{ $current === 'SD' ? 'selected' : '' }}>SD</option>
                                            <option value="LIBERADO" {{ $current === 'LIBERADO' ? 'selected' : '' }}>LIBERADO</option>
                                            <option value="BLOQUEADO" {{ $current === 'BLOQUEADO' ? 'selected' : '' }}>BLOQUEADO</option>
                                        </select>
                                    </td>

                                    <td class="px-3 py-2">{{ $r->fecha_analisis }}</td>
                                    <td class="px-3 py-2 break-words">{{ $r->observaciones }}</td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="zfecha_muestras[{{ $r->id }}]"
                                               value="{{ $r->zfecha_muestras }}"
                                               class="h-8 w-36 rounded-lg border-gray-300 text-xs px-2"
                                               placeholder="dd/mm/aaaa">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="zobserv_muestras[{{ $r->id }}]"
                                               value="{{ $r->zobserv_muestras }}"
                                               class="h-8 w-52 rounded-lg border-gray-300 text-xs px-2">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="zobserv_tecn[{{ $r->id }}]"
                                               value="{{ $r->zobserv_tecn }}"
                                               class="h-8 w-52 rounded-lg border-gray-300 text-xs px-2">
                                    </td>

                                    <td class="px-3 py-2 break-words">
                                        {{ $r->zreserva_clientes }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $rows->links() }}
                </div>
            </form>

        </div>
    </div>


</x-app-layout>

 