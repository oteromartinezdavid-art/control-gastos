<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gasto;
use App\Models\Ingreso;
use App\Models\CategoriaGasto;
use App\Models\FuenteIngreso;
use App\Models\ReglaCategorizacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ImportacionController extends Controller
{
    public function index()
    {
        return view('importar.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'archivo_csv' => 'required|mimes:csv,txt',
        ]);

        $file = $request->file('archivo_csv');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Saltamos la cabecera
        fgetcsv($handle, 0, ";");

        $importadosCount = 0;
        $user_id = Auth::id();

        // 1. Cargamos las reglas de categorización
        $reglas = ReglaCategorizacion::where('user_id', $user_id)->get();

        // 2. Aseguramos categoría y fuente por defecto
        $categoriaDefault = CategoriaGasto::firstOrCreate(
            ['nombre' => 'Sin Clasificar', 'user_id' => $user_id],
            ['color' => '#94a3b8', 'presupuesto_mensual' => 0]
        );

        $fuenteDefault = FuenteIngreso::firstOrCreate(
            ['nombre' => 'Otros Ingresos', 'user_id' => $user_id]
        );

        while (($columna = fgetcsv($handle, 0, ";")) !== FALSE) {
            if (empty($columna[0]) || !isset($columna[3])) continue;

            // 1. Limpieza extrema de Importe
            $importeLimpio = str_replace(['.', ','], ['', '.'], $columna[3]);
            $importe = round((float) $importeLimpio, 2); // Redondeamos a 2 decimales para evitar variaciones
            
            // 2. Normalización de Fecha
            try {
                $fecha = Carbon::createFromFormat('d/m/Y', trim($columna[0]))->format('Y-m-d');
            } catch (\Exception $e) {
                continue; 
            }

            // 3. Normalización de Descripción (Sin espacios extra y en Mayúsculas para el hash)
            $descripcionOriginal = trim($columna[2]);
            $descripcionHash = strtoupper($descripcionOriginal);

            // 4. GENERACIÓN DE HASH REFORZADA
            // Usamos los datos normalizados para que la huella sea SIEMPRE la misma
            $hashMovimiento = md5($fecha . $descripcionHash . $importe . $user_id);
            // Solo para probar una vez:
            //dd($hashMovimiento, $fecha, $descripcionHash, $importe);
            if ($importe < 0) {
                $descripcionUpper = strtoupper($descripcionOriginal);
                $categoriaIdFinal = $categoriaDefault->id;

                foreach ($reglas as $regla) {
                    if (str_contains($descripcionUpper, strtoupper($regla->palabra_clave))) {
                        $categoriaIdFinal = $regla->categoria_id;
                        break;
                    }
                }

                // GASTO: firstOrCreate garantiza que si el hash existe, NO SE TOCA
                Gasto::firstOrCreate(
                    ['hash' => $hashMovimiento],
                    [
                        'user_id' => $user_id,
                        'descripcion' => $descripcionOriginal,
                        'monto' => abs($importe),
                        'fecha' => $fecha,
                        'categoria_id' => $categoriaIdFinal,
                    ]
                );
            } else {
                // INGRESO
                Ingreso::firstOrCreate(
                    ['hash' => $hashMovimiento],
                    [
                        'user_id' => $user_id,
                        'descripcion' => $descripcionOriginal,
                        'monto' => $importe,
                        'fecha' => $fecha,
                        'fuente_ingreso_id' => $fuenteDefault->id,
                    ]
                );
            }
            $importadosCount++;
        }

        fclose($handle);

        return redirect()->route('dashboard', ['mes' => now()->month, 'anio' => now()->year])
                         ->with('success', "Se han procesado $importadosCount movimientos. El sistema ha omitido o actualizado duplicados automáticamente.");
    }
}