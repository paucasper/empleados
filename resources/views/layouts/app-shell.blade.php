@extends('layouts.app')

@section('body')
    <div class="min-h-screen bg-slate-100">
        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <aside class="hidden w-72 flex-col border-r border-slate-200 bg-white lg:flex sticky top-0 h-screen overflow-y-auto">
                <div class="border-b border-slate-200 px-6 py-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500 text-xl font-bold text-white shadow-sm">
                            D
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-400">
                                Portal interno
                            </p>
                            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">
                                Tramitaciones
                            </h1>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-6">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">
                        Navegación
                    </p>

                    <nav class="mt-4 space-y-2">
                        <a
                            href="{{ route('dashboard') }}"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition
                            {{ request()->routeIs('dashboard')
                                ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100'
                                : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span>Inicio</span>
                        </a>

                        <a
                            href="{{ route('pending-approvals') }}"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition
                            {{ request()->routeIs('pending-approvals')
                                ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100'
                                : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span>Bandeja de entrada</span>
                        </a>

                        <a
                            href="{{ route('vacations') }}"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition
                            {{ request()->routeIs('vacations')
                                ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100'
                                : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span>Ausencias</span>
                        </a>

                        <a
                            href="{{ route('expenses') }}"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition
                            {{ request()->routeIs('expenses')
                                ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100'
                                : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span>Gastos</span>
                        </a>

                        @if(auth()->user()->role === 'admin')
                            <a
                                href="#"
                                class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                            >
                                <span>Administración</span>
                            </a>
                        @endif
                    </nav>
                </div>

                <div class="mt-auto border-t border-slate-200 p-4">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">
                            Sesión activa
                        </p>
                        <p class="mt-2 text-base font-semibold text-slate-900">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ auth()->user()->email ?? 'Usuario autenticado' }}
                        </p>

                        <form method="POST" action="{{ route('logout') }}" class="mt-4">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                            >
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Main -->
            <div class="flex min-w-0 flex-1 flex-col">
                <!-- Mobile header -->
                <div class="border-b border-slate-200 bg-white px-4 py-4 lg:hidden">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-500 text-lg font-bold text-white">
                                D
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-[0.2em] text-slate-400">Portal interno</p>
                                <p class="text-lg font-semibold text-slate-900">Tramitaciones</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700"
                            >
                                Salir
                            </button>
                        </form>
                    </div>
                </div>

                <main class="flex-1">
                    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    </div>
@endsection