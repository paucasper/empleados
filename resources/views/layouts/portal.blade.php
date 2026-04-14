@extends('layouts.app')

@section('body')
    <div class="min-h-screen bg-gray-100">
        <!-- Header -->
        <header class="bg-green-800 text-white shadow">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-2xl font-bold">
                        D
                    </div>

                    <div>
                        <p class="text-sm uppercase tracking-[0.2em] text-green-100">Portal interno</p>
                        <h1 class="text-3xl font-light md:text-5xl">Tramitaciones</h1>
                    </div>
                </div>

                <div class="text-left lg:text-right">
                    <p class="text-sm text-green-100">Bienvenido/a</p>
                    <p class="text-lg font-semibold">{{ auth()->user()->name }}</p>

                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button
                            type="submit"
                            class="text-sm text-green-100 underline transition hover:text-white"
                        >
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Navbar -->
        <nav class="border-b border-green-900 bg-lime-700 text-white shadow-sm">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-2 px-6 py-3 text-sm font-semibold md:gap-4">
                <a href="{{ route('dashboard') }}"
                   class="rounded-lg px-4 py-2 transition hover:bg-white/10 {{ request()->routeIs('dashboard') ? 'bg-white/15' : '' }}">
                    Inicio
                </a>

                <a href="{{ route('pending-approvals') }}"
                   class="rounded-lg px-4 py-2 transition hover:bg-white/10 {{ request()->routeIs('pending-approvals') ? 'bg-white/15' : '' }}">
                    Bandeja de entrada
                </a>

                <a href="{{ route('vacations') }}"
                   class="rounded-lg px-4 py-2 transition hover:bg-white/10 {{ request()->routeIs('vacations') ? 'bg-white/15' : '' }}">
                    Nueva ausencia
                </a>

                <a href="#"
                   class="rounded-lg px-4 py-2 transition hover:bg-white/10">
                    Nuevo gastos
                </a>

                @if(auth()->user()->role === 'admin')
                    <a href="#"
                       class="rounded-lg px-4 py-2 transition hover:bg-white/10">
                        Administración
                    </a>
                @endif
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-6 py-8">
            @yield('content')
        </main>
    </div>
@endsection