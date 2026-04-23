@extends('layouts.app')

@section('body')
    <div x-data="{ mobileMenuOpen: false }" class="min-h-screen bg-[#f6f7f2]">
        <div class="flex min-h-screen">
            <!-- Sidebar desktop -->
            <aside class="hidden w-72 flex-col border-r border-[#e7eadf] bg-white lg:flex sticky top-0 h-screen overflow-y-auto">
                <div class="border-b border-[#e7eadf] px-6 py-6">
                    <div class="flex items-center gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-gray-400">
                                Portal interno
                            </p>
                            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-[#2f4a27]">
                                Tramitaciones
                            </h1>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-6">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">
                        Navegación
                    </p>

                    <nav class="mt-4 space-y-2">
                        <a
                            href="{{ route('dashboard') }}"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition
                            {{ request()->routeIs('dashboard')
                                ? 'bg-[#f2f4ed] text-[#2f4a27] ring-1 ring-[#dfe6d6]'
                                : 'text-gray-600 hover:bg-[#fcfcf9] hover:text-[#2f4a27]' }}"
                        >
                            <span>Inicio</span>
                        </a>

                        <a
                            href="{{ route('pending-approvals') }}"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition
                            {{ request()->routeIs('pending-approvals')
                                ? 'bg-[#f2f4ed] text-[#2f4a27] ring-1 ring-[#dfe6d6]'
                                : 'text-gray-600 hover:bg-[#fcfcf9] hover:text-[#2f4a27]' }}"
                        >
                            <span>Bandeja de entrada</span>
                        </a>

                        <a
                            href="{{ route('vacations') }}"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition
                            {{ request()->routeIs('vacations')
                                ? 'bg-[#f2f4ed] text-[#2f4a27] ring-1 ring-[#dfe6d6]'
                                : 'text-gray-600 hover:bg-[#fcfcf9] hover:text-[#2f4a27]' }}"
                        >
                            <span>Ausencias</span>
                        </a>

                        <a
                            href="{{ route('expenses') }}"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition
                            {{ request()->routeIs('expenses')
                                ? 'bg-[#f2f4ed] text-[#2f4a27] ring-1 ring-[#dfe6d6]'
                                : 'text-gray-600 hover:bg-[#fcfcf9] hover:text-[#2f4a27]' }}"
                        >
                            <span>Gastos</span>
                        </a>

                        <a
                            href="{{ route('calendar') }}"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition
                            {{ request()->routeIs('calendar')
                                ? 'bg-[#f2f4ed] text-[#2f4a27] ring-1 ring-[#dfe6d6]'
                                : 'text-gray-600 hover:bg-[#fcfcf9] hover:text-[#2f4a27]' }}"
                        >
                            <span>Calendario</span>
                        </a>

                        @if(auth()->user()->role === 'admin')
                            <a
                                href="#"
                                class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-gray-600 transition hover:bg-[#fcfcf9] hover:text-[#2f4a27]"
                            >
                                <span>Administración</span>
                            </a>
                        @endif
                    </nav>
                </div>

                <div class="mt-auto border-t border-[#e7eadf] p-4">
                    <div class="rounded-2xl bg-[#fcfcf9] p-4 border border-[#efe7d6]">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-gray-400">
                            Sesión activa
                        </p>
                        <p class="mt-2 text-base font-semibold text-[#2f4a27]">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ auth()->user()->email ?? 'Usuario autenticado' }}
                        </p>

                        <form method="POST" action="{{ route('logout') }}" class="mt-4">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-[#e7eadf] bg-white px-4 py-2.5 text-sm font-semibold text-[#2f4a27] transition hover:bg-[#f2f4ed]"
                            >
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Mobile menu overlay -->
            <div
                x-show="mobileMenuOpen"
                x-transition.opacity
                class="fixed inset-0 z-40 bg-black/30 lg:hidden"
                @click="mobileMenuOpen = false"
            ></div>

            <!-- Mobile sidebar -->
            <aside
                x-show="mobileMenuOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-[#e7eadf] bg-white shadow-xl lg:hidden"
            >
                <div class="border-b border-[#e7eadf] px-6 py-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-gray-400">
                                Portal interno
                            </p>
                            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-[#2f4a27]">
                                Tramitaciones
                            </h1>
                        </div>

                        <button
                            type="button"
                            @click="mobileMenuOpen = false"
                            class="rounded-xl border border-[#e7eadf] bg-white px-3 py-2 text-sm font-medium text-[#2f4a27] transition hover:bg-[#f2f4ed]"
                        >
                            ✕
                        </button>
                    </div>
                </div>

                <div class="px-4 py-6 overflow-y-auto">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">
                        Navegación
                    </p>

                    <nav class="mt-4 space-y-2">
                        <a
                            href="{{ route('dashboard') }}"
                            @click="mobileMenuOpen = false"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition
                            {{ request()->routeIs('dashboard')
                                ? 'bg-[#f2f4ed] text-[#2f4a27] ring-1 ring-[#dfe6d6]'
                                : 'text-gray-600 hover:bg-[#fcfcf9] hover:text-[#2f4a27]' }}"
                        >
                            <span>Inicio</span>
                        </a>

                        <a
                            href="{{ route('pending-approvals') }}"
                            @click="mobileMenuOpen = false"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition
                            {{ request()->routeIs('pending-approvals')
                                ? 'bg-[#f2f4ed] text-[#2f4a27] ring-1 ring-[#dfe6d6]'
                                : 'text-gray-600 hover:bg-[#fcfcf9] hover:text-[#2f4a27]' }}"
                        >
                            <span>Bandeja de entrada</span>
                        </a>

                        <a
                            href="{{ route('vacations') }}"
                            @click="mobileMenuOpen = false"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition
                            {{ request()->routeIs('vacations')
                                ? 'bg-[#f2f4ed] text-[#2f4a27] ring-1 ring-[#dfe6d6]'
                                : 'text-gray-600 hover:bg-[#fcfcf9] hover:text-[#2f4a27]' }}"
                        >
                            <span>Ausencias</span>
                        </a>

                        <a
                            href="{{ route('expenses') }}"
                            @click="mobileMenuOpen = false"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition
                            {{ request()->routeIs('expenses')
                                ? 'bg-[#f2f4ed] text-[#2f4a27] ring-1 ring-[#dfe6d6]'
                                : 'text-gray-600 hover:bg-[#fcfcf9] hover:text-[#2f4a27]' }}"
                        >
                            <span>Gastos</span>
                        </a>

                        <a
                            href="{{ route('calendar') }}"
                            @click="mobileMenuOpen = false"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition
                            {{ request()->routeIs('calendar')
                                ? 'bg-[#f2f4ed] text-[#2f4a27] ring-1 ring-[#dfe6d6]'
                                : 'text-gray-600 hover:bg-[#fcfcf9] hover:text-[#2f4a27]' }}"
                        >
                            <span>Calendario</span>
                        </a>

                        @if(auth()->user()->role === 'admin')
                            <a
                                href="#"
                                @click="mobileMenuOpen = false"
                                class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-gray-600 transition hover:bg-[#fcfcf9] hover:text-[#2f4a27]"
                            >
                                <span>Administración</span>
                            </a>
                        @endif
                    </nav>
                </div>

                <div class="mt-auto border-t border-[#e7eadf] p-4">
                    <div class="rounded-2xl bg-[#fcfcf9] p-4 border border-[#efe7d6]">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-gray-400">
                            Sesión activa
                        </p>
                        <p class="mt-2 text-base font-semibold text-[#2f4a27]">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ auth()->user()->email ?? 'Usuario autenticado' }}
                        </p>

                        <form method="POST" action="{{ route('logout') }}" class="mt-4">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-[#e7eadf] bg-white px-4 py-2.5 text-sm font-semibold text-[#2f4a27] transition hover:bg-[#f2f4ed]"
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
                <div class="border-b border-[#e7eadf] bg-white px-4 py-4 lg:hidden">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="mobileMenuOpen = true"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-[#e7eadf] bg-white text-[#2f4a27] shadow-sm transition hover:bg-[#f2f4ed]"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>

                            <div>
                                <p class="text-[11px] uppercase tracking-[0.2em] text-gray-400">Portal interno</p>
                                <p class="text-lg font-semibold text-[#2f4a27]">Tramitaciones</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="rounded-xl border border-[#e7eadf] bg-white px-3 py-2 text-sm font-medium text-[#2f4a27] transition hover:bg-[#f2f4ed]"
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