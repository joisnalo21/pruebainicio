<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Paciente;
use App\Models\Formulario008;

class MedicoController extends Controller
{
    // =========================
    // DASHBOARD
    // =========================
    public function index()
    {
        $totalPacientes = Paciente::count();
        $totalFormularios = Formulario008::count();

        return view('medico.dashboard', compact('totalPacientes', 'totalFormularios'));
    }

    // =========================
    // FORMULARIOS 008 - LISTADO
    // =========================
    public function listarFormularios(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $estado = $request->query('estado'); // completo | incompleto | null
        $desde = $request->query('desde');   // YYYY-MM-DD
        $hasta = $request->query('hasta');   // YYYY-MM-DD

        $query = Formulario008::query()
            ->with(['paciente', 'creador'])
            ->latest();

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('paciente', function ($p) use ($q) {
                    $p->where('cedula', 'like', "%{$q}%")
                      ->orWhere('primer_nombre', 'like', "%{$q}%")
                      ->orWhere('segundo_nombre', 'like', "%{$q}%")
                      ->orWhere('apellido_paterno', 'like', "%{$q}%")
                      ->orWhere('apellido_materno', 'like', "%{$q}%")
                      ->orWhereRaw(
                          "CONCAT_WS(' ', primer_nombre, segundo_nombre, apellido_paterno, apellido_materno) LIKE ?",
                          ["%{$q}%"]
                      );
                })
                ->orWhere('id', $q)
                ->orWhereRaw("CONCAT('008-', LPAD(id, 6, '0')) LIKE ?", ["%{$q}%"]);
            });
        }

        if ($desde) {
            $query->whereDate('created_at', '>=', $desde);
        }

        if ($hasta) {
            $query->whereDate('created_at', '<=', $hasta);
        }

        // PRO: completo/incompleto depende de estado
        if ($estado === 'completo') {
            $query->where('estado', 'completo');
        } elseif ($estado === 'incompleto') {
            $query->where('estado', 'borrador');
        }

        $formularios = $query->paginate(12)->withQueryString();

        // KPIs
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();

        $stats = [
            'hoy' => Formulario008::whereDate('created_at', $hoy)->count(),
            'mes' => Formulario008::whereDate('created_at', '>=', $inicioMes)->count(),
            'pendientes' => Formulario008::where('estado', 'borrador')->count(),
            'trauma' => 0, // aún no calculamos por tipo
        ];

        return view('medico.formularios.index', compact('formularios', 'stats', 'q', 'estado', 'desde', 'hasta'));
    }

    // =========================
    // NUEVO 008 - SELECCIONAR PACIENTE
    // =========================
    public function seleccionarPaciente(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $pacientes = Paciente::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('cedula', 'like', "%{$q}%")
                    ->orWhere('primer_nombre', 'like', "%{$q}%")
                    ->orWhere('segundo_nombre', 'like', "%{$q}%")
                    ->orWhere('apellido_paterno', 'like', "%{$q}%")
                    ->orWhere('apellido_materno', 'like', "%{$q}%");
            })
            ->orderBy('apellido_paterno')
            ->orderBy('primer_nombre')
            ->paginate(10)
            ->withQueryString();

        return view('medico.formularios.seleccionar_paciente', compact('pacientes', 'q'));
    }

    // =========================
    // NUEVO 008 - CREAR BORRADOR
    // =========================
    public function iniciarFormulario008(Request $request)
    {
        $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
        ]);

        $formulario = Formulario008::create([
            'paciente_id' => (int) $request->input('paciente_id'),
            'created_by' => Auth::id(),
            'estado' => 'borrador',
            'paso_actual' => 1,
        ]);

        return redirect()->route('medico.formularios.paso', [
            'formulario' => $formulario->id,
            'paso' => 1,
        ])->with('success', 'Formulario 008 creado en borrador.');
    }

    // =========================
    // WIZARD - VER PASO (GET)
    // =========================
    public function wizardPaso(Request $request, $formulario, $paso)
    {
        $paso = (int) $paso;

        $steps = config('form008.wizard', []);

        if ($paso < 1 || empty($steps) || !isset($steps[$paso])) {
            abort(404);
        }

        $form = Formulario008::with(['paciente', 'creador'])->findOrFail($formulario);

        // Seguridad: solo el creador (por ahora)
        if ((int) $form->created_by !== (int) Auth::id()) {
            abort(403);
        }

        // No permitir saltar pasos hacia adelante
        if ($paso > (int) $form->paso_actual) {
            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => $form->paso_actual,
            ])->with('success', 'Continúa desde el paso actual.');
        }

        // Prefill paso 1: encabezado fijo del hospital
        if ($paso === 1) {
            $h = config('form008.hospital', []);

            $dirty = false;
            foreach ([
                'institucion_sistema' => $h['institucion_sistema'] ?? null,
                'unidad_operativa' => $h['unidad_operativa'] ?? null,
                'cod_uo' => $h['cod_uo'] ?? null,
                'cod_provincia' => $h['cod_provincia'] ?? null,
                'cod_canton' => $h['cod_canton'] ?? null,
                'cod_parroquia' => $h['cod_parroquia'] ?? null,
            ] as $k => $v) {
                if (blank($form->{$k}) && !blank($v)) {
                    $form->{$k} = $v;
                    $dirty = true;
                }
            }
            if ($dirty) $form->save();
        }

        return view('medico.formularios.wizard', [
            'formulario' => $form,
            'paso' => $paso,
            'steps' => $steps,
        ]);
    }

    // =========================
    // WIZARD - GUARDAR PASO (POST)
    // =========================
    public function guardarPaso(Request $request, $formulario, $paso)
    {
        $paso = (int) $paso;

        $steps = config('form008.wizard', []);
        if ($paso < 1 || empty($steps) || !isset($steps[$paso])) {
            abort(404);
        }

        $form = Formulario008::with('paciente')->findOrFail($formulario);

        if ((int) $form->created_by !== (int) Auth::id()) {
            abort(403);
        }

        // -------------------------
        // PASO 1: ADMISION
        // -------------------------
        if ($paso === 1) {
            $data = $request->validate([
                'fecha_admision' => ['required', 'date'],
                'referido_de' => ['nullable', 'string', 'max:255'],

                'avisar_nombre' => ['nullable', 'string', 'max:255'],
                'avisar_parentesco' => ['nullable', 'string', 'max:100'],
                'avisar_direccion' => ['nullable', 'string', 'max:255'],
                'avisar_telefono' => ['nullable', 'string', 'max:50'],

                'forma_llegada' => ['required', 'in:ambulatorio,ambulancia,otro'],
                'fuente_informacion' => ['nullable', 'string', 'max:255'],
                'entrega_institucion_persona' => ['nullable', 'string', 'max:255'],
                'entrega_telefono' => ['nullable', 'string', 'max:50'],
            ]);

            $form->fill($data);
            $form->estado = 'borrador';

            if ($form->paso_actual < 2) {
                $form->paso_actual = 2;
            }

            $form->save();

            $accion = $request->input('accion', 'next');

            if ($accion === 'save') {
                return back()->with('success', 'Paso 1 guardado (borrador).');
            }

            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => 2,
            ])->with('success', 'Paso 1 guardado. Continúa al paso 2.');
        }

        // -------------------------
        // PASO 2: MOTIVO / EVENTO
        // -------------------------
        if ($paso === 2) {

            $allowedTipos = [
                'accidente_transito','caida','quemadura','mordedura','ahogamiento','cuerpo_extrano','aplastamiento','otro_accidente',
                'violencia_arma_fuego','violencia_arma_punzante','violencia_rina','violencia_familiar','abuso_fisico','abuso_psicologico','abuso_sexual','otra_violencia',
                'intoxicacion_alcoholica','intoxicacion_alimentaria','intoxicacion_drogas','inhalacion_gases','otra_intoxicacion',
                'envenenamiento','picadura','anafilaxia',
            ];

            $data = $request->validate([
                'hora_inicio_atencion' => ['required'],
                'motivo_causa' => ['required', 'in:trauma,clinica,obstetrica,quirurgica,otro'],
                'notificacion_policia' => ['nullable', 'boolean'],
                'otro_motivo_detalle' => ['nullable', 'string', 'max:255'],
                'grupo_sanguineo' => ['nullable', 'string', 'max:5'],

                'evento_fecha_hora' => ['nullable', 'date'],
                'evento_lugar' => ['nullable', 'string', 'max:255'],
                'evento_direccion' => ['nullable', 'string', 'max:255'],
                'evento_tipos' => ['nullable', 'array'],
                'evento_tipos.*' => ['in:' . implode(',', $allowedTipos)],

                'no_aplica_custodia_policial' => ['nullable', 'boolean'],
                'evento_observaciones' => ['nullable', 'string'],

                'aliento_etilico' => ['nullable', 'boolean'],
                'valor_alcochek' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            ], [
                'hora_inicio_atencion.required' => 'La hora de inicio de atención es obligatoria.',
                'motivo_causa.required' => 'Debe seleccionar la causa del motivo.',
            ]);

            if (($data['motivo_causa'] ?? null) === 'otro' && blank($data['otro_motivo_detalle'] ?? null)) {
                return back()
                    ->withErrors(['otro_motivo_detalle' => 'Debe detallar el “otro motivo”.'])
                    ->withInput();
            }

            $data['notificacion_policia'] = (bool) $request->boolean('notificacion_policia');
            $data['no_aplica_custodia_policial'] = (bool) $request->boolean('no_aplica_custodia_policial');
            $data['aliento_etilico'] = (bool) $request->boolean('aliento_etilico');

            $form->fill($data);
            $form->estado = 'borrador';

            if ($form->paso_actual < 3) {
                $form->paso_actual = 3;
            }

            $form->save();

            $accion = $request->input('accion', 'next');

            if ($accion === 'save') {
                return back()->with('success', 'Paso 2 guardado (borrador).');
            }

            return redirect()->route('medico.formularios.paso', [
                'formulario' => $form->id,
                'paso' => 3,
            ])->with('success', 'Paso 2 guardado. Continúa al paso 3.');
        }

        // -------------------------
        // OTROS PASOS (aún no)
        // -------------------------
        return back()->with('warning', 'Este paso aún está en construcción.');
    }

    // =========================
    // REPORTES
    // =========================
    public function reportes()
    {
        $formularios = Formulario008::with('paciente')->get();
        return view('medico.reportes.index', compact('formularios'));
    }
}
