<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Financiacion;
use App\Models\Gasto;
use App\Models\CategoriaGasto;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class FinanciacionController extends Controller
{
    public function index()
    {
        $user_id = auth()->id();

        // Sincronizar cuotas antes de mostrar (idempotente: solo procesa meses nuevos)
        $this->sincronizarCuotas($user_id);

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
            'fecha_inicio'       => 'required|date',
            'fecha_fin'          => 'nullable|date|after_or_equal:fecha_inicio',
            'categoria_gasto_id' => ['required', Rule::exists('categoria_gastos', 'id')->where('user_id', auth()->id())],
        ]);

        // Fecha fin estimada si no se indica: inicio + cuotas pendientes (meses)
        $fechaFin = $request->fecha_fin
            ?: Carbon::parse($request->fecha_inicio)->addMonths((int) $request->cuotas_pendientes)->toDateString();

        Financiacion::create([
            'user_id'            => auth()->id(),
            'nombre'             => $request->nombre,
            'entidad'            => $request->entidad,
            'cuota_mensual'      => $request->cuota_mensual,
            'cuotas_pendientes'  => $request->cuotas_pendientes,
            'dia_cobro'          => $request->dia_cobro,
            'fecha_inicio'       => $request->fecha_inicio,
            'fecha_fin'          => $fechaFin,
            'categoria_gasto_id' => $request->categoria_gasto_id,
        ]);

        return redirect()->back()->with('success', 'Financiación registrada correctamente.');
    }

    public function edit(Financiacion $financiacion)
    {
        abort_if($financiacion->user_id !== auth()->id(), 403);

        $categorias = CategoriaGasto::where('user_id', auth()->id())->get();

        return view('financiaciones.edit', compact('financiacion', 'categorias'));
    }

    public function update(Request $request, Financiacion $financiacion)
    {
        abort_if($financiacion->user_id !== auth()->id(), 403);

        $request->validate([
            'nombre'             => 'required|string|max:255',
            'entidad'            => 'nullable|string|max:255',
            'cuota_mensual'      => 'required|numeric|min:0',
            'cuotas_pendientes'  => 'required|integer|min:0',
            'dia_cobro'          => 'required|integer|between:1,31',
            'fecha_inicio'       => 'required|date',
            'fecha_fin'          => 'nullable|date|after_or_equal:fecha_inicio',
            'categoria_gasto_id' => ['required', Rule::exists('categoria_gastos', 'id')->where('user_id', auth()->id())],
        ]);

        $financiacion->update([
            'nombre'             => $request->nombre,
            'entidad'            => $request->entidad,
            'cuota_mensual'      => $request->cuota_mensual,
            'cuotas_pendientes'  => $request->cuotas_pendientes,
            'dia_cobro'          => $request->dia_cobro,
            'fecha_inicio'       => $request->fecha_inicio,
            'fecha_fin'          => $request->fecha_fin,
            'categoria_gasto_id' => $request->categoria_gasto_id,
        ]);

        // Reconstruir snapshot: los pagos en ventana hasta hoy se consideran ya
        // reflejados en las cuotas_pendientes que ha indicado el usuario, así que
        // se marcan como procesados sin volver a decrementar. Solo meses futuros
        // nuevos descontarán cuota en sincronizarCuotas().
        $query = Gasto::where('user_id', auth()->id())
            ->whereRaw('LOWER(descripcion) LIKE ?', ['%' . strtolower($financiacion->nombre) . '%'])
            ->whereDate('fecha', '>=', $financiacion->fecha_inicio)
            ->whereDate('fecha', '<=', now());

        if ($financiacion->fecha_fin) {
            $query->whereDate('fecha', '<=', $financiacion->fecha_fin);
        }

        $financiacion->meses_procesados = $query->get()
            ->map(fn($g) => Carbon::parse($g->fecha)->format('Y-m'))
            ->unique()->values()->toArray();
        $financiacion->save();

        return redirect()->route('financiaciones.index')->with('success', 'Financiación actualizada correctamente.');
    }

    public function destroy(Financiacion $financiacion)
    {
        abort_if($financiacion->user_id !== auth()->id(), 403);

        $financiacion->delete();

        return redirect()->back()->with('success', 'Financiación eliminada correctamente.');
    }

    /**
     * Detects paid months for each financing by matching expense descriptions
     * and decrements cuotas_pendientes for newly found months.
     * Idempotent: meses_procesados prevents double-counting the same month.
     * Extracted from index() so GET stays semantically read-only in intent,
     * and this logic can also be triggered after CSV imports if needed.
     */
    private function sincronizarCuotas(int $userId): void
    {
        $todas = Financiacion::where('user_id', $userId)->get();

        foreach ($todas as $fin) {
            if ($fin->cuotas_pendientes <= 0) continue;

            $query = Gasto::where('user_id', $userId)
                ->whereRaw('LOWER(descripcion) LIKE ?', ['%' . strtolower($fin->nombre) . '%']);

            // Solo contar pagos a partir de la fecha de inicio de la financiación
            if ($fin->fecha_inicio) {
                $query->whereDate('fecha', '>=', $fin->fecha_inicio);
            }
            if ($fin->fecha_fin) {
                $query->whereDate('fecha', '<=', $fin->fecha_fin);
            }

            $mesesConPago = $query->get()
                ->map(fn($g) => Carbon::parse($g->fecha)->format('Y-m'))
                ->unique()
                ->values()
                ->toArray();

            $yaProcesados = $fin->meses_procesados ?? [];
            $nuevos       = array_values(array_diff($mesesConPago, $yaProcesados));

            if (!empty($nuevos)) {
                $fin->cuotas_pendientes = max(0, $fin->cuotas_pendientes - count($nuevos));
                $fin->meses_procesados  = array_merge($yaProcesados, $nuevos);
                $fin->save();
            }
        }
    }
}
