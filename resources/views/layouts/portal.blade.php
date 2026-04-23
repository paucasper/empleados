@extends('layouts.app')

@section('body')
    <div class="min-h-screen bg-[#f8f9f5]">
        <header class="bg-[#2d4a2a] text-white shadow-xl relative overflow-hidden">
            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-[#c5a35d]/10 blur-3xl"></div>
            
            <div class="relative mx-auto flex max-w-7xl flex-col gap-6 px-6 py-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-5">
                    <div class="flex h-16 w-16 items-center justify-center rounded-[1.25rem] bg-white/10 text-2xl font-serif italic text-[#c5a35d] backdrop-blur-sm border border-white/10 shadow-inner">
                        D
                    </div>

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-[#e5cf9e]/80">Ecosistema Digital</p>
                        <h1 class="text-4xl font-light tracking-tight md:text-5xl">
                            Tramitaciones <span class="font-serif italic text-[#c5a35d]">Internas</span>
                        </h1>
                    </div>
                </div>

                <div class="flex flex-col border-l border-white/10 pl-6 lg:text-right">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#e5cf9e]/60">Sesión activa</p>
                    <p class="text-xl font-medium tracking-tight">{{ auth()->user()->name }}</p>

                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="group flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-white/50 transition hover:text-[#c5a35d] lg:justify-end">
                            <span>Cerrar sesión</span>
                            <span class="transition-transform group-hover:translate-x-1">→</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <nav class="sticky top-0 z-50 border-b border-gray-100 bg-white/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-1 px-6 py-3 text-[11px] font-bold uppercase tracking-widest">
                <a href="{{ route('dashboard') }}"
                   class="rounded-xl px-5 py-2.5 transition-all {{ request()->routeIs('dashboard') ? 'bg-[#2d4a2a] text-white shadow-lg' : 'text-gray-500 hover:bg-gray-50 hover:text-[#2d4a2a]' }}">
                    Inicio
                </a>

                <a href="{{ route('pending-approvals') }}"
                   class="rounded-xl px-5 py-2.5 transition-all {{ request()->routeIs('pending-approvals') ? 'bg-[#2d4a2a] text-white shadow-lg' : 'text-gray-500 hover:bg-gray-50 hover:text-[#2d4a2a]' }}">
                    Bandeja Entrada
                </a>

                <a href="{{ route('vacations') }}"
                   class="rounded-xl px-5 py-2.5 transition-all {{ request()->routeIs('vacations') ? 'bg-[#2d4a2a] text-white shadow-lg' : 'text-gray-500 hover:bg-gray-50 hover:text-[#2d4a2a]' }}">
                    Nueva Ausencia
                </a>

                <a href="#"
                   class="rounded-xl px-5 py-2.5 text-gray-400 transition-all hover:bg-gray-50 hover:text-[#2d4a2a]">
                    Nuevo Gasto
                </a>

                @if(auth()->user()->role === 'admin')
                    <div class="ml-auto">
                        <a href="#"
                           class="rounded-xl border border-[#c5a35d]/20 bg-[#fcfcf9] px-5 py-2.5 text-[#c5a35d] transition-all hover:bg-[#c5a35d] hover:text-white">
                            Administración
                        </a>
                    </div>
                @endif
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-6 py-12">
            @yield('content')
        </main>
        
        <footer class="mx-auto max-w-7xl px-6 pb-12">
            <div class="border-t border-gray-200 pt-8 text-center">
                <p class="text-[10px] font-medium uppercase tracking-[0.5em] text-gray-400">
                    Dcoop Sistemas · Proceso de Gestión de Activos
                </p>
            </div>
        </footer>
    </div>
@endsection