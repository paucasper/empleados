@extends('layouts.app')

@section('body')
<div class="min-h-screen bg-gradient-to-br from-[#f2f4ed] via-[#e8ede0] to-[#d9e0ce] text-[#1a1c15] selection:bg-[#c5a35d]/30">
    <div class="flex min-h-screen items-center justify-center px-4 py-12">
        
        <div class="grid w-full max-w-6xl overflow-hidden rounded-[48px] bg-white/70 backdrop-blur-2xl shadow-[0_40px_100px_-20px_rgba(26,43,21,0.15)] border border-white/50 lg:grid-cols-12">

            <div class="hidden lg:flex flex-col justify-between p-16 relative overflow-hidden lg:col-span-5 bg-[#1a2b15]">
                <div class="absolute inset-0 z-0">
                    <img 
                        src="https://images.unsplash.com/photo-1464965211596-f68200676450?q=80&w=2070&auto=format&fit=crop" 
                        class="h-full w-full object-cover opacity-30 mix-blend-luminosity"
                        alt="Background"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-[#1a2b15] via-[#1a2b15]/60 to-transparent"></div>
                </div>

                <div class="relative z-10">
                    <img src="{{ asset('images/logo_sin_fondo_recortado.png') }}" alt="Dcoop" class="h-16 w-auto brightness-0 invert" />
                    
                    <div class="mt-28">
                        <div class="flex items-center gap-2 mb-6">
                            <span class="h-[1px] w-6 bg-[#c5a35d]"></span>
                            <span class="text-[10px] font-bold uppercase tracking-[0.4em] text-[#c5a35d]">Portal Corporativo</span>
                        </div>
                        <h2 class="text-5xl font-extralight leading-[1.15] text-white">
                            El valor del <br><span class="font-serif italic text-[#c5a35d]">esfuerzo</span> común.
                        </h2>
                        <p class="mt-8 max-w-xs text-base leading-relaxed text-white/50 font-light">
                            Gestione sus ausencias, reporte sus gastos y consulte su calendario en un solo lugar.
                        </p>
                    </div>
                </div>

                <div class="relative z-10">
                    <p class="text-[11px] uppercase tracking-widest text-white/30 font-medium">© 2026 Dcoop S.C.A. · v2.0</p>
                </div>
            </div>

            <div class="flex items-center justify-center bg-white/40 p-8 sm:p-12 lg:p-24 lg:col-span-7">
                <div class="w-full max-w-[380px]">
                    <div class="mb-12">
                        <h1 class="text-4xl font-semibold tracking-tight text-[#1a2b15]">Bienvenido</h1>
                        <p class="mt-4 text-[#6b7280] font-light leading-relaxed">
                            Por favor, introduzca su cuenta de empleado para acceder a las tramitaciones.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('login') }}" class="space-y-6">
                        @csrf

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold uppercase tracking-widest text-[#1a2b15]/60 ml-1">Email</label>
                            <input
                                id="email" type="email" name="email" required autofocus
                                placeholder="nombre@dcoop.es"
                                class="w-full rounded-2xl border-0 bg-white px-5 py-4 text-sm shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] transition-all placeholder:text-gray-300 focus:ring-2 focus:ring-[#1a2b15] outline-none"
                            >
                        </div>

                        <div class="space-y-2">
                            <div class="flex justify-between items-center ml-1">
                                <label class="text-[11px] font-bold uppercase tracking-widest text-[#1a2b15]/60">Contraseña</label>
                                <a href="#" class="text-[11px] font-bold text-[#c5a35d] hover:text-[#a6894d] transition-colors">¿Problemas al entrar?</a>
                            </div>
                            <input
                                id="password" type="password" name="password" required
                                placeholder="••••••••"
                                class="w-full rounded-2xl border-0 bg-white px-5 py-4 text-sm shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05)] transition-all placeholder:text-gray-300 focus:ring-2 focus:ring-[#1a2b15] outline-none"
                            >
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 text-[#1a2b15] focus:ring-[#1a2b15]/20">
                            <span class="text-sm text-[#6b7280]">Recordar mi sesión</span>
                        </div>

                        <button
                            type="submit"
                            class="group relative w-full overflow-hidden rounded-2xl bg-[#1a2b15] py-4 text-sm font-bold text-white shadow-2xl transition-all hover:scale-[1.02] active:scale-[0.98]"
                        >
                            <span class="relative z-10">INICIAR SESIÓN</span>
                            <div class="absolute inset-0 bg-gradient-to-r from-[#1a2b15] via-[#2d4a27] to-[#1a2b15] opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        </button>
                    </form>

                    <div class="mt-16 flex items-center justify-between border-t border-gray-100 pt-8">
                        <img src="{{ asset('images/iso-dcoop.png') }}" class="h-6 opacity-20" alt="">
                        <p class="text-[10px] text-gray-400 font-medium">DCOOP DIGITAL ECOSYSTEM</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection