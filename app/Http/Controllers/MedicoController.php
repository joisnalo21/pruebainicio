<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paciente;
use App\Models\Formulario008;

class MedicoController extends Controller
{
    public function index()
    {
        $totalPacientes = Paciente::count();
        $totalFormularios = Formulario008::count();
        return view('medico.dashboard', compact('totalPacientes', 'totalFormularios'));
    }

    // PACIENTES
    public function listarPacientes()
    {
        $pacientes = Paciente::latest()->paginate(10);
        return view('medico.pacientes.index', compact('pacientes'));
    }

    public function crearPaciente()
    {
        return view('medico.pacientes.crear');
    }

    public function guardarPaciente(Request $request)
    {
        $request->validate([
            'cedula' => 'required|unique:pacientes',
            'nombre' => 'required|string',
            'edad' => 'required|numeric',
            'direccion' => 'nullable|string',
        ]);

        Paciente::create($request->all());
        return redirect()->route('medico.pacientes')->with('success', 'Paciente registrado correctamente.');
    }

    // FORMULARIO 008
    public function listarFormularios()
    {
        $formularios = Formulario008::with('paciente')->latest()->paginate(10);
        return view('medico.formularios.index', compact('formularios'));
    }

    public function crearFormulario(Paciente $paciente)
    {
        return view('medico.formularios.crear', compact('paciente'));
    }

    public function guardarFormulario(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'motivo' => 'required|string',
            'diagnostico' => 'nullable|string',
        ]);

        Formulario008::create($request->all());
        return redirect()->route('medico.formularios')->with('success', 'Formulario 008 registrado correctamente.');
    }

    public function editarFormulario($id)
    {
        $formulario = Formulario008::findOrFail($id);
        return view('medico.formularios.editar', compact('formulario'));
    }

    public function actualizarFormulario(Request $request, $id)
    {
        $formulario = Formulario008::findOrFail($id);
        $formulario->update($request->all());
        return redirect()->route('medico.formularios')->with('success', 'Formulario actualizado correctamente.');
    }

    // REPORTES
    public function reportes()
    {
        $formularios = Formulario008::with('paciente')->get();
        return view('medico.reportes.index', compact('formularios'));
    }
}
