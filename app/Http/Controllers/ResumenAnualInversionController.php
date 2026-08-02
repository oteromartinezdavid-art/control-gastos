<?php

namespace App\Http\Controllers;

use App\Services\InversionService;
use Illuminate\Http\Request;

class ResumenAnualInversionController extends Controller
{
    public function __construct(private InversionService $inversionService) {}

    public function index(Request $request)
    {
        $anio   = (int) $request->get('anio', now()->year);
        $userId = auth()->id();
        $data   = $this->inversionService->getResumenAnual($userId, $anio);

        return view('inversiones.resumen-anual', $data);
    }
}
