@extends('layouts.admin')

@section('title', 'Delivery | El Brasero')
@section('header-title', 'Configuración de Delivery')
@section('header-subtitle', 'Comisión + IVA que cobra cada plataforma')

@section('content')
<div class="px-3 sm:px-6 lg:px-8 py-5 sm:py-8 w-full max-w-4xl mx-auto space-y-5 sm:space-y-8 relative z-10" style="background-color: var(--bg-color);">

    <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-xl bg-gradient-to-br from-[#b74309] to-[#8f3207] shadow-[0_4px_14px_rgba(183,67,9,0.35)] shrink-0">
            <i class="fas fa-motorcycle text-white text-sm"></i>
        </div>
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight" style="color: var(--text-color);">Plataformas de Delivery</h1>
            <p class="text-xs sm:text-sm font-medium" style="color: var(--text-muted);">
                Estos porcentajes se negocian directamente con cada plataforma. Ajústalos aquí cuando cambie tu contrato.
            </p>
        </div>
    </div>

    <div class="rounded-2xl sm:rounded-[2rem] p-3.5 sm:p-6 shadow-xl space-y-3" style="background-color: var(--card-color); border: 1px solid var(--border-color);">
        @foreach($plataformas as $plataforma)
            <div class="plataforma-card p-3.5 sm:p-4 rounded-xl border transition-colors"
                 style="background-color: var(--input-bg); border-color: var(--border-color);"
                 data-id="{{ $plataforma->id }}">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex items-center gap-2.5 sm:w-40 shrink-0">
                        <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $plataforma->color }}"></span>
                        <span class="font-black text-sm" style="color: var(--text-color);">{{ $plataforma->nombre }}</span>
                    </div>

                    <div class="flex-1 grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-[10px] font-bold uppercase tracking-wide" style="color: var(--text-muted);">% Comisión</span>
                            <input type="text" inputmode="decimal" data-teclado="numerico"
                                   class="input-comision mt-0.5 w-full px-3 py-2 rounded-lg border text-sm font-bold outline-none transition-colors focus:border-[#b74309] focus:ring-1 focus:ring-[#b74309]"
                                   style="background-color: var(--card-color); border-color: var(--border-color); color: var(--text-color);"
                                   value="{{ number_format($plataforma->comision_porcentaje, 2, '.', '') }}">
                        </label>
                        <label class="block">
                            <span class="text-[10px] font-bold uppercase tracking-wide" style="color: var(--text-muted);">% IVA sobre comisión</span>
                            <input type="text" inputmode="decimal" data-teclado="numerico"
                                   class="input-iva mt-0.5 w-full px-3 py-2 rounded-lg border text-sm font-bold outline-none transition-colors focus:border-[#b74309] focus:ring-1 focus:ring-[#b74309]"
                                   style="background-color: var(--card-color); border-color: var(--border-color); color: var(--text-color);"
                                   value="{{ number_format($plataforma->iva_comision_porcentaje, 2, '.', '') }}">
                        </label>
                    </div>

                    <div class="flex items-center gap-2 sm:w-auto shrink-0">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="input-activo sr-only peer" {{ $plataforma->activo ? 'checked' : '' }}>
                            <div class="w-9 h-5 rounded-full peer peer-checked:bg-emerald-600 transition-colors" style="background-color: var(--border-color);"></div>
                            <div class="absolute left-0.5 top-0.5 bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-4 shadow-sm"></div>
                        </label>
                        <button type="button" class="btn-guardar px-4 py-2 rounded-lg bg-[#b74309] hover:bg-[#8f3207] active:scale-95 text-white text-xs font-black uppercase tracking-wide transition-all shadow-md shadow-[#b74309]/20 outline-none cursor-pointer">
                            Guardar
                        </button>
                    </div>
                </div>
                <p class="mensaje-guardado hidden text-[11px] font-bold text-emerald-500 mt-2"></p>
            </div>
        @endforeach

        @if($plataformas->isEmpty())
            <p class="text-center text-sm py-8" style="color: var(--text-muted);">No hay plataformas configuradas todavía.</p>
        @endif
    </div>

    <p class="text-[11px] text-center" style="color: var(--text-muted);">
        La comisión se calcula sobre el precio de venta (subtotal + IVA del producto) y se suma al total que paga el cliente en el pedido de delivery.
    </p>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    document.querySelectorAll('.plataforma-card').forEach(card => {
        card.querySelector('.btn-guardar').addEventListener('click', async () => {
            const id = card.dataset.id;
            const mensaje = card.querySelector('.mensaje-guardado');

            const leerPorcentaje = (selector) => {
                const el = card.querySelector(selector);
                const crudo = (el.value || '').trim().replace(',', '.');
                const num = parseFloat(crudo);
                return isNaN(num) ? null : num;
            };

            const comision = leerPorcentaje('.input-comision');
            const iva = leerPorcentaje('.input-iva');
            const activo = card.querySelector('.input-activo').checked;

            const mostrarError = (texto) => {
                mensaje.textContent = texto;
                mensaje.classList.remove('hidden', 'text-emerald-500');
                mensaje.classList.add('text-rose-500');
                setTimeout(() => mensaje.classList.add('hidden'), 3000);
            };

            if (comision === null || iva === null) {
                mostrarError('Escribe un número válido (ej. 25.5)');
                return;
            }
            if (comision < 0 || comision > 100 || iva < 0 || iva > 100) {
                mostrarError('Los porcentajes deben estar entre 0 y 100');
                return;
            }

            card.querySelector('.input-comision').value = comision.toFixed(2);
            card.querySelector('.input-iva').value = iva.toFixed(2);

            try {
                const res = await fetch(`/delivery/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        comision_porcentaje: comision,
                        iva_comision_porcentaje: iva,
                        activo: activo,
                    }),
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    mensaje.textContent = 'Guardado correctamente';
                    mensaje.classList.remove('hidden', 'text-rose-500');
                    mensaje.classList.add('text-emerald-500');
                } else {
                    mensaje.textContent = data.message || 'Error al guardar';
                    mensaje.classList.remove('hidden', 'text-emerald-500');
                    mensaje.classList.add('text-rose-500');
                }
            } catch (e) {
                mensaje.textContent = 'Error de conexión al guardar';
                mensaje.classList.remove('hidden', 'text-emerald-500');
                mensaje.classList.add('text-rose-500');
            }

            setTimeout(() => mensaje.classList.add('hidden'), 2500);
        });
    });
});
</script>
@endsection