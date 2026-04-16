<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gasto;
use App\Models\Ingreso;
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
        // Abrimos el archivo en modo lectura
        $handle = fopen($file->getRealPath(), 'r');
        
        // Saltamos la primera línea (cabecera)
        fgetcsv($handle, 0, ";");

        $importadosCount = 0;

        while (($columna = fgetcsv($handle, 0, ";")) !== FALSE) {
            // Estructura Bankinter:
            // [0] Fecha contable, [1] Fecha valor, [2] Descripción, [3] Importe, [4] Saldo...
            
            if (empty($columna[0]) || !isset($columna[3])) continue;

            // 1. Limpieza de Importe: de "1.250,55" a "1250.55"
            $importeLimpio = str_replace(['.', ','], ['', '.'], $columna[3]);
            $importe = (float) $importeLimpio;
            
            // 2. Formatear Fecha (Bankinter usa d/m/Y)
            try {
                $fecha = Carbon::createFromFormat('d/m/Y', $columna[0])->format('Y-m-d');
            } catch (\Exception $e) {
                continue; // Saltamos si la fecha no tiene el formato esperado
            }

            $descripcion = $columna[2];

            if ($importe < 0) {
                // Es un Gasto (lo guardamos con valor positivo)
                Gasto::create([
                    'user_id' => Auth::id(),
                    'descripcion' => $descripcion,
                    'monto' => abs($importe),
                    'fecha' => $fecha,
                    'categoria_id' => 1, // Asume que tienes una categoría "Varios" o "Sin clasificar" con ID 1
                ]);
            } else {
                // Es un Ingreso
                Ingreso::create([
                    'user_id' => Auth::id(),
                    'descripcion' => $descripcion,
                    'monto' => $importe,
                    'fecha' => $fecha,
                    'fuente_ingreso_id' => 1, // Asume que tienes una fuente con ID 1
                ]);
            }
            $importadosCount++;
        }

        fclose($handle);

        return redirect()->route('dashboard', ['mes' => now()->month, 'anio' => now()->year])
                         ->with('success', "Se han procesado $importadosCount movimientos correctamente.");
    }
}