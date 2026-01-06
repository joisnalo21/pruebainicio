<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;

class AdminPacienteController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar', ''));

        $pacientes = Paciente::query()
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where('cedula', 'like', "%{$buscar}%")
                  ->orWhere('primer_nombre', 'like', "%{$buscar}%")
                  ->orWhere('segundo_nombre', 'like', "%{$buscar}%")
                  ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                  ->orWhere('apellido_materno', 'like', "%{$buscar}%")
                  ->orWhereRaw(
                      "CONCAT_WS(' ', primer_nombre, segundo_nombre, apellido_paterno, apellido_materno) LIKE ?",
                      ["%{$buscar}%"]
                  );
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.pacientes.index', compact('pacientes', 'buscar'));
    }

    public function show(Paciente $paciente)
    {
        return view('admin.pacientes.show', compact('paciente'));
    }
}
