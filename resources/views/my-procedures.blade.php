@extends('layouts.app-shell')

@section('title', 'Mis trámites')

@section('content')
<div class="space-y-8 pb-16">

    <section class="relative overflow-hidden rounded-[3rem] bg-[#2f4a27] px-10 py-12 text-white shadow-[0_30px_70px_-15px_rgba(47,74,39,0.3)]">
        <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-[#c5a35d]/10 blur-[100px]"></div>

        <div class="relative">
            <div class="inline-flex rounded-full border border-white/10 bg-white/5 px-4 py-2 text-[10px] font-bold uppercase tracking-[0.3em] text-[#c5a35d]">
                Histórico personal
            </div>

            <h1 class="mt-8 text-5xl font-light tracking-tight">
                Mis <span class="font-serif italic text-[#c5a35d]">trámites</span>
            </h1>

            <p class="mt-5 max-w-2xl text-lg font-light leading-relaxed text-white/65">
                Consulta tus solicitudes de ausencias y gastos, organizadas por año, tipo y estado.
            </p>
        </div>
    </section>

    <section class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
        <form method="GET" class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-gray-400">
                    Año
                </label>
                <select name="year" class="w-full rounded-2xl border-gray-200 text-sm focus:border-[#2f4a27] focus:ring-[#2f4a27]">
                    <option value="">Todos</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" @selected(request('year') == $year)>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-gray-400">
                    Tipo
                </label>
                <select name="type" class="w-full rounded-2xl border-gray-200 text-sm focus:border-[#2f4a27] focus:ring-[#2f4a27]">
                    <option value="">Todos</option>
                    <option value="absence" @selected(request('type') === 'absence')>Ausencias</option>
                    <option value="expense" @selected(request('type') === 'expense')>Gastos</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-gray-400">
                    Estado
                </label>
                <select name="status" class="w-full rounded-2xl border-gray-200 text-sm focus:border-[#2f4a27] focus:ring-[#2f4a27]">
                    <option value="">Todos</option>
                    <option value="pending_signer_signature" @selected(request('status') === 'pending_signer_signature')>Pendiente firmante</option>
                    <option value="pending_approval" @selected(request('status') === 'pending_approval')>Pendiente aprobación</option>
                    <option value="pending_admin_approval" @selected(request('status') === 'pending_admin_approval')>Pendiente administración</option>
                    <option value="exported_to_sap" @selected(request('status') === 'exported_to_sap')>Exportado a SAP</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rechazado</option>
                </select>
            </div>

            <div class="md:col-span-3 flex gap-3">
                <button class="rounded-2xl bg-[#2f4a27] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#3d5c33]">
                    Filtrar
                </button>

                <a href="{{ route('my-procedures') }}" class="rounded-2xl border border-gray-200 bg-white px-6 py-3 text-sm font-bold text-[#2f4a27] transition hover:bg-[#f2f4ed]">
                    Limpiar
                </a>
            </div>
        </form>
    </section>

    @php
        $filteredProcedures = $procedures
            ->when(request('year'), fn($items) => $items->where('year', request('year')))
            ->when(request('type'), fn($items) => $items->where('type', request('type')))
            ->when(request('status'), fn($items) => $items->where('status', request('status')))
            ->values();

        $statusLabel = function ($status) {
            return match ($status) {
                'pending_employee_signature' => 'Pendiente empleado',
                'pending_signer_signature' => 'Pendiente firmante',
                'pending_approval' => 'Pendiente firmante',
                'pending_admin_approval' => 'Pendiente administración',
                'exported_to_sap' => 'Exportado a SAP',
                'rejected' => 'Rechazado',
                'approved' => 'Aprobado',
                default => ucfirst(str_replace('_', ' ', $status)),
            };
        };

        $statusClass = function ($status) {
            return match ($status) {
                'exported_to_sap', 'approved' => 'bg-[#f2f4ed] text-[#2f4a27] border-[#dfe6d6]',
                'rejected' => 'bg-red-50 text-red-700 border-red-100',
                default => 'bg-[#fcfcf9] text-[#9a6a1f] border-[#ead7ad]',
            };
        };
    @endphp

    <section>
        <div class="mb-6 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-[#2f4a27]">
                    Resultados
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $filteredProcedures->count() }} trámite(s) encontrados.
                </p>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse($filteredProcedures as $procedure)
                <div class="group rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm transition-all hover:-translate-y-1 hover:border-[#c5a35d]/40 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <span class="inline-flex rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest
                                {{ $procedure['type'] === 'absence'
                                    ? 'bg-[#f2f4ed] text-[#2f4a27]'
                                    : 'bg-[#fcfcf9] text-[#c5a35d] ring-1 ring-[#c5a35d]/20' }}">
                                {{ $procedure['label'] }}
                            </span>

                            <h3 class="mt-4 text-lg font-bold text-[#2f4a27]">
                                {{ $procedure['title'] }}
                            </h3>

                            <p class="mt-2 text-sm text-gray-500">
                                {{ $procedure['from'] }}
                                @if($procedure['to'])
                                    — {{ $procedure['to'] }}
                                @endif
                            </p>
                        </div>

                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#2f4a27] text-lg font-serif italic text-[#c5a35d]">
                            {{ $procedure['type'] === 'absence' ? 'A' : 'G' }}
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400">Firmante</span>
                            <span class="font-semibold text-[#2f4a27]">{{ $procedure['signer'] ?? '-' }}</span>
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400">Año</span>
                            <span class="font-semibold text-[#2f4a27]">{{ $procedure['year'] }}</span>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-3">
                        <span class="rounded-full border px-3 py-1 text-[10px] font-bold uppercase tracking-widest {{ $statusClass($procedure['status']) }}">
                            {{ $statusLabel($procedure['status']) }}
                        </span>

                        <span class="text-xs font-bold text-gray-300">
                            #{{ $procedure['id'] }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 xl:col-span-3 rounded-[2rem] border border-dashed border-gray-200 bg-gray-50/60 px-6 py-14 text-center">
                    <p class="text-sm font-medium text-gray-400">
                        No hay trámites con los filtros seleccionados.
                    </p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection