<?php

namespace App\Http\Controllers;

use App\Models\Formulario008;
use App\Services\Formulario008PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EnfermeriaFormulario008Controller extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $estado = $request->query('estado'); // completo | incompleto | archivado | null
        $desde = $request->query('desde');   // YYYY-MM-DD
        $hasta = $request->query('hasta');   // YYYY-MM-DD

        $query = Formulario008::query()
            ->with(['paciente', 'creador'])
            ->latest();

        // Por defecto ocultar archivados
        if ($estado === null || $estado === '') {
            $query->where('estado', '!=', 'archivado');
        }

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

        if ($desde) $query->whereDate('created_at', '>=', $desde);
        if ($hasta) $query->whereDate('created_at', '<=', $hasta);

        if ($estado === 'completo') {
            $query->where('estado', 'completo');
        } elseif ($estado === 'incompleto') {
            $query->where('estado', 'borrador');
        } elseif ($estado === 'archivado') {
            $query->where('estado', 'archivado');
        }

        $formularios = $query->paginate(12)->withQueryString();

        // KPIs (opcional para header)
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $base = Formulario008::query()->where('estado', '!=', 'archivado');

        $stats = [
            'hoy'        => (clone $base)->whereDate('created_at', $hoy)->count(),
            'mes'        => (clone $base)->whereDate('created_at', '>=', $inicioMes)->count(),
            'pendientes' => Formulario008::where('estado', 'borrador')->count(),
            'completos'  => Formulario008::where('estado', 'completo')->count(),
        ];

        return view('enfermeria.formularios.index', compact('formularios', 'stats', 'q', 'estado', 'desde', 'hasta'));
    }

    public function resumen(Formulario008 $formulario)
    {
        $formulario->load(['paciente', 'creador']);
        return view('enfermeria.formularios.resumen', compact('formulario'));
    }

    public function pdf(Formulario008 $formulario, Request $request, Formulario008PdfService $pdfService)
    {
        abort_unless($formulario->esCompleto(), 403, 'Solo se puede ver PDF cuando el formulario está completo.');

        $grid = $request->boolean('grid');
        $bytes = $pdfService->render($formulario, $grid);

        $filename = 'Formulario008-' . str_pad((string)$formulario->id, 6, '0', STR_PAD_LEFT) . '.pdf';

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }
    public function verPaso(Request $request, \App\Models\Formulario008 $formulario, $paso)
    {
        $paso = (int) $paso;

        $steps = config('form008.wizard', []);
        if ($paso < 1 || empty($steps) || !isset($steps[$paso])) {
            abort(404);
        }

        $form = $formulario->load(['paciente', 'creador']);

        // ✅ Para enfermería: permitir ver aunque esté en borrador.
        // (Si quieres restringir a completos, descomenta este abort)
        // abort_unless($form->esCompleto(), 403, 'Solo lectura disponible cuando está completo.');

        return view('enfermeria.formularios.wizard_readonly', [
            'formulario' => $form,
            'paso' => $paso,
            'steps' => $steps,
        ]);
    }
}
