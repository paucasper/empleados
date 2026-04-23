@extends('layouts.app-shell')

@section('title', 'Inicio')

@section('content')
    <div class="space-y-12 pb-16">
        
        <section class="relative overflow-hidden rounded-[3rem] bg-[#2f4a27] text-white shadow-[0_30px_70px_-15px_rgba(47,74,39,0.3)]">
            <div class="absolute -right-20 -top-20 h-96 w-96 rounded-full bg-[#c5a35d]/10 blur-[100px]"></div>
            <div class="absolute bottom-0 left-1/3 h-64 w-64 rounded-full bg-white/5 blur-[80px]"></div>

            <div class="relative grid gap-12 px-10 py-12 lg:grid-cols-[1.4fr_0.6fr] lg:px-14 lg:py-16">
                <div>
                    <div class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-[10px] font-bold uppercase tracking-[0.3em] text-[#c5a35d] backdrop-blur-md">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#c5a35d] opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-[#c5a35d]"></span>
                        </span>
                        Portal Interno de Tramitación
                    </div>

                    <h1 class="mt-8 text-5xl font-light tracking-tight md:text-6xl">
                        ¡Hola, <span class="font-serif italic text-[#c5a35d]">{{ auth()->user()->name }}</span>!
                    </h1>

                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-white/60 font-light">
                        Gestiona tus <span class="text-white/90">ausencias</span>, reporta <span class="text-white/90">gastos</span> y organiza tu calendario desde el entorno digital de <span class="text-[#c5a35d] font-medium">Dcoop</span>.
                    </p>

                    <div class="mt-10 flex flex-wrap gap-4">
                        <a href="{{ route('vacations') }}" class="group relative overflow-hidden rounded-2xl bg-[#c5a35d] px-8 py-4 text-sm font-bold text-[#2f4a27] transition-all hover:bg-[#d9b66d] hover:scale-[1.02] active:scale-[0.98]">
                            NUEVO TRÁMITE
                        </a>

                        <a href="{{ route('pending-approvals') }}" class="rounded-2xl border border-white/10 bg-white/5 px-8 py-4 text-sm font-bold text-white backdrop-blur-md transition hover:bg-white/10">
                            VER BANDEJA
                        </a>
                    </div>
                </div>


            </div>
        </section>

        <section>
            <div class="mb-8">
                <h2 class="text-2xl font-bold tracking-tight text-[#2f4a27]">Resumen</h2>
                <p class="text-sm text-gray-500 font-light italic mt-1">Estado actual de tus procesos principales</p>
            </div>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <div class="group rounded-[2rem] border border-gray-100 bg-white p-7 shadow-sm transition-all hover:-translate-y-1 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Ausencias</p>
                            <h3 class="mt-2 text-lg font-bold text-[#2f4a27]">Sin firma</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f2f4ed] text-[#2f4a27] transition group-hover:bg-[#2f4a27] group-hover:text-white">
                            <span class="text-2xl font-light">{{ $stats['unsigned_absences'] }}</span>
                        </div>
                    </div>
                    <p class="mt-6 text-xs leading-relaxed text-gray-500">Solicitudes pendientes de completar o firmar por tu parte.</p>
                </div>

                <div class="group rounded-[2rem] border border-gray-100 bg-white p-7 shadow-sm transition-all hover:-translate-y-1 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Ausencias</p>
                            <h3 class="mt-2 text-lg font-bold text-[#2f4a27]">En curso</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f2f4ed] text-[#2f4a27] transition group-hover:bg-[#2f4a27] group-hover:text-white">
                            <span class="text-2xl font-light">{{ $stats['active_absences'] }}</span>
                        </div>
                    </div>
                    <p class="mt-6 text-xs leading-relaxed text-gray-500">Procesos de ausencia activos actualmente en el sistema.</p>
                </div>

                <div class="group rounded-[2rem] border border-gray-100 bg-white p-7 shadow-sm transition-all hover:-translate-y-1 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Gastos</p>
                            <h3 class="mt-2 text-lg font-bold text-[#2f4a27]">Liquidaciones</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f2f4ed] text-[#2f4a27] transition group-hover:bg-[#2f4a27] group-hover:text-white">
                            <span class="text-2xl font-light">{{ $stats['active_expenses'] }}</span>
                        </div>
                    </div>
                    <p class="mt-6 text-xs leading-relaxed text-gray-500">Notas de gasto y tickets en proceso de validación.</p>
                </div>


            </div>
        </section>


        <!-- Mis tramitaciones -->
        <section>
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-[#2f4a27]">Mis tramitaciones</h2>
                    <p class="mt-1 text-sm text-gray-500 font-light italic">Estado actual de tus solicitudes en curso.</p>
                </div>
            </div>

            <div class="space-y-4">

                {{-- Ausencias --}}
                @forelse($myAbsences as $absence)
                    <div class="overflow-hidden rounded-[2rem] border border-gray-100 bg-white p-8 shadow-sm transition-all hover:border-[#c5a35d]/30">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="inline-flex rounded-full bg-[#f2f4ed] px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-[#2f4a27] ring-1 ring-[#2f4a27]/10">
                                    Ausencia
                                </span>
                                <p class="mt-3 font-bold tracking-tight text-[#2f4a27]">
                                    {{ $absence->description ?: $absence->awart }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $absence->begda->format('d/m/Y') }} — {{ $absence->endda->format('d/m/Y') }}
                                </p>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                {{ $absence->created_at->format('d/m/Y') }}
                            </span>
                        </div>

                        {{-- Stepper ausencia --}}
                        <div class="mt-8 flex items-center gap-0">
                            {{-- Paso 1: Empleado --}}
                            <div class="flex flex-col items-center gap-2" style="min-width:100px">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full border-4 border-white {{ $absence->employee_signed_at ? 'bg-[#c5a35d] text-[#2f4a27]' : 'bg-gray-100 text-gray-400' }}">
                                    @if($absence->employee_signed_at)
                                        <span class="text-sm font-bold">✓</span>
                                    @else
                                        <span class="text-xs font-bold">1</span>
                                    @endif
                                </div>
                                <p class="text-center text-[10px] font-bold uppercase tracking-widest text-[#2f4a27]">{{ $user->name }}</p>
                                <p class="text-center text-[10px] text-gray-400">
                                    {{ $absence->employee_signed_at ? $absence->employee_signed_at->format('d/m/Y') : 'Pendiente' }}
                                </p>
                            </div>

                            {{-- Línea 1 --}}
                            <div class="h-[2px] flex-1 {{ $absence->employee_signed_at ? 'bg-[#c5a35d]' : 'bg-gray-100' }}"></div>

                            {{-- Paso 2: Firmante --}}
                            <div class="flex flex-col items-center gap-2" style="min-width:100px">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full border-4 border-white {{ $absence->signer_signed_at ? 'bg-[#c5a35d] text-[#2f4a27]' : 'bg-gray-100 text-gray-400' }}">
                                    @if($absence->signer_signed_at)
                                        <span class="text-sm font-bold">✓</span>
                                    @else
                                        <span class="text-xs font-bold">2</span>
                                    @endif
                                </div>
                                <p class="text-center text-[10px] font-bold uppercase tracking-widest text-[#2f4a27]">{{ $absence->signer?->name ?? 'Firmante' }}</p>
                                <p class="text-center text-[10px] text-gray-400">
                                    {{ $absence->signer_signed_at ? $absence->signer_signed_at->format('d/m/Y') : 'Pendiente' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[2rem] border border-dashed border-gray-200 bg-gray-50/50 px-4 py-10 text-center text-sm text-gray-400 font-light italic">
                        No tienes ausencias en curso.
                    </div>
                @endforelse

                {{-- Gastos --}}
                @forelse($myExpenses as $expense)
                    <div class="overflow-hidden rounded-[2rem] border border-gray-100 bg-white p-8 shadow-sm transition-all hover:border-[#c5a35d]/30">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="inline-flex rounded-full bg-[#fcfcf9] px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-[#c5a35d] ring-1 ring-[#c5a35d]/20">
                                    Gasto
                                </span>
                                <p class="mt-3 font-bold tracking-tight text-[#2f4a27]">
                                    {{ $expense->description ?: $expense->title }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $expense->status->name ?? '-' }}
                                </p>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                {{ $expense->created_at->format('d/m/Y') }}
                            </span>
                        </div>

                        {{-- Stepper gasto --}}
                        <div class="mt-8 flex items-center gap-0">
                            {{-- Paso 1: Solicitante --}}
                            <div class="flex flex-col items-center gap-2" style="min-width:100px">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full border-4 border-white {{ $expense->submitted_at ? 'bg-[#c5a35d] text-[#2f4a27]' : 'bg-gray-100 text-gray-400' }}">
                                    @if($expense->submitted_at)
                                        <span class="text-sm font-bold">✓</span>
                                    @else
                                        <span class="text-xs font-bold">1</span>
                                    @endif
                                </div>
                                <p class="text-center text-[10px] font-bold uppercase tracking-widest text-[#2f4a27]">{{ $user->name }}</p>
                                <p class="text-center text-[10px] text-gray-400">
                                    {{ $expense->submitted_at ? \Carbon\Carbon::parse($expense->submitted_at)->format('d/m/Y') : 'Pendiente' }}
                                </p>
                            </div>

                            {{-- Línea 1 --}}
                            <div class="h-[2px] flex-1 {{ $expense->submitted_at ? 'bg-[#c5a35d]' : 'bg-gray-100' }}"></div>

                            {{-- Paso 2: Jefe --}}
                            <div class="flex flex-col items-center gap-2" style="min-width:100px">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full border-4 border-white {{ $expense->approved_at ? 'bg-[#c5a35d] text-[#2f4a27]' : 'bg-gray-100 text-gray-400' }}">
                                    @if($expense->approved_at)
                                        <span class="text-sm font-bold">✓</span>
                                    @else
                                        <span class="text-xs font-bold">2</span>
                                    @endif
                                </div>
                                <p class="text-center text-[10px] font-bold uppercase tracking-widest text-[#2f4a27]">{{ $expense->approver?->name ?? 'Jefe' }}</p>
                                <p class="text-center text-[10px] text-gray-400">
                                    {{ $expense->approved_at ? \Carbon\Carbon::parse($expense->approved_at)->format('d/m/Y') : 'Pendiente' }}
                                </p>
                            </div>

                            {{-- Línea 2 --}}
                            <div class="h-[2px] flex-1 {{ $expense->approved_at ? 'bg-[#c5a35d]' : 'bg-gray-100' }}"></div>

                            {{-- Paso 3: Admin --}}
                            <div class="flex flex-col items-center gap-2" style="min-width:100px">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full border-4 border-white {{ $expense->sap_exported_at ? 'bg-[#c5a35d] text-[#2f4a27]' : 'bg-gray-100 text-gray-400' }}">
                                    @if($expense->sap_exported_at)
                                        <span class="text-sm font-bold">✓</span>
                                    @else
                                        <span class="text-xs font-bold">3</span>
                                    @endif
                                </div>
                                <p class="text-center text-[10px] font-bold uppercase tracking-widest text-[#2f4a27]">{{ $expense->admin?->name ?? 'Administración' }}</p>
                                <p class="text-center text-[10px] text-gray-400">
                                    {{ $expense->sap_exported_at ? \Carbon\Carbon::parse($expense->sap_exported_at)->format('d/m/Y') : 'Pendiente' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[2rem] border border-dashed border-gray-200 bg-gray-50/50 px-4 py-10 text-center text-sm text-gray-400 font-light italic">
                        No tienes gastos en curso.
                    </div>
                @endforelse

            </div>
        </section>

        <section>
            <h2 class="text-xl font-bold text-[#2f4a27] mb-6">Mis trámites</h2>
            <div class="space-y-4">
                {{-- Ausencias finalizadas --}}
                @forelse($completedAbsences as $absence)
                    <div class="rounded-[2rem] border border-[#c5a35d]/20 bg-[#fcfcf9] p-8 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-2xl bg-[#2f4a27] flex items-center justify-center text-[#c5a35d] font-serif italic text-xl">
                                    A
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#2f4a27] tracking-tight">
                                        {{ $absence->description ?: $absence->awart }}
                                    </h4>
                                    <p class="text-xs text-gray-400 font-medium tracking-wide uppercase">
                                        {{ $absence->begda->format('d/m/Y') }} — {{ $absence->endda->format('d/m/Y') }}
                                    </p>
                                </div>
                            </div>

                            <span class="rounded-full bg-[#c5a35d]/10 px-4 py-2 text-[10px] font-bold uppercase tracking-widest text-[#2f4a27]">
                                {{ $absence->status === 'exported_to_sap' ? 'Exportado a SAP' : 'Rechazado' }}
                            </span>
                        </div>
                    </div>
                @empty
                @endforelse

                {{-- Gastos finalizados --}}
                @forelse($completedExpenses as $expense)
                    <div class="rounded-[2rem] border border-[#c5a35d]/20 bg-[#fcfcf9] p-8 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-2xl bg-[#2f4a27] flex items-center justify-center text-[#c5a35d] font-serif italic text-xl">
                                    G
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#2f4a27] tracking-tight">
                                        {{ $expense->description ?: $expense->title ?: 'Solicitud de gasto' }}
                                    </h4>
                                    <p class="text-xs text-gray-400 font-medium tracking-wide uppercase">
                                        {{ $expense->created_at->format('d/m/Y') }}
                                    </p>
                                </div>
                            </div>

                            <span class="rounded-full bg-[#c5a35d]/10 px-4 py-2 text-[10px] font-bold uppercase tracking-widest text-[#2f4a27]">
                                {{ $expense->status?->code === 'exported_to_sap' ? 'Exportado a SAP' : 'Rechazado' }}
                            </span>
                        </div>
                    </div>
                @empty
                @endforelse

                @if(($completedAbsences->count() ?? 0) === 0 && ($completedExpenses->count() ?? 0) === 0)
                    <div class="rounded-[2rem] border border-dashed border-gray-200 bg-gray-50/50 px-4 py-12 text-center text-sm text-gray-400 font-light italic">
                        No tienes trámites finalizados todavía.
                    </div>
                @endif
            </div>
        </section>

        <section class="grid gap-8 xl:grid-cols-2">
            <div class="overflow-hidden rounded-[2.5rem] border border-gray-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-50 px-8 py-6">
                    <h2 class="text-lg font-bold text-[#2f4a27]">Últimas solicitudes</h2>
                    
                </div>
                <div class="overflow-x-auto px-8 py-4">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-[10px] font-bold uppercase tracking-widest text-gray-400 border-b border-gray-50">
                                <th class="pb-4">Concepto</th>
                                <th class="pb-4">Estado</th>
                                <th class="pb-4 text-right">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @forelse($recentRequests as $request)
                                <tr class="group">
                                    <td class="py-4 font-semibold text-[#2f4a27] group-last:pb-0">{{ $request['title'] }}</td>
                                    <td class="py-4 group-last:pb-0">
                                        <span class="rounded-full bg-[#f2f4ed] px-3 py-1 text-[10px] font-bold text-[#2f4a27] uppercase">{{ $request['status'] }}</span>
                                    </td>
                                    <td class="py-4 text-right text-gray-400 group-last:pb-0">{{ $request['date'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-8 text-center text-xs text-gray-400 italic">Sin actividad reciente</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-[2.5rem] border border-gray-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-50 px-8 py-6">
                    <h2 class="text-lg font-bold text-[#2f4a27]">Pendientes aprobación</h2>
                    <a href="{{ route('pending-approvals') }}" class="text-xs font-bold text-[#c5a35d] uppercase tracking-widest hover:text-[#2f4a27] transition-colors">Bandeja</a>
                </div>
                <div class="space-y-3 px-8 py-6">
                    @forelse($pendingApprovals as $approval)
                        <div class="flex items-center justify-between rounded-2xl border border-gray-50 bg-[#fcfcf9] p-4 transition-all hover:bg-[#f2f4ed]">
                            <div>
                                <p class="text-sm font-bold text-[#2f4a27]">{{ $approval['employee'] }}</p>
                                <p class="text-[10px] text-gray-400 font-medium uppercase mt-1">{{ $approval['type'] }}</p>
                            </div>
                            <span class="text-[10px] font-bold text-[#c5a35d]">{{ $approval['date'] }}</span>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-gray-400 italic">No hay aprobaciones pendientes</div>
                    @endforelse
                </div>
            </div>
        </section>

        <footer class="mt-16 flex items-center justify-between border-t border-gray-100 pt-10">
            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.4em]">Departamento de Sistemas · Dcoop</span>
                <span class="text-[9px] text-gray-400 mt-1">Ecosistema Digital Interno · v2.6.0</span>
            </div>
            <div class="flex gap-4 opacity-20 grayscale">
                </div>
        </footer>
    </div>
@endsection