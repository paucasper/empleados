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