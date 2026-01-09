<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Form008;

class FormulariosController extends Controller
{
    public function index(Request $request)
    {
        // estadísticas
        $stats = [
            'hoy'        => Form008::whereDate('created_at', today())->count(),
            'mes'        => Form008::whereMonth('created_at', now()->month)->count(),
            'trauma'     => Form008::where('tipo_emergencia', 'trauma')->count(),
            'pendientes' => Form008::where('completo', 0)->count(),
        ];
        //comentario de prueba 3
        // búsqueda
        $query = Form008::with(['paciente', 'medico']);

        if ($request->buscar) {
            $query->whereHas('paciente', function ($q) use ($request) {
                $q->where('nombre', 'LIKE', "%{$request->buscar}%")
                    ->orWhere('cedula', 'LIKE', "%{$request->buscar}%");
            })->orWhere('numero', 'LIKE', "%{$request->buscar}%");
        }

        if ($request->filtro == 'completo') {
            $query->where('completo', 1);
        } elseif ($request->filtro == 'incompleto') {
            $query->where('completo', 0);
        }

        $formularios = $query->orderBy('created_at', 'DESC')->paginate(10);

        return view('form008.index', compact('formularios', 'stats'));
    }
}
