<?php

namespace App\Http\Controllers;

use App\Models\Formulario008;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EnfermeroController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();

        $base = Formulario008::query()->where('estado', '!=', 'archivado');

        $stats = [
            'pacientes_total'  => Paciente::count(),
            'pacientes_hoy'    => Paciente::whereDate('created_at', $hoy)->count(),

            'formularios_hoy'  => (clone $base)->whereDate('created_at', $hoy)->count(),
            'formularios_mes'  => (clone $base)->whereDate('created_at', '>=', $inicioMes)->count(),

            // En tu app “pendiente” realmente es borrador
            'pendientes'       => Formulario008::where('estado', 'borrador')->count(),
            'completos'        => Formulario008::where('estado', 'completo')->count(),
        ];

        $recentForms = (clone $base)
            ->with(['paciente', 'creador'])
            ->latest()
            ->take(8)
            ->get();
        //comentario de prueba 2
        $recentPatients = Paciente::latest()->take(6)->get();

        return view('enfermeria.dashboard', compact('stats', 'recentForms', 'recentPatients'));
    }
}
