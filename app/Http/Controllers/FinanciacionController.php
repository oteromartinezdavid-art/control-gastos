<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Financiacion;
use App\Models\Gasto;
use App\Models\CategoriaGasto;
use Carbon\Carbon;

class FinanciacionController extends Controller
{
    public function index()
    {
        $user_id = auth()->id();

        // Auto-detección: para cada financiación busca meses con pago detectado
        // y decrementa cuotas_pendientes solo por los meses aún no procesados.
        // meses_procesados actúa como registro idempotente: nunca se resta dos veces
        // el mismo mes, aunque se importe el mismo CSV varias veces.
        $todas = Financiacion::where('user_id', $user_id)->get();

        foreach ($todas as $fin) {
            if ($fin->cuotas_pendientes <= 0) continue;

            $mesesConPago = Gasto::where('user_id', $user_id)
                ->whereRaw('LOWER(descripcion) LIKE ?', ['%' . strtolower($fin->nombre) . '%'])
                ->get()
                ->map(fn($g) => Carbon::parse($g->fecha)->format('Y-m'))
                ->unique()
                ->values()
                ->toArray();

            $yaProcesados  = $fin->meses_procesados ?? [];
            $nuevos        = array_values(array_diff($mesesConPago, $yaProcesados));

            if (!empty($nuevos)) {
                $fin->cuotas_pendientes = max(0, $fin->cuotas_pendientes - count($nuevos));
                $fin->meses_procesados  = array_merge($yaProcesados, $nuevos);
                $fin->save();
            }
        }

        // Construir listado con estado del mes actual
        $mesActual  = now()->month;
        $anioActual = now()->year;

        $financiaciones = Financiacion::where('user_id', $user_id)
            ->with('categoriaGasto')
            ->orderBy('dia_cobro')
            ->get()
            ->map(function ($fin) use ($mesActual, $anioActual) {

                $pagoEsteMes = Gasto::where('user_id', $fin->user_id)
                    ->whereRaw('LOWER(descripcion) LIKE ?', ['%' . strtolower($fin->nombre) . '%'])
                    ->whereMonth('fecha', $mesActual)
                    ->whereYear('fecha',  $anioActual)
                    ->first();

                $cuotasPagadas = count($fin->meses_procesados ?? []);
                $cuotasTotal   = $cuotasPagadas + $fin->cuotas_pendientes;
                $porcentaje    = $cuotasTotal > 0 ? round(($cuotasPagadas / $cuotasTotal) * 100) : 0;

                return (object) [
                    'id'                => $fin->id,
                    'nombre'            => $fin->nombre,
                    'entidad'           => $fin->entidad,
                    'cuota_mensual'     => $fin->cuota_mensual,
                    'cuotas_pendientes' => $fin->cuotas_pendientes,
                    'cuotas_pagadas'    => $cuotasPagadas,
                    'cuotas_total'      => $cuotasTotal,
                    'porcentaje'        => $porcentaje,
                    'monto_pendiente'   => $fin->cuotas_pendientes * $fin->cuota_mensual,
                    'dia_cobro'         => $fin->dia_cobro,
                    'categoria'         => $fin->categoriaGasto,
                    'pagado_este_mes'   => !is_null($pagoEsteMes),
                    'fecha_pago_real'   => $pagoEsteMes ? $pagoEsteMes->fecha : null,
                ];
            });

        $totalDeuda         = $financiaciones->sum('monto_pendiente');
        $totalCuotaMensual  = $financiaciones->sum('cuota_mensual');
        $pagadasEsteMes     = $financiaciones->filter(fn($f) =>  $f->pagado_este_mes)->count();
        $totalFinanciaciones = $financiaciones->count();

        $categorias = CategoriaGasto::where('user_id', $user_id)->get();

        return view('financiaciones.index', compact(
            'financiaciones',
            'totalDeuda',
            'totalCuotaMensual',
            'pagadasEsteMes',
            'totalFinanciaciones',
            'categorias'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'             => 'required|string|max:255',
            'entidad'            => 'nullable|string|max:255',
            'cuota_mensual'      => 'required|numeric|min:0',
            'cuotas_pendientes'  => 'required|integer|min:1',
            'dia_cobro'          => 'required|integer|between:1,31',
            'categoria_gasto_id' => 'required|exists:categoria_gastos,id',
        ]);

        Financiacion::create([
            'user_id'            => auth()->id(),
            'nombre'             => $request->nombre,
            'entidad'            => $request->entidad,
            'cuota_mensual'      => $request->cuota_mensual,
            'cuotas_pendientes'  => $request->cuotas_pendientes,
            'dia_cobro'          => $request->dia_cobro,
            'categoria_gasto_id' => $request->categoria_gasto_id,
        ]);

        return redirect()->back()->with('success', 'Financiación registrada correctamente.');
    }

    public function edit(Financiacion $financiacion)
    {
        abort_if($financiacion->user_id != auth()->id(), 403);

        $categorias = CategoriaGasto::where('user_id', auth()->id())->get();

        return view('financiaciones.edit', compact('financiacion', 'categorias'));
    }

    public function update(Request $request, Financiacion $financiacion)
    {
        abort_if($financiacion->user_id != auth()->id(), 403);

        $request->validate([
            'nombre'             => 'required|string|max:255',
            'entidad'            => 'nullable|string|max:255',
            'cuota_mensual'      => 'required|numeric|min:0',
            'cuotas_pendientes'  => 'required|integer|min:0',
            'dia_cobro'          => 'required|integer|between:1,31',
            'categoria_gasto_id' => 'required|exists:categoria_gastos,id',
        ]);

        $financiacion->update([
            'nombre'             => $request->nombre,
            'entidad'            => $request->entidad,
            'cuota_mensual'      => $request->cuota_mensual,
            'cuotas_pendientes'  => $request->cuotas_pendientes,
            'dia_cobro'          => $request->dia_cobro,
            'categoria_gasto_id' => $request->categoria_gasto_id,
        ]);

        return redirect()->route('financiaciones.index')->with('success', 'Financiación actualizada correctamente.');
    }

    public function destroy(Financiacion $financiacion)
    {
        abort_if($financiacion->user_id != auth()->id(), 403);

        $financiacion->delete();

        return redirect()->back()->with('success', 'Financiación eliminada correctamente.');
    }
}
