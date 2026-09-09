<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CorteController extends Controller
{
    public function index(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', today()->toDateString());
        $fechaFin    = $request->input('fecha_fin',    today()->toDateString());
        $areaFiltro  = $request->input('area', 'todas'); // 'todas' | cualquier area_impresion

        $ventasPorArea = $this->obtenerDatosCorte($fechaInicio, $fechaFin);

        // Listado de áreas disponibles para los botones de filtro
        $areasDisponibles = $ventasPorArea->keys()->sort()->values();

        // Filtrar si se pidió un área específica
        if ($areaFiltro !== 'todas') {
            $ventasPorArea = $ventasPorArea->filter(fn($_, $k) => strtolower($k) === strtolower($areaFiltro));
        }

        $resumenPorArea = $this->obtenerResumenPorArea($fechaInicio, $fechaFin);
        $totalVentas    = $resumenPorArea->sum('total_monto');
        $totalPiezas    = $resumenPorArea->sum('total_piezas');

        return view('corte.index', compact('ventasPorArea', 'fechaInicio', 'fechaFin', 'areaFiltro', 'areasDisponibles', 'resumenPorArea', 'totalVentas', 'totalPiezas'));
    }

    public function descargarPdf(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', today()->toDateString());
        $fechaFin    = $request->input('fecha_fin',    today()->toDateString());
        $areaFiltro  = $request->input('area', 'todas');

        $ventasPorArea = $this->obtenerDatosCorte($fechaInicio, $fechaFin);

        if ($areaFiltro !== 'todas') {
            $ventasPorArea = $ventasPorArea->filter(fn($_, $k) => strtolower($k) === strtolower($areaFiltro));
        }

        $resumenPorArea = $this->obtenerResumenPorArea($fechaInicio, $fechaFin);
        $totalVentas    = $resumenPorArea->sum('total_monto');
        $totalPiezas    = $resumenPorArea->sum('total_piezas');

        $pdf = Pdf::loadView('corte.pdf', compact('ventasPorArea', 'fechaInicio', 'fechaFin', 'areaFiltro', 'resumenPorArea', 'totalVentas', 'totalPiezas'));

        $sufijo = $areaFiltro !== 'todas' ? "_{$areaFiltro}" : '';
        return $pdf->download("ventas{$sufijo}_{$fechaInicio}_al_{$fechaFin}.pdf");
    }

    private function obtenerDatosCorte($fechaInicio, $fechaFin)
    {
        $resultados = DB::table('detalles_orden')
            ->join('ordenes', 'detalles_orden.orden_id', '=', 'ordenes.id')
            ->join('productos', 'detalles_orden.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->whereDate('ordenes.created_at', '>=', $fechaInicio)
            ->whereDate('ordenes.created_at', '<=', $fechaFin)
            ->where('detalles_orden.estado', '!=', 'cancelado')
            ->whereIn('ordenes.estado', ['pagada', 'servida', 'en proceso', 'pendiente'])
            ->select(
                'productos.nombre as producto',
                'categorias.nombre as categoria',
                'categorias.area_impresion as area',
                DB::raw('SUM(detalles_orden.cantidad) as total_vendido'),
                DB::raw('SUM(detalles_orden.cantidad * detalles_orden.precio_unitario) as total_monto')
            )
            ->groupBy('productos.id', 'productos.nombre', 'categorias.id', 'categorias.nombre', 'categorias.area_impresion')
            ->get();

        return $resultados->groupBy('area');
    }

    public function obtenerResumenPorArea($fechaInicio, $fechaFin)
    {
        // Resumen por área con monto total, cantidad y porcentaje
        $datos = DB::table('detalles_orden')
            ->join('ordenes', 'detalles_orden.orden_id', '=', 'ordenes.id')
            ->join('productos', 'detalles_orden.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->whereDate('ordenes.created_at', '>=', $fechaInicio)
            ->whereDate('ordenes.created_at', '<=', $fechaFin)
            ->where('detalles_orden.estado', '!=', 'cancelado')
            ->whereIn('ordenes.estado', ['pagada', 'servida', 'en proceso', 'pendiente'])
            ->select(
                'categorias.area_impresion as area',
                DB::raw('SUM(detalles_orden.cantidad) as total_piezas'),
                DB::raw('SUM(detalles_orden.cantidad * detalles_orden.precio_unitario) as total_monto')
            )
            ->groupBy('categorias.area_impresion')
            ->get();

        $totalGeneral = $datos->sum('total_monto');

        return $datos->map(function ($row) use ($totalGeneral) {
            return (object) [
                'area'         => $row->area,
                'total_piezas' => (int) $row->total_piezas,
                'total_monto'  => round($row->total_monto, 2),
                'porcentaje'   => $totalGeneral > 0
                    ? round(($row->total_monto / $totalGeneral) * 100, 1)
                    : 0,
            ];
        })->sortByDesc('total_monto')->values();
    }
}