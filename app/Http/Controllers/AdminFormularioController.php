<?php

namespace App\Http\Controllers;

use App\Models\Formulario008;
use App\Services\Formulario008PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminFormularioController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $estado = $request->query('estado'); // completo | borrador | archivado | null
        $desde = $request->query('desde');   // YYYY-MM-DD
        $hasta = $request->query('hasta');   // YYYY-MM-DD

        $query = Formulario008::query()
            ->with(['paciente', 'creador'])
            ->latest();

        // Por defecto: ocultar archivados
        if ($estado === null || $estado === '') {
            $query->where('estado', '!=', 'archivado');
        }

        // Búsqueda: paciente, id o "008-000123"
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

        // Compatibilidad: antes se usaba "incompleto" para borrador.
        if ($estado === 'incompleto') {
            $estado = 'borrador';
        }

        if ($estado === 'completo') {
            $query->where('estado', 'completo');
        } elseif ($estado === 'borrador') {
            $query->where('estado', 'borrador');
        } elseif ($estado === 'archivado') {
            $query->where('estado', 'archivado');
        }

        $formularios = $query->paginate(12)->withQueryString();

        // KPIs (coherentes: por defecto excluyen archivados)
        $hoy = Carbon::today();
        $inicioSemana = Carbon::now()->startOfWeek();
        $inicioMes = Carbon::now()->startOfMonth();

        $baseKpi = Formulario008::query()->where('estado', '!=', 'archivado');

        $stats = [
            'hoy'        => (clone $baseKpi)->whereDate('created_at', $hoy)->count(),
            'semana'     => (clone $baseKpi)->whereDate('created_at', '>=', $inicioSemana)->count(),
            'mes'        => (clone $baseKpi)->whereDate('created_at', '>=', $inicioMes)->count(),
            'completos'  => Formulario008::where('estado', 'completo')->count(),
            'borrador'   => Formulario008::where('estado', 'borrador')->count(),
            'archivados' => Formulario008::where('estado', 'archivado')->count(),
        ];

        return view('admin.formularios.index', compact('formularios', 'stats', 'q', 'estado', 'desde', 'hasta'));
    }

    public function show(Formulario008 $formulario)
    {
        // Admin solo consulta: lo mando a un paso de solo lectura.
        $paso = $formulario->esCompleto() ? 13 : max(1, (int)($formulario->paso_actual ?? 1));

        return redirect()->route('admin.formularios.ver.paso', [
            'formulario' => $formulario->id,
            'paso' => $paso,
        ]);
    }

    public function verPaso(Formulario008 $formulario, int $paso)
    {

        $steps = config('form008.wizard', []);
        if ($paso < 1 || empty($steps) || !isset($steps[$paso])) {
            abort(404);
        }

        $form = $formulario->load(['paciente', 'creador']);

        return view('admin.formularios.wizard_readonly', [
            'formulario' => $form,
            'paso' => $paso,
            'steps' => $steps,
        ]);
    }

    public function pdf(Formulario008 $formulario, Request $request, Formulario008PdfService $pdfService)
    {
        abort_unless($formulario->esCompleto(), 403, 'Solo se puede generar PDF cuando el formulario está completo.');

        $grid = $request->boolean('grid');
        $bytes = $pdfService->render($formulario, $grid);

        $filename = $formulario->pdfFilename();


        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"; filename*=UTF-8''" . rawurlencode($filename),
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }

    public function archivar(Formulario008 $formulario)
    {
        if ($formulario->estado === 'archivado') {
            return back()->with('error', 'Este formulario ya está archivado.');
        }

        $formulario->estado = 'archivado';
        $formulario->archivado_en = now();
        $formulario->save();

        return back()->with('success', 'Formulario archivado.');
    }

    public function desarchivar(Formulario008 $formulario)
    {
        if ($formulario->estado !== 'archivado') {
            return back()->with('error', 'Este formulario no está archivado.');
        }

        $formulario->estado = 'borrador';
        $formulario->archivado_en = null;
        $formulario->save();

        return back()->with('success', 'Formulario desarchivado (vuelve a borrador).');
    }

    public function destroy(Formulario008 $formulario)
    {
        $formulario->delete();
        return redirect()->route('admin.formularios.index')->with('success', 'Formulario eliminado.');
    }
}
