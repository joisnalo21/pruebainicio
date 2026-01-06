<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class MedicoPacienteController extends Controller
{
    protected function rp(): string
    {
        $name = (string) Route::currentRouteName();
        if (Str::startsWith($name, 'enfermero.')) return 'enfermero.';
        return 'medico.';
    }

    protected function layout(): string
    {
        return $this->rp() === 'enfermero.' ? 'layouts.enfermeria' : 'layouts.medico';
    }

    /**
     * Listado de pacientes con búsqueda.
     */
    public function index(Request $request)
    {
        $busqueda = $request->input('buscar');

        $pacientes = Paciente::when($busqueda, function ($query, $busqueda) {
            $query->where('cedula', 'like', "%$busqueda%")
                ->orWhere('primer_nombre', 'like', "%$busqueda%")
                ->orWhere('apellido_paterno', 'like', "%$busqueda%");
        })->paginate(10);

        $rp = $this->rp();
        $layout = $this->layout();

        return view('medico.pacientes.index', compact('pacientes', 'busqueda', 'rp', 'layout'));
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create()
    {
        $path = public_path('provincias.json');
        $json = json_decode(file_get_contents($path), true);

        $provincias = [];
        foreach ($json as $codigo => $provinciaData) {
            if (isset($provinciaData['provincia'])) {
                $provincias[$codigo] = $provinciaData['provincia'];
            }
        }

        $rp = $this->rp();
        $layout = $this->layout();

        return view('medico.pacientes.create', compact('provincias', 'rp', 'layout'));
    }

    /**
     * Guardar nuevo paciente.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cedula' => 'required|digits:10|unique:pacientes,cedula',
            'primer_nombre' => 'required',
            'segundo_nombre' => 'required',
            'apellido_paterno' => 'required',
            'apellido_materno' => 'required',
            'fecha_nacimiento' => 'required|date',
            'edad' => 'nullable|numeric',
            'direccion' => 'required',
            'sexo' => 'required',
            'provincia' => 'required',
            'canton' => 'required',
            'parroquia' => 'required',
            'telefono' => 'required|digits:10',
            'ocupacion' => 'required',

            'zona' => 'required',
            'barrio' => 'required',
            'lugar_nacimiento' => 'required',
            'nacionalidad' => 'required',
            'grupo_cultural' => 'nullable',
            'estado_civil' => 'nullable',
            'instruccion' => 'nullable',
            'empresa' => 'nullable',
            'seguro_salud' => 'nullable',
        ], [
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.unique'   => 'Ya existe un paciente con esta cédula.',
            'cedula.digits'   => 'La cédula debe tener exactamente 10 dígitos.',
            'telefono.digits' => 'El teléfono debe tener 10 números.',
            'required'        => 'El campo :attribute es obligatorio.',
        ]);

        if (!$this->cedulaEcuatorianaValida($request->cedula)) {
            return back()->withErrors(['cedula' => 'La cédula ingresada no es válida.'])->withInput();
        }

        $validated['edad'] = Carbon::parse($request->fecha_nacimiento)->age;

        Paciente::create($validated);

        return redirect()->route($this->rp() . 'pacientes.index')
            ->with('success', 'Paciente registrado correctamente.');
    }

    /**
     * Editar paciente.
     */
    public function edit(Paciente $paciente)
    {
        $path = public_path('provincias.json');
        $json = json_decode(file_get_contents($path), true);

        $provincias = [];
        foreach ($json as $codigo => $provinciaData) {
            if (isset($provinciaData['provincia'])) {
                $provincias[$codigo] = $provinciaData['provincia'];
            }
        }

        $rp = $this->rp();
        $layout = $this->layout();

        return view('medico.pacientes.edit', compact('paciente', 'provincias', 'rp', 'layout'));
    }

    /**
     * Actualizar paciente existente.
     */
    public function update(Request $request, Paciente $paciente)
    {
        $data = $request->validate([
            'cedula' => 'required|digits:10|unique:pacientes,cedula,' . $paciente->id,
            'primer_nombre' => 'required',
            'segundo_nombre' => 'required',
            'apellido_paterno' => 'required',
            'apellido_materno' => 'required',
            'fecha_nacimiento' => 'required|date',
            'edad' => 'nullable|numeric',
            'direccion' => 'required',
            'sexo' => 'required',
            'provincia' => 'required',
            'canton' => 'required',
            'parroquia' => 'required',
            'telefono' => 'required|digits:10',
            'ocupacion' => 'required',

            'zona' => 'required',
            'barrio' => 'required',
            'lugar_nacimiento' => 'required',
            'nacionalidad' => 'required',
            'grupo_cultural' => 'nullable',
            'estado_civil' => 'nullable',
            'instruccion' => 'nullable',
            'empresa' => 'nullable',
            'seguro_salud' => 'nullable',
        ]);

        $paciente->update($data);

        return redirect()->route($this->rp() . 'pacientes.index')
            ->with('success', 'Paciente actualizado correctamente');
    }

    /**
     * Eliminar paciente.
     */
    public function destroy(Paciente $paciente)
    {
        $paciente->delete();

        return redirect()->route($this->rp() . 'pacientes.index')
            ->with('success', 'Paciente eliminado correctamente.');
    }

    public function validarCedula($cedula)
    {
        if (!preg_match('/^[0-9]{10}$/', $cedula)) {
            return response()->json(['valido' => false, 'mensaje' => 'Debe tener 10 dígitos numéricos.']);
        }

        if (!$this->cedulaEcuatorianaValida($cedula)) {
            return response()->json(['valido' => false, 'mensaje' => 'Cédula inválida.']);
        }

        if (Paciente::where('cedula', $cedula)->exists()) {
            return response()->json(['valido' => false, 'mensaje' => 'Esta cédula ya está registrada.']);
        }

        return response()->json(['valido' => true, 'mensaje' => 'Cédula válida.']);
    }

    private function cedulaEcuatorianaValida($cedula)
    {
        if (strlen($cedula) !== 10) return false;

        $provincia = intval(substr($cedula, 0, 2));
        if ($provincia < 1 || $provincia > 24) return false;

        $digitoVerificador = intval(substr($cedula, 9, 1));
        $suma = 0;

        for ($i = 0; $i < 9; $i++) {
            $num = intval($cedula[$i]);
            if ($i % 2 === 0) {
                $num *= 2;
                if ($num > 9) $num -= 9;
            }
            $suma += $num;
        }

        $resultado = 10 - ($suma % 10);
        if ($resultado == 10) $resultado = 0;

        return $resultado == $digitoVerificador;
    }
}
