<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Formulario008;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();
        $inicioSemana = Carbon::now()->startOfWeek();     // si quieres semana lunes-domingo
        $inicioMes = Carbon::now()->startOfMonth();

        // Pacientes
        $pacientesTotal = Paciente::count();

        // Formularios (global)
        $fHoy = Formulario008::whereDate('created_at', $hoy)->count();

        $fSemana = Formulario008::whereDate('created_at', '>=', $inicioSemana)->count();

        $fMes = Formulario008::whereDate('created_at', '>=', $inicioMes)->count();

        // Estado (global)
        $completos = Formulario008::where('estado', 'completo')->count();
        $borrador  = Formulario008::where('estado', 'borrador')->count();
        $archivados = Formulario008::where('estado', 'archivado')->count();

        // Últimos formularios (incluye archivados si quieres; yo recomiendo mostrarlos también, pero si no, filtra)
        $ultimosFormularios = Formulario008::with(['paciente', 'creador'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $kpi = [
            'pacientes_total' => $pacientesTotal,
            'f_hoy' => $fHoy,
            'f_semana' => $fSemana,
            'f_mes' => $fMes,
            'completos' => $completos,
            'borrador' => $borrador,
            'archivados' => $archivados,
        ];

        return view('admin.dashboard', compact('kpi', 'ultimosFormularios'));
    }
}
