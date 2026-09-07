<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#f3f4f6">
<title>Pre-cuenta — Mesa {{ $mesa->numero }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

    html, body {
        height: 100%;
    }

    body {
        font-family: 'Courier New', Courier, monospace;
        color: #111;
        font-size: 16px;
        background: #e5e7eb;
        /* soporte para el "notch"/barras del sistema en móviles */
        padding: env(safe-area-inset-top) env(safe-area-inset-right) env(safe-area-inset-bottom) env(safe-area-inset-left);
    }

    /* ===== Contenedor de pantalla: centra el ticket como una tarjeta ===== */
    .pagina {
        min-height: 100%;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: clamp(12px, 4vw, 32px) clamp(10px, 3vw, 20px) calc(env(safe-area-inset-bottom) + 24px);
    }

    .recibo-envoltura {
        width: 100%;
        max-width: 460px;
    }

    .recibo {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 10px 30px -8px rgba(0,0,0,0.18), 0 2px 8px -2px rgba(0,0,0,0.06);
        padding: clamp(22px, 6vw, 32px) clamp(18px, 5.5vw, 28px) clamp(28px, 7vw, 36px);
        width: 100%;
    }

    .centro { text-align: center; }
    .negrita { font-weight: bold; }
    .titulo { font-size: clamp(20px, 5.5vw, 24px); font-weight: bold; letter-spacing: 1.5px; }
    .subtitulo { font-size: clamp(13px, 3.4vw, 14px); color: #555; margin-top: 4px; line-height: 1.4; }

    hr {
        border: none;
        border-top: 1px dashed #bbb;
        margin: 18px 0;
    }

    table { width: 100%; border-collapse: collapse; font-size: clamp(15px, 4vw, 17px); }
    td { padding: 7px 0; vertical-align: top; word-break: break-word; }
    .col-cant { width: 42px; white-space: nowrap; }
    .col-monto { width: 90px; text-align: right; white-space: nowrap; }
    .item-nombre { font-weight: bold; }
    .item-detalle { font-size: clamp(12px, 3.2vw, 13px); color: #555; padding-left: 2px; padding-top: 2px; }

    .footer {
        margin-top: 26px;
        font-size: clamp(12px, 3.2vw, 13px);
        text-align: center;
        color: #555;
        line-height: 1.5;
    }

    .btn-imprimir {
        display: block;
        width: 100%;
        margin-top: 26px;
        padding: 17px;
        background: #111;
        color: #fff;
        border: none;
        border-radius: 12px;
        font-family: Arial, sans-serif;
        font-size: 17px;
        font-weight: bold;
        letter-spacing: 0.2px;
        cursor: pointer;
        min-height: 52px; /* área táctil cómoda */
        -webkit-user-select: none;
        user-select: none;
        transition: transform 0.15s ease, opacity 0.15s ease;
    }
    .btn-imprimir:active {
        transform: scale(0.97);
        opacity: 0.9;
    }

    /* ===== Ajuste fino para pantallas muy angostas (≤340px) ===== */
    @media (max-width: 340px) {
        .col-monto { width: 70px; }
        .recibo { padding: 18px 14px 24px; }
    }

    /* ===== Impresión: vuelve al formato de ticket térmico clásico ===== */
    @media print {
        body { background: #fff; padding: 0; font-size: 15px; }
        .pagina { padding: 0; display: block; }
        .recibo-envoltura { max-width: none; }
        .recibo {
            box-shadow: none;
            border-radius: 0;
            width: 340px;
            margin: 0 auto;
            padding: 18px 14px 36px;
        }
        .btn-imprimir { display: none; }
    }
</style>
</head>
<body>

    <div class="pagina">
        <div class="recibo-envoltura">
            <div class="recibo">

                <div class="centro">
                    <div class="titulo">El Brasero</div>
                    <div class="subtitulo">Pre-cuenta / cuenta informativa</div>
                    <div class="subtitulo">No es un comprobante fiscal</div>
                </div>

                <hr>

                <table>
                    <tr>
                        <td>Mesa:</td>
                        <td class="col-monto negrita">{{ $mesa->numero }}</td>
                    </tr>
                    <tr>
                        <td>Mesero:</td>
                        <td class="col-monto">{{ $orden->mesero->nombre ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Fecha:</td>
                        <td class="col-monto">{{ $fecha->format('d/m/Y') }}</td>
                    </tr>
                </table>

                <hr>

                @if($detalles->isEmpty())
                    <p class="centro">Esta mesa aún no tiene productos enviados a cocina.</p>
                @else
                    <table>
                        @foreach($detalles as $detalle)
                            <tr>
                                <td class="col-cant">{{ $detalle->cantidad }}x</td>
                                <td class="item-nombre">
                                    {{ $detalle->producto->nombre ?? 'Producto eliminado' }}
                                    @if($detalle->gramaje)
                                        @php
                                            $gramajeLimpio = rtrim(rtrim(number_format((float) $detalle->gramaje, 2, '.', ''), '0'), '.');
                                        @endphp
                                        <div class="item-detalle">{{ $gramajeLimpio }}g</div>
                                    @endif
                                    @if($detalle->tiempo)
                                        <div class="item-detalle">TIEMPO - {{ strtoupper(str_replace('-', ' ', $detalle->tiempo)) }}</div>
                                    @endif
                                    @if($detalle->notas)
                                        <div class="item-detalle">{{ strtoupper($detalle->notas) }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                @endif

                <div class="footer">
                    Esta pre-cuenta es solo informativa.<br>
                    Solicita tu ticket de pago en caja.
                </div>

                <button class="btn-imprimir" onclick="window.print()">Imprimir / Guardar como PDF</button>

            </div>
        </div>
    </div>

    <script>
        // Se dispara automático para que el mesero solo tenga que elegir
        // impresora o "Guardar como PDF" en el diálogo del navegador.
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 300);
        });
    </script>

</body>
</html>