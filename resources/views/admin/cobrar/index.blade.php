@extends('layouts.admin')

@section('title', 'Cobrar Mesa | El Brasero')
@section('no-sidebar', 'true')

@section('content')
@php
    $esPorProducto = ($division['tipo'] ?? null) === 'por_producto';
    $anchoIzquierda = $esPorProducto ? 'lg:w-3/5' : 'lg:w-2/5';
    $anchoDerecha   = $esPorProducto ? 'lg:w-2/5' : 'lg:w-3/5';
@endphp
<div class="flex flex-col lg:flex-row min-h-screen lg:h-screen lg:overflow-hidden transition-colors duration-300" style="background-color: var(--bg-color); color: var(--text-color);">
    
    {{-- IZQUIERDA: Detalle --}}
    <div class="w-full {{ $anchoIzquierda }} border-r flex flex-col border-b lg:border-b-0 lg:overflow-hidden lg:min-h-0 shadow-sm transition-colors duration-300" style="background-color: var(--card-color); border-color: var(--border-color);">
        <div class="p-4 sm:p-5 border-b" style="border-color: var(--border-color);">
            <a href="{{ route('admin.caja.index') }}" class="text-[10px] font-black flex items-center gap-2 mb-1 transition-all hover:translate-x-1 uppercase tracking-widest hover:text-[#b74309]" style="color: var(--text-muted);">
                <i class="fas fa-arrow-left"></i> VOLVER A CAJA
            </a>
            
            <h1 class="text-2xl sm:text-3xl font-black italic tracking-tighter uppercase break-words" style="color: var(--text-color);">
                @if($mesa->esDelivery())
                    <i class="fas fa-motorcycle text-orange-500 mr-1"></i> {{ $mesa->plataformaDelivery->nombre ?? 'Delivery' }} · {{ $mesa->numero }}
                @else
                    Mesa {{ $mesa->numero }}
                @endif
            </h1>
            
            <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wide mt-0.5" style="color: var(--text-muted);">
                {{ $orden->numero_orden ?? 'ORDEN SIN NÚMERO' }} • {{ $orden->mesero->nombre ?? 'MESERO NO ASIGNADO' }}
            </p>

            {{-- DESCUENTO DE LA CUENTA --}}
            @if(auth()->user()->tienePermiso('Caja', 'editar'))
                <div class="mt-3 flex items-center gap-2">
                    <div class="relative">
                        <input type="text" inputmode="decimal" data-teclado="numerico"
                               id="input-descuento-caja"
                               value="{{ ($descuentoPorcentaje ?? 0) > 0 ? rtrim(rtrim(number_format($descuentoPorcentaje, 2, '.', ''), '0'), '.') : '' }}"
                               placeholder="0"
                               class="w-20 pl-3 pr-6 py-1.5 rounded-lg border text-sm font-bold outline-none transition-colors focus:ring-2 focus:ring-[#b74309] focus:border-[#b74309]"
                               style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);">
                        <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-bold" style="color: var(--text-muted);">%</span>
                    </div>
                    <button type="button" id="btn-aplicar-descuento-caja"
                        class="px-3 py-1.5 rounded-lg bg-[#b74309] hover:bg-[#8f3207] text-white text-[10px] font-black uppercase tracking-wider transition-colors shadow-sm cursor-pointer active:scale-95">
                        Aplicar descuento
                    </button>
                    <span id="msg-descuento-caja" class="hidden text-[11px] font-bold"></span>
                </div>
            @endif

            {{-- CANCELAR CUENTA SIN COBRAR --}}
            @if(auth()->user()->tienePermiso('Caja', 'eliminar'))
                <button type="button" id="btn-abrir-cancelar-cuenta"
                    class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-rose-500/30 text-rose-500 text-[10px] font-black uppercase tracking-wider hover:bg-rose-500/10 transition-colors cursor-pointer">
                    <i class="fas fa-ban"></i> Cancelar cuenta sin cobrar
                </button>
            @endif
        </div>

        <div class="flex-1 lg:min-h-0 lg:overflow-y-auto custom-scrollbar">
            @include('admin.cobrar.partials.detalle-cuenta')
        </div>
    </div>

    {{-- DERECHA: Pago --}}
    @include('admin.cobrar.partials.panel-pago')
</div>

{{-- Modales integrados --}}
@include('admin.cobrar.modals.metodo-pago')
@include('admin.cobrar.modals.exito')
@include('admin.cobrar.modals.error')
@include('admin.cobrar.modals.ticket-preview')
@if(auth()->user()->tienePermiso('Caja', 'eliminar'))
    @include('admin.cobrar.modals.cancelar-cuenta')
@endif
@endsection

{{-- ═══════════════════════════════════════════════════════
     MODAL NIP — Compartido para Descuento y Cancelar Cuenta
     ════════════════════════════════════════════════════════ --}}
<div id="modal-nip-caja" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="cerrarModalNipCaja()"></div>
    <div class="modal-container relative w-full max-w-xs rounded-3xl shadow-2xl border overflow-hidden"
         style="background-color: var(--card-color); border-color: var(--border-color);">

        {{-- Header --}}
        <div id="mnc-header" class="px-6 py-4 border-b flex items-center justify-between" style="border-color: var(--border-color);">
            <div class="flex items-center gap-3">
                <div id="mnc-icono-wrap" class="w-9 h-9 rounded-xl flex items-center justify-center">
                    <i id="mnc-icono" class="fas fa-lock text-sm"></i>
                </div>
                <div>
                    <h3 id="mnc-titulo" class="text-sm font-black" style="color: var(--text-color);">Autorización</h3>
                    <p id="mnc-subtitulo" class="text-[11px]" style="color: var(--text-muted);">Ingresa el NIP del Administrador</p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalNipCaja()"
                class="w-8 h-8 rounded-xl border flex items-center justify-center transition-colors cursor-pointer"
                style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-muted);">
                <i class="fas fa-xmark text-xs"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 space-y-4">
            {{-- Dots --}}
            <div class="flex justify-center gap-3 py-1">
                @for($i = 0; $i < 4; $i++)
                    <div class="mnc-dot w-4 h-4 rounded-full border-2 bg-transparent transition-all duration-150" style="border-color: var(--border-color);"></div>
                @endfor
            </div>

            {{-- Teclado --}}
            <div class="grid grid-cols-3 gap-2">
                @foreach(['1','2','3','4','5','6','7','8','9'] as $k)
                    <button type="button" onclick="mncEscribir('{{ $k }}')"
                        class="h-12 rounded-2xl border font-black text-lg transition-all active:scale-95 cursor-pointer hover:border-[#b74309]/50"
                        style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);">
                        {{ $k }}
                    </button>
                @endforeach
                <button type="button" onclick="mncBorrar()"
                    class="h-12 rounded-2xl border bg-rose-500/10 border-rose-500/20 text-rose-500 flex items-center justify-center hover:bg-rose-500/20 active:scale-95 transition-all cursor-pointer">
                    <i class="fas fa-delete-left text-base"></i>
                </button>
                <button type="button" onclick="mncEscribir('0')"
                    class="h-12 rounded-2xl border font-black text-lg transition-all active:scale-95 cursor-pointer hover:border-[#b74309]/50"
                    style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-color);">
                    0
                </button>
                <button type="button" id="mnc-btn-ok" onclick="mncConfirmar()"
                    class="h-12 rounded-2xl bg-[#b74309] hover:bg-[#8f3207] text-white font-black text-sm active:scale-95 transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-md shadow-[#b74309]/20">
                    <i class="fas fa-check text-xs"></i> OK
                </button>
            </div>

            {{-- Error --}}
            <p id="mnc-error" class="hidden text-center text-xs font-bold text-rose-500 bg-rose-500/10 border border-rose-500/20 rounded-xl py-2 px-3"></p>
        </div>
    </div>
</div>

<script>
(function () {
    let _mncNip      = '';
    let _mncCallback = null;

    const dots    = () => document.querySelectorAll('.mnc-dot');
    const errorEl = () => document.getElementById('mnc-error');
    const btnOk   = () => document.getElementById('mnc-btn-ok');

    function actualizarDots() {
        dots().forEach((d, i) => {
            if (i < _mncNip.length) {
                d.style.backgroundColor = 'var(--text-color)';
                d.style.borderColor = 'var(--text-color)';
            } else {
                d.style.backgroundColor = 'transparent';
                d.style.borderColor = 'var(--border-color)';
            }
        });
    }

    window.abrirModalNipCaja = function ({ titulo, subtitulo, icono, colorIcono, onConfirm }) {
        _mncNip      = '';
        _mncCallback = onConfirm;

        document.getElementById('mnc-titulo').textContent    = titulo || 'Autorización';
        document.getElementById('mnc-subtitulo').textContent = subtitulo || 'Ingresa el NIP del Administrador';

        const wrap = document.getElementById('mnc-icono-wrap');
        const ico  = document.getElementById('mnc-icono');
        wrap.className = `w-9 h-9 rounded-xl flex items-center justify-center bg-[#b74309]/15 border border-[#b74309]/20`;
        ico.className  = `fas ${icono || 'fa-lock'} text-[#b74309] text-sm`;

        actualizarDots();
        if (errorEl()) errorEl().classList.add('hidden');
        document.getElementById('modal-nip-caja').classList.remove('hidden');
    };

    window.cerrarModalNipCaja = function () {
        document.getElementById('modal-nip-caja').classList.add('hidden');
        _mncNip = '';
        _mncCallback = null;
    };

    window.mncEscribir = function (digit) {
        if (_mncNip.length >= 4) return;
        _mncNip += digit;
        actualizarDots();
        if (errorEl()) errorEl().classList.add('hidden');
        if (_mncNip.length === 4) mncConfirmar();
    };

    window.mncBorrar = function () {
        _mncNip = _mncNip.slice(0, -1);
        actualizarDots();
    };

    window.mncConfirmar = async function () {
        if (!_mncNip || _mncNip.length < 1) return;
        if (!_mncCallback) return;

        const btn = btnOk();
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>'; }

        try {
            await _mncCallback(_mncNip);
        } catch (err) {
            const el = errorEl();
            if (el) { el.textContent = err.message || 'Error al verificar.'; el.classList.remove('hidden'); }
            _mncNip = '';
            actualizarDots();
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check text-xs"></i> OK'; }
        }
    };

    document.addEventListener('keydown', function (e) {
        const modal = document.getElementById('modal-nip-caja');
        if (!modal || modal.classList.contains('hidden')) return;
        if (e.key === 'Escape') cerrarModalNipCaja();
        else if (e.key === 'Backspace') mncBorrar();
        else if (/^[0-9]$/.test(e.key)) mncEscribir(e.key);
    });
})();
</script>

@push('scripts')
@vite(['resources/js/cobro.js'])
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.COBRO_CONFIG = {
            mesaId: {{ $mesa->id }},
            urlTicket: "{{ route('admin.caja.ticket.imprimir', $mesa->id) }}",
            total: {{ $totalPagar ?? 0 }},
            csrfToken: "{{ csrf_token() }}",
            urlPago: "{{ route('admin.caja.procesar-pago') }}",
            urlDivisionIniciar: "{{ route('admin.caja.division.iniciar') }}",
            urlDivisionAsignar: "{{ route('admin.caja.division.asignar') }}",
            urlDivisionCancelar: "{{ route('admin.caja.division.cancelar') }}",
            division: @json($division ?? null)
        };

        const btnDescuento = document.getElementById('btn-aplicar-descuento-caja');
        const inputDescuento = document.getElementById('input-descuento-caja');
        const msgDescuento = document.getElementById('msg-descuento-caja');

        if (btnDescuento && inputDescuento) {
            btnDescuento.addEventListener('click', () => {
                const crudo = (inputDescuento.value || '').trim().replace(',', '.');
                const porcentaje = crudo === '' ? 0 : parseFloat(crudo);

                const avisar = (texto, ok) => {
                    msgDescuento.textContent = texto;
                    msgDescuento.className = 'text-[11px] font-bold ' + (ok ? 'text-emerald-500' : 'text-rose-500');
                    msgDescuento.classList.remove('hidden');
                };

                if (isNaN(porcentaje) || porcentaje < 0 || porcentaje > 100) {
                    avisar('Escribe un porcentaje entre 0 y 100', false);
                    return;
                }

                abrirModalNipCaja({
                    titulo: 'Autorizar descuento',
                    subtitulo: `Descuento del ${porcentaje}% — ingresa tu NIP`,
                    icono: 'fa-tag',
                    colorIcono: 'amber',
                    onConfirm: async (nip) => {
                        const resNip = await fetch('/mesero/capitan/verify', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                            body: JSON.stringify({ nip })
                        });
                        const dataNip = await resNip.json().catch(() => null);
                        if (!resNip.ok || !dataNip?.success) {
                            throw new Error(dataNip?.message || 'NIP incorrecto.');
                        }

                        const res = await fetch(@json(route('admin.caja.cuenta.descuento')), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                            body: JSON.stringify({ mesa_id: {{ $mesa->id }}, porcentaje: porcentaje }),
                        });
                        const data = await res.json();
                        if (!res.ok || !data.success) throw new Error(data.message || 'No se pudo aplicar el descuento.');
                        window.location.reload();
                    }
                });
            });
        }
    });
</script>
@endpush