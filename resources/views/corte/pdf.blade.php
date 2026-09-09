<!-- resources/views/corte/pdf.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Corte de Ventas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; }
        h3 { color: #444; border-bottom: 2px solid #ccc; padding-bottom: 5px; margin-top: 20px; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Corte General de Ventas</h2>
        <p>Reporte del: <strong>{{ $fechaInicio }}</strong> al <strong>{{ $fechaFin }}</strong></p>
    </div>

    {{-- Resumen por área --}}
    @if(isset($resumenPorArea) && $resumenPorArea->isNotEmpty())
    <div style="margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
        <div style="background: #f4f4f4; padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #ddd;">
            Venta (No incluye impuestos) — Por tipo de producto
        </div>
        <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
            <tbody>
                @foreach($resumenPorArea as $area)
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 7px 12px; font-weight: 700; text-transform: uppercase;">{{ $area->area }}</td>
                    <td style="padding: 7px 12px; text-align: right; font-weight: 900;">${{ number_format($area->total_monto, 2) }}</td>
                    <td style="padding: 7px 12px; text-align: right; color: #555;">({{ $area->porcentaje }}%)</td>
                    <td style="padding: 7px 12px; text-align: right; color: #555;">{{ number_format($area->total_piezas) }} pzs.</td>
                </tr>
                @endforeach
                <tr style="background: #f4f4f4; font-weight: 900; font-size: 13px;">
                    <td style="padding: 8px 12px;">TOTAL GENERAL</td>
                    <td style="padding: 8px 12px; text-align: right;">${{ number_format($totalVentas, 2) }}</td>
                    <td style="padding: 8px 12px; text-align: right;">(100%)</td>
                    <td style="padding: 8px 12px; text-align: right;">{{ number_format($totalPiezas) }} pzs.</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    @foreach($ventasPorArea as $area => $productos)
        <h3>Área: {{ $area }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th style="width: 150px;" class="text-center">Total Vendidos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $item)
                    <tr>
                        <td>{{ $item->producto }}</td>
                        <td class="text-center">{{ $item->total_vendido }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>