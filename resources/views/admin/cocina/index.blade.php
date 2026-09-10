@extends('layouts.admin')

@section('content')
<div class="w-full min-h-screen p-4 sm:p-6 lg:p-8" style="background-color: var(--bg-color);">
    
    {{-- 1. Encabezado / Botones de Área --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-2 p-1.5 rounded-2xl border" style="background-color: var(--card-color); border-color: var(--border-color);">
            <a href="{{ route('admin.cocina.index', ['area' => 'cocina']) }}" 
               class="px-5 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider transition-all {{ $areaSeleccionada === 'Cocina' ? 'bg-[#b74309] text-white shadow-lg shadow-[#b74309]/25' : 'hover:opacity-100 opacity-70' }}"
               style="{{ $areaSeleccionada !== 'Cocina' ? 'color: var(--text-color);' : '' }}">
                <i class="fas fa-fire mr-2"></i>Cocina
            </a>
            <a href="{{ route('admin.cocina.index', ['area' => 'barra']) }}" 
   class="px-5 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider transition-all {{ $areaSeleccionada === 'Barra' ? 'bg-[#b74309] text-white shadow-lg shadow-[#b74309]/25' : 'hover:opacity-100 opacity-70' }}"
   style="{{ $areaSeleccionada !== 'Barra' ? 'color: var(--text-color);' : '' }}">
    <i class="fas fa-glass-martini-alt mr-2"></i>Barra
</a>
<a href="{{ route('admin.cocina.index', ['area' => 'parrilla']) }}" 
class="px-5 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider transition-all {{ $areaSeleccionada === 'Parrilla' ? 'bg-[#b74309] text-white shadow-lg shadow-[#b74309]/25' : 'hover:opacity-100 opacity-70' }}"
style="{{ $areaSeleccionada !== 'Parrilla' ? 'color: var(--text-color);' : '' }}">
<i class="fas fa-drumstick-bite mr-2"></i>Parrilla
 </a>
</div>

        <div class="flex items-center gap-2 text-xs font-bold text-emerald-500">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span> EN VIVO

            {{-- Acceso al historial de comandas del turno --}}
            <a href="{{ route('admin.cocina.historial', ['area' => $areaSeleccionada]) }}"
               class="ml-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border transition-colors font-bold text-[10px] uppercase tracking-wider hover:border-[#b74309] hover:text-[#b74309]"
               style="background-color: var(--card-color); border-color: var(--border-color); color: var(--text-muted);"
               title="Ver historial de comandas del turno">
                <i class="fas fa-clock-rotate-left text-[10px]"></i> Historial
            </a>
        </div>
    </div>

    {{-- 2. Tarjetas de Estadísticas --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-6">

        {{-- Órdenes Activas - Acento El Brasero (#b74309) --}}
        <div class="p-4 sm:p-5 rounded-2xl border shadow-[0_0_20px_-5px_rgba(183,67,9,0.35)]" style="background-color: var(--card-color); border-color: rgba(183,67,9,0.4);">
            <span class="text-[10px] font-black uppercase tracking-wider" style="color: var(--text-muted);">Órdenes Activas</span>
            <h4 id="stat-ordenes-activas" class="text-2xl sm:text-3xl font-black mt-1" style="color: var(--text-color);">{{ $ordenesActivasEnArea }}</h4>
        </div>

        {{-- En Proceso - Púrpura --}}
        <div class="p-4 sm:p-5 rounded-2xl border border-purple-500/40 shadow-[0_0_20px_-5px_rgba(168,85,247,0.35)]" style="background-color: var(--card-color);">
            <span class="text-[10px] font-black uppercase tracking-wider" style="color: var(--text-muted);">En Proceso</span>
            <h4 id="stat-enproceso" class="text-2xl sm:text-3xl font-black mt-1" style="color: var(--text-color);">{{ $enProceso }}</h4>
        </div>

        {{-- Listas (Turno) - Esmeralda --}}
        <div class="p-4 sm:p-5 rounded-2xl border border-emerald-500/40 shadow-[0_0_20px_-5px_rgba(16,185,129,0.35)]" style="background-color: var(--card-color);">
            <span class="text-[10px] font-black uppercase tracking-wider" style="color: var(--text-muted);">Listas (Turno)</span>
            <h4 id="stat-servidas" class="text-2xl sm:text-3xl font-black mt-1" style="color: var(--text-color);">{{ $servidas }}</h4>
        </div>

    </div>

    {{-- 3. CONTENEDOR DE COMANDAS --}}
    <div id="comandas-container" class="w-full">
        @include('admin.cocina.partials.comandas')
    </div>

</div>
@endsection

@push('scripts')
<script>
    const AREA_ACTUAL = @json(strtolower($areaSeleccionada));
    const URL_API_COMANDAS = @json(route('admin.cocina.api.comandas'));

    // ---------------------------------------------------------------
    // AUTO-REFRESCO: consulta el servidor cada 5s y reemplaza las
    // tarjetas + contadores, sin recargar la página completa.
    // ---------------------------------------------------------------
    async function actualizarComandas() {
        try {
            const res = await fetch(`${URL_API_COMANDAS}?area=${AREA_ACTUAL}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });

            if (!res.ok) {
                const texto = await res.text();
                console.error('apiComandas falló:', res.status, texto.slice(0, 500));
                return;
            }

            const data = await res.json();
            if (!data || !data.success) {
                console.error('apiComandas respondió sin success:', data);
                return;
            }

            const contenedor = document.getElementById('comandas-container');
            if (contenedor) contenedor.innerHTML = data.html;

            const setTexto = (id, valor) => {
                const el = document.getElementById(id);
                if (el) el.innerText = valor;
            };
            setTexto('stat-ordenes-activas', data.ordenesActivasEnArea);
            setTexto('stat-pendientes', data.pendientes);
            setTexto('stat-enproceso', data.enProceso);
            setTexto('stat-servidas', data.servidas);

            actualizarContadoresEspera();
        } catch (err) {
            console.error('Error actualizando comandas:', err);
        }
    }

    // ---------------------------------------------------------------
    // Carga lateral de Mesas Abiertas (KDS)
    // ---------------------------------------------------------------
    async function cargarKdsMesas() {
        try {
            const res = await fetch('{{ route("mesero.mesas.abiertas") }}', { 
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest', 
                    'Accept': 'application/json' 
                } 
            });
            
            const data = await res.json().catch(() => null);
            if (!res.ok || !data || !data.success) return;

            const list = document.getElementById('kdsMesasList');
            const badge = document.getElementById('kdsBadge');
            if (!list) return;

            list.innerHTML = '';
            if (badge) badge.innerText = data.conteo_abiertas || 0;

            const mesas = data.mesas_abiertas || [];
            
            if (mesas.length === 0) {
                list.innerHTML = `
                    <div class="text-[11px] font-medium p-4 text-center rounded-xl border" style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-muted);">
                        No hay mesas activas en este momento.
                    </div>`;
                return;
            }

            mesas.forEach(m => {
                const a = document.createElement('a');
                a.href = `{{ url('mesero/comanda') }}/${m.id}`;
                a.className = 'block p-4 rounded-xl border transition-all flex items-center justify-between group cursor-pointer hover:border-[#b74309]/50';
                a.style.backgroundColor = 'var(--card-color)';
                a.style.borderColor = 'var(--border-color)';
                
                a.innerHTML = `
                    <div class="flex-1 w-full min-w-0 pr-2">
                        <div class="flex items-center gap-2 mb-1">
                            <h5 class="text-sm font-black truncate group-hover:text-[#b74309] transition-colors" style="color: var(--text-color);">
                                Mesa ${m.numero}
                            </h5>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded border border-emerald-500/30 bg-emerald-500/10 text-emerald-500 text-[8px] font-black uppercase tracking-wider shadow-sm">
                                ACTIVA
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-[10px] font-bold" style="color: var(--text-muted);">
                            <span class="flex items-center gap-1.5"><i class="fas fa-users text-[#b74309]"></i> ${m.capacidad ?? '0'} pax</span>
                            <span class="text-emerald-500 tracking-wide">$ ${Number(m.total_consumo || 0).toFixed(2)}</span>
                        </div>
                    </div>
                    <div class="pl-2 flex items-center shrink-0">
                        <div class="w-8 h-8 rounded-full border flex items-center justify-center group-hover:bg-[#b74309] group-hover:border-[#b74309] group-hover:text-white transition-colors" style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-muted);">
                            <i class="fas fa-chevron-right text-[9px]"></i>
                        </div>
                    </div>
                `;
                list.appendChild(a);
            });

        } catch (err) {
            console.error('Error cargando mesas KDS:', err);
        }
    }

    // ---------------------------------------------------------------
    // Contador de tiempo de espera por ticket + Alertas Visuales
    // ---------------------------------------------------------------
    function formatearEspera(minutos) {
        if (minutos < 1) return 'Recién enviado';
        if (minutos < 60) return `Espera: ${minutos} min`;
        const horas = Math.floor(minutos / 60);
        const resto = minutos % 60;
        return `Espera: ${horas}h ${resto}min`;
    }

    function claseNivelEspera(minutos) {
        if (minutos >= 15) {
            return 'bg-rose-500/20 border-rose-500/50 text-rose-500 animate-pulse';
        }
        if (minutos >= 10) {
            return 'bg-amber-500/20 border-amber-500/50 text-amber-500';
        }
        return 'border text-[10px] font-black uppercase tracking-wide';
    }

    function actualizarContadoresEspera() {
        document.querySelectorAll('.tiempo-espera').forEach((el) => {
            const creado = el.dataset.creado;
            if (!creado) return;

            const minutos = Math.max(0, Math.floor((Date.now() - new Date(creado).getTime()) / 60000));
            const texto = el.querySelector('.tiempo-texto');
            if (texto) texto.textContent = formatearEspera(minutos);

            el.className = 'tiempo-espera shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded-lg border text-[10px] font-black uppercase tracking-wide whitespace-nowrap transition-colors ' + claseNivelEspera(minutos);
            if (minutos < 10) {
                el.style.backgroundColor = 'var(--input-bg)';
                el.style.borderColor = 'var(--border-color)';
                el.style.color = 'var(--text-muted)';
            } else {
                el.style.backgroundColor = '';
                el.style.borderColor = '';
                el.style.color = '';
            }
        });
    }

    // ---------------------------------------------------------------
    // Inicialización del DOM
    // ---------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', () => {
        actualizarContadoresEspera();
        cargarKdsMesas();

        setInterval(actualizarComandas, 5000);
        setInterval(cargarKdsMesas, 10000);
        setInterval(actualizarContadoresEspera, 10000);
        setInterval(verificarAlertas15min, 30000);

        iniciarSonidosYTachar();
    });

    // ---------------------------------------------------------------
    // SONIDOS (Web Audio API)
    // ---------------------------------------------------------------
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

    function sonarEntrada() {
        [660, 880].forEach((freq, i) => {
            const o = audioCtx.createOscillator();
            const g = audioCtx.createGain();
            o.connect(g); g.connect(audioCtx.destination);
            o.frequency.value = freq;
            o.type = 'sine';
            const t = audioCtx.currentTime + i * 0.18;
            g.gain.setValueAtTime(0, t);
            g.gain.linearRampToValueAtTime(0.4, t + 0.04);
            g.gain.exponentialRampToValueAtTime(0.001, t + 0.3);
            o.start(t); o.stop(t + 0.3);
        });
    }

    function sonarAlerta() {
        [440, 440, 440].forEach((freq, i) => {
            const o = audioCtx.createOscillator();
            const g = audioCtx.createGain();
            o.connect(g); g.connect(audioCtx.destination);
            o.frequency.value = freq;
            o.type = 'square';
            const t = audioCtx.currentTime + i * 0.22;
            g.gain.setValueAtTime(0, t);
            g.gain.linearRampToValueAtTime(0.25, t + 0.03);
            g.gain.exponentialRampToValueAtTime(0.001, t + 0.18);
            o.start(t); o.stop(t + 0.2);
        });
    }

    let lotesConocidos = new Set();
    let alertasDisparadas = new Set();
    let audioDesbloqueado = false;

    document.addEventListener('click', () => {
        if (!audioDesbloqueado && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        audioDesbloqueado = true;
    }, { once: true });

    // ---------------------------------------------------------------
    // VERIFICAR ALERTAS DE 15 MINUTOS
    // ---------------------------------------------------------------
    function verificarAlertas15min() {
        const tarjetas = document.querySelectorAll('.comanda-card[data-tiempo-inicio]');
        tarjetas.forEach(card => {
            const lote = card.dataset.lote;
            const inicio = parseInt(card.dataset.tiempoInicio || '0', 10);
            if (!inicio || alertasDisparadas.has(lote)) return;

            const minutos = (Date.now() - inicio) / 60000;
            if (minutos >= 15) {
                alertasDisparadas.add(lote);
                sonarAlerta();
                card.classList.add('ring-2', 'ring-rose-500', 'ring-offset-1', 'animate-pulse');
                setTimeout(() => card.classList.remove('animate-pulse'), 5000);
            }
        });
    }

    // ---------------------------------------------------------------
    // TACHAR PRODUCTOS
    // ---------------------------------------------------------------
    const URL_DETALLE_LISTO = @json(url('/cocina/detalle'));
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

    function iniciarSonidosYTachar() {
        document.querySelectorAll('[data-lote]').forEach(c => {
            lotesConocidos.add(c.dataset.lote);
        });
    }

    document.getElementById('comandas-container').addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-tachar');
        if (!btn) return;

        const li = btn.closest('.detalle-item');
        if (!li) return;

        const detalleId = li.dataset.detalleId;
        if (!detalleId) return;

        btn.disabled = true;

        try {
            const res = await fetch(`${URL_DETALLE_LISTO}/${detalleId}/listo`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            });
            const data = await res.json();

            if (res.ok && data.success) {
                const listo = data.nuevo_estado === 'listo_cocina' || data.todos_listos;
                const nombre = li.querySelector('.nombre-producto');

                if (listo) {
                    nombre?.classList.add('line-through', 'opacity-40');
                    nombre?.style.setProperty('color', 'var(--text-muted)');
                    btn.classList.add('bg-emerald-500', 'border-emerald-500', 'text-white', 'scale-95');
                    btn.style.borderColor = '';
                    btn.style.color = '';
                } else {
                    nombre?.classList.remove('line-through', 'opacity-40');
                    nombre?.style.removeProperty('color');
                    btn.classList.remove('bg-emerald-500', 'border-emerald-500', 'text-white', 'scale-95');
                    btn.style.borderColor = 'var(--border-color)';
                    btn.style.color = 'var(--text-muted)';
                }
            }
        } catch (err) {
            console.error('Error al tachar producto:', err);
        } finally {
            btn.disabled = false;
        }
    });

    // ---------------------------------------------------------------
    // SONAR al detectar comandas NUEVAS
    // ---------------------------------------------------------------
    const _actualizarOriginal = actualizarComandas;
    actualizarComandas = async function() {
        await _actualizarOriginal();

        const lotesActuales = new Set();
        document.querySelectorAll('[data-lote]').forEach(c => {
            const l = c.dataset.lote;
            lotesActuales.add(l);
            if (!lotesConocidos.has(l)) {
                sonarEntrada();
            }
        });
        lotesConocidos = lotesActuales;
        verificarAlertas15min();
    };
</script>
@endpush