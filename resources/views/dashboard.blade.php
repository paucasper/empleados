@extends('layouts.app-shell')

@section('title', 'Inicio')

@section('content')
    <div class="space-y-10">
        <!-- Hero -->
        <div class="mb-2 flex items-center justify-between gap-4"></div>

        <section class="relative overflow-hidden rounded-[2rem] border border-emerald-200/60 bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 text-white shadow-[0_20px_60px_-20px_rgba(5,150,105,0.45)]">
            <!-- decor -->
            <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 h-40 w-40 rounded-full bg-teal-300/10 blur-2xl"></div>

            <div class="relative grid gap-8 px-8 py-10 lg:grid-cols-[1.35fr_0.85fr] lg:px-10 lg:py-12">
                <div>
                    <div class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.24em] text-white/90 backdrop-blur-sm">
                        Portal interno RRHH
                    </div>

                    <h1 class="mt-5 text-4xl font-semibold tracking-tight md:text-5xl">
                        ¡Hola, {{ auth()->user()->name }}!
                    </h1>

                    <p class="mt-5 max-w-2xl text-sm leading-7 text-white/90 md:text-base">
                        Gestiona ausencias, vacaciones, aprobaciones y trámites desde un entorno
                        claro, moderno y preparado para acompañar el crecimiento del portal.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a
                            href="{{ route('vacations') }}"
                            class="inline-flex items-center rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-emerald-700 shadow-sm transition hover:translate-y-[-1px] hover:bg-emerald-50"
                        >
                            Nuevo trámite
                        </a>

                        <a
                            href="{{ route('pending-approvals') }}"
                            class="inline-flex items-center rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/15"
                        >
                            Ver bandeja
                        </a>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/85">
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.2em] text-white/60">Estado</p>
                            <p class="mt-1 font-semibold">Operativo</p>
                        </div>
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.2em] text-white/60">Módulos</p>
                            <p class="mt-1 font-semibold">Ausencias · Vacaciones · Gastos</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4">
                    <div class="rounded-3xl border border-white/20 bg-white/10 p-5 backdrop-blur-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-white/80">Estado general</p>
                                <p class="mt-3 text-4xl font-semibold text-white">Activo</p>
                                <p class="mt-2 text-sm leading-6 text-white/75">
                                    Portal disponible y flujo de tramitación operativo.
                                </p>
                            </div>
                            <span class="mt-1 inline-flex h-3 w-3 rounded-full bg-emerald-300 shadow-[0_0_20px_rgba(110,231,183,0.9)]"></span>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-white/20 bg-white/10 p-5 backdrop-blur-sm">
                        <p class="text-sm font-medium text-white/80">Actividad</p>
                        <p class="mt-3 text-lg font-semibold text-white">Tramitaciones y aprobaciones</p>
                        <p class="mt-2 text-sm leading-6 text-white/75">
                            Accede rápidamente a las acciones más habituales del día a día.
                        </p>

                        <div class="mt-4 inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold text-white/90">
                            Experiencia mejorada
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Resumen -->
        <section>
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Resumen</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Estado actual de tus procesos principales.
                    </p>
                </div>

                <div class="hidden rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 md:inline-flex">
                    Vista general
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                <div class="group rounded-[1.75rem] border border-emerald-100 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">
                                Ausencias
                            </p>
                            <h3 class="mt-2 text-lg font-semibold text-slate-900">Sin firma</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 ring-1 ring-emerald-100">
                            <div class="h-2.5 w-2.5 rounded-full bg-emerald-500"></div>
                        </div>
                    </div>

                    <p class="mt-6 text-4xl font-semibold tracking-tight text-slate-900">
                        {{ $stats['unsigned_absences'] }}
                    </p>

                    <div class="mt-4 h-px w-full bg-gradient-to-r from-emerald-100 via-emerald-50 to-transparent"></div>

                    <p class="mt-4 text-sm leading-6 text-slate-500">
                        Solicitudes pendientes de completar o firmar.
                    </p>
                </div>

                <div class="group rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">
                                Ausencias
                            </p>
                            <h3 class="mt-2 text-lg font-semibold text-slate-900">En curso</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 ring-1 ring-slate-200">
                            <div class="h-2.5 w-2.5 rounded-full bg-slate-500"></div>
                        </div>
                    </div>

                    <p class="mt-6 text-4xl font-semibold tracking-tight text-slate-900">
                        {{ $stats['active_absences'] }}
                    </p>

                    <div class="mt-4 h-px w-full bg-gradient-to-r from-slate-200 via-slate-100 to-transparent"></div>

                    <p class="mt-4 text-sm leading-6 text-slate-500">
                        Ausencias activas actualmente.
                    </p>
                </div>

                <div class="group rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">
                                Gastos
                            </p>
                            <h3 class="mt-2 text-lg font-semibold text-slate-900">Liquidaciones</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 ring-1 ring-slate-200">
                            <div class="h-2.5 w-2.5 rounded-full bg-slate-500"></div>
                        </div>
                    </div>

                    <p class="mt-6 text-4xl font-semibold tracking-tight text-slate-900">
                        {{ $stats['active_expenses'] }}
                    </p>

                    <div class="mt-4 h-px w-full bg-gradient-to-r from-slate-200 via-slate-100 to-transparent"></div>

                    <p class="mt-4 text-sm leading-6 text-slate-500">
                        Notas de gasto en proceso.
                    </p>
                </div>

                <div class="group rounded-[1.75rem] border border-amber-100 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">
                                Recursos
                            </p>
                            <h3 class="mt-2 text-lg font-semibold text-slate-900">Documentación</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 ring-1 ring-amber-100">
                            <div class="h-2.5 w-2.5 rounded-full bg-amber-500"></div>
                        </div>
                    </div>

                    <p class="mt-6 text-2xl font-semibold tracking-tight text-slate-900">
                        Disponible
                    </p>

                    <div class="mt-4 h-px w-full bg-gradient-to-r from-amber-100 via-amber-50 to-transparent"></div>

                    <p class="mt-4 text-sm leading-6 text-slate-500">
                        Recursos y documentación interna accesibles.
                    </p>
                </div>
            </div>
        </section>

        <!-- Accesos rápidos -->
        <section>
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Accesos rápidos</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Las acciones más utilizadas dentro del portal.
                    </p>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <a
                    href="{{ route('vacations') }}"
                    class="group rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Nueva ausencia</p>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Crear una nueva solicitud de ausencia o vacaciones.
                            </p>
                        </div>

                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-2xl font-light text-emerald-600 ring-1 ring-emerald-100 transition group-hover:bg-emerald-100">
                            +
                        </div>
                    </div>
                </a>

                <a
                    href="{{ route('pending-approvals') }}"
                    class="group rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Bandeja de entrada</p>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Revisar y firmar solicitudes pendientes.
                            </p>
                        </div>

                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-lg text-slate-700 ring-1 ring-slate-200 transition group-hover:bg-slate-100">
                            →
                        </div>
                    </div>
                </a>

                <a
                    href="#"
                    class="group rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Nuevo gasto</p>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Revisar y firmar gastos pendientes.
                            </p>
                        </div>

                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-lg text-slate-700 ring-1 ring-slate-200 transition group-hover:bg-slate-100">
                            ≡
                        </div>
                    </div>
                </a>
            </div>
        </section>

        <!-- Mis tramitaciones -->
        <section>
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Mis tramitaciones</h2>
                    <p class="mt-1 text-sm text-slate-500">Estado actual de tus solicitudes en curso.</p>
                </div>
            </div>

            <div class="space-y-4">
                {{-- Ausencias --}}
                @forelse($myAbsences as $absence)
                    <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                    Ausencia
                                </span>
                                <p class="mt-2 font-semibold text-slate-900">{{ $absence->description ?: $absence->awart }}</p>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $absence->begda->format('d/m/Y') }} — {{ $absence->endda->format('d/m/Y') }}
                                </p>
                            </div>
                            <span class="text-xs font-semibold text-slate-400">{{ $absence->created_at->format('d/m/Y') }}</span>
                        </div>

                        {{-- Stepper ausencia --}}
                        <div class="mt-6 flex items-center gap-0">
                            {{-- Paso 1: Empleado --}}
                            <div class="flex flex-col items-center gap-2" style="min-width:80px">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full {{ $absence->employee_signed_at ? 'bg-emerald-500' : 'bg-slate-200' }}">
                                    @if($absence->employee_signed_at)
                                        <span class="text-sm font-bold text-white">✓</span>
                                    @endif
                                </div>
                                <p class="text-center text-xs font-semibold text-slate-700">{{ $user->name }}</p>
                                <p class="text-center text-[11px] text-slate-400">
                                    {{ $absence->employee_signed_at ? $absence->employee_signed_at->format('d/m/Y') : 'Pendiente' }}
                                </p>
                            </div>

                            {{-- Línea 1 --}}
                            <div class="h-0.5 flex-1 {{ $absence->employee_signed_at ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>

                            {{-- Paso 2: Firmante --}}
                            <div class="flex flex-col items-center gap-2" style="min-width:80px">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full {{ $absence->signer_signed_at ? 'bg-emerald-500' : 'bg-slate-200' }}">
                                    @if($absence->signer_signed_at)
                                        <span class="text-sm font-bold text-white">✓</span>
                                    @endif
                                </div>
                                <p class="text-center text-xs font-semibold text-slate-700">{{ $absence->signer?->name ?? 'Firmante' }}</p>
                                <p class="text-center text-[11px] text-slate-400">
                                    {{ $absence->signer_signed_at ? $absence->signer_signed_at->format('d/m/Y') : 'Pendiente' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                        No tienes ausencias en curso.
                    </div>
                @endforelse

                {{-- Gastos --}}
                @forelse($myExpenses as $expense)
                    <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-100">
                                    Gasto
                                </span>
                                <p class="mt-2 font-semibold text-slate-900">{{ $expense->description ?: $expense->title }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $expense->status->name ?? '-' }}</p>
                            </div>
                            <span class="text-xs font-semibold text-slate-400">{{ $expense->created_at->format('d/m/Y') }}</span>
                        </div>

                        {{-- Stepper gasto --}}
                        <div class="mt-6 flex items-center gap-0">
                            {{-- Paso 1: Solicitante --}}
                            <div class="flex flex-col items-center gap-2" style="min-width:80px">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full {{ $expense->submitted_at ? 'bg-emerald-500' : 'bg-slate-200' }}">
                                    @if($expense->submitted_at)
                                        <span class="text-sm font-bold text-white">✓</span>
                                    @endif
                                </div>
                                <p class="text-center text-xs font-semibold text-slate-700">{{ $user->name }}</p>
                                <p class="text-center text-[11px] text-slate-400">
                                    {{ $expense->submitted_at ? \Carbon\Carbon::parse($expense->submitted_at)->format('d/m/Y') : 'Pendiente' }}
                                </p>
                            </div>

                            {{-- Línea 1 --}}
                            <div class="h-0.5 flex-1 {{ $expense->submitted_at ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>

                            {{-- Paso 2: Jefe --}}
                            <div class="flex flex-col items-center gap-2" style="min-width:80px">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full {{ $expense->approved_at ? 'bg-emerald-500' : 'bg-slate-200' }}">
                                    @if($expense->approved_at)
                                        <span class="text-sm font-bold text-white">✓</span>
                                    @endif
                                </div>
                                <p class="text-center text-xs font-semibold text-slate-700">{{ $expense->approver?->name ?? 'Jefe' }}</p>
                                <p class="text-center text-[11px] text-slate-400">
                                    {{ $expense->approved_at ? \Carbon\Carbon::parse($expense->approved_at)->format('d/m/Y') : 'Pendiente' }}
                                </p>
                            </div>

                            {{-- Línea 2 --}}
                            <div class="h-0.5 flex-1 {{ $expense->approved_at ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>

                            {{-- Paso 3: Admin --}}
                            <div class="flex flex-col items-center gap-2" style="min-width:80px">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full {{ $expense->sap_exported_at ? 'bg-emerald-500' : 'bg-slate-200' }}">
                                    @if($expense->sap_exported_at)
                                        <span class="text-sm font-bold text-white">✓</span>
                                    @endif
                                </div>
                                <p class="text-center text-xs font-semibold text-slate-700">{{ $expense->admin?->name ?? 'Administración' }}</p>
                                <p class="text-center text-[11px] text-slate-400">
                                    {{ $expense->sap_exported_at ? \Carbon\Carbon::parse($expense->sap_exported_at)->format('d/m/Y') : 'Pendiente' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                        No tienes gastos en curso.
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Actividad -->
        <section class="grid gap-8 xl:grid-cols-2">
            <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Últimas solicitudes</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Actividad reciente registrada en el sistema.
                        </p>
                    </div>

                    <a href="#" class="text-sm font-semibold text-emerald-700 transition hover:text-emerald-800">
                        Ver todas
                    </a>
                </div>

                <div class="overflow-x-auto px-6 py-4">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-100 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">
                                <th class="pb-4 pr-4">Solicitud</th>
                                <th class="pb-4 pr-4">Tipo</th>
                                <th class="pb-4 pr-4">Estado</th>
                                <th class="pb-4">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-slate-700">
                            @forelse($recentRequests as $request)
                                <tr class="border-b border-slate-50 last:border-b-0">
                                    <td class="py-4 pr-4 font-medium text-slate-900">
                                        {{ $request['title'] }}
                                    </td>
                                    <td class="py-4 pr-4">
                                        {{ $request['type'] }}
                                    </td>
                                    <td class="py-4 pr-4">
                                        <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-100">
                                            {{ $request['status'] }}
                                        </span>
                                    </td>
                                    <td class="py-4">
                                        {{ $request['date'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-sm text-slate-500">
                                        No hay solicitudes recientes.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Pendientes de aprobación</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Solicitudes que requieren revisión.
                        </p>
                    </div>

                    <a
                        href="{{ route('pending-approvals') }}"
                        class="text-sm font-semibold text-emerald-700 transition hover:text-emerald-800"
                    >
                        Ver bandeja
                    </a>
                </div>

                <div class="space-y-4 px-6 py-5">
                    @forelse($pendingApprovals as $approval)
                        <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4 transition hover:bg-slate-100">
                            <div>
                                <p class="font-semibold text-slate-900">
                                    {{ $approval['employee'] }}
                                </p>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $approval['type'] }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-sm font-medium text-slate-700">
                                    {{ $approval['date'] }}
                                </p>
                                <span class="mt-2 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                    Pendiente
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                            No hay aprobaciones pendientes.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection