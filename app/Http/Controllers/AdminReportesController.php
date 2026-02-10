<?php

namespace App\Http\Controllers;

use App\Models\Formulario008;
use App\Models\User;
use App\Services\ReportesPdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminReportesController extends Controller
{
    public function index(Request $request)
    {
        [$filters, $report, $ui] = $this->buildReportFromRequest($request);

        return view('admin.reportes.index', [
            'filters' => $filters,
            'report'  => $report,
            'ui'      => $ui, // options para selects
        ]);
    }

    public function pdf(Request $request, ReportesPdfService $pdf)
    {
        [$filters, $report, $ui] = $this->buildReportFromRequest($request);

        $bytes = $pdf->render($report, $filters);
        $tipoKey = $filters['tipo'] ?? 'general';

        $tipoSlug = match ($tipoKey) {
            'prod' => 'produccion',
            'prod_prof' => 'productividad_profesional',
            'demo' => 'demografia',
            'dx_ingreso' => 'diagnosticos_ingreso',
            'dx_alta' => 'diagnosticos_alta',
            'tiempos' => 'tiempos',
            default => 'reporte',
        };

        $desde = ($filters['desde'] ?? 'NA');
        $hasta = ($filters['hasta'] ?? 'NA');

        $filename = sprintf(
            "REP-008_%s_%s_A_%s_%s.pdf",
            strtoupper($tipoSlug),
            $desde,
            $hasta,
            now()->format('Ymd_His')
        );

        return response($bytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control'       => 'private, max-age=0, must-revalidate',
            'Pragma'              => 'public',
        ]);
    }

    private function buildReportFromRequest(Request $request): array
    {
        // Tipos (1–5)
        // 1) producción (día/semana/mes + completitud)
        // 2) productividad por profesional
        // 3) sexo/edad
        // 4) top dx ingreso/alta (con %)
        // 5) tiempos (si hay campos; fallback a updated_at)
        $tipo = $request->query('tipo', 'prod');

        // B) Filtros
        $estado     = $request->query('estado', 'activos'); // activos | completo | borrador | archivado | todos
        $groupBy    = $request->query('group', 'day');      // day | week | month
        $desde      = $request->query('desde');
        $hasta      = $request->query('hasta');
        $role       = $request->query('role', '');          // medico|enfermero|admin|'' (todos)
        $userId     = $request->query('user_id', '');       // id usuario
        $sexo       = $request->query('sexo', '');          // M|F|... o ''
        $edadMin    = $request->query('edad_min', '');
        $edadMax    = $request->query('edad_max', '');
        $provincia  = $request->query('provincia', '');
        $canton     = $request->query('canton', '');
        $parroquia  = $request->query('parroquia', '');

        // Defaults: últimos 30 días
        $hoy = Carbon::today();
        $desdeDate = $desde ? Carbon::parse($desde)->startOfDay() : $hoy->copy()->subDays(30)->startOfDay();
        $hastaDate = $hasta ? Carbon::parse($hasta)->endOfDay() : Carbon::now()->endOfDay();

        // Normaliza group
        if (!in_array($groupBy, ['day', 'week', 'month'], true)) $groupBy = 'day';

        // Normaliza edad
        $edadMin = ($edadMin === '' ? null : (int)$edadMin);
        $edadMax = ($edadMax === '' ? null : (int)$edadMax);

        $filters = [
            'tipo'       => $tipo,
            'estado'     => $estado,
            'group'      => $groupBy,
            'desde'      => $desdeDate->toDateString(),
            'hasta'      => $hastaDate->toDateString(),
            'role'       => $role,
            'user_id'    => $userId,
            'sexo'       => $sexo,
            'edad_min'   => $edadMin,
            'edad_max'   => $edadMax,
            'provincia'  => $provincia,
            'canton'     => $canton,
            'parroquia'  => $parroquia,
        ];

        // UI (selects)
        $ui = [
            'roles' => [
                '' => 'Todos',
                'medico' => 'Médico',
                'enfermero' => 'Enfermero',
                'admin' => 'Administrador',
            ],
            'group' => [
                'day' => 'Día',
                'week' => 'Semana',
                'month' => 'Mes',
            ],
            'estado' => [
                'activos' => 'Activos (sin archivados)',
                'completo' => 'Completos',
                'borrador' => 'Borrador',
                'archivado' => 'Archivados',
                'todos' => 'Todos',
            ],
            'tipos' => [
                'prod' => '1) Producción (día/semana/mes + completitud)',
                'prod_prof' => '2) Productividad por profesional',
                'demo' => '3) Demografía (sexo y rangos de edad)',
                'dx_ingreso' => '4) Top diagnósticos (ingreso)',
                'dx_alta' => '4) Top diagnósticos (alta)',
                'tiempos' => '5) Tiempos de atención / cierre',
            ],
            'users' => User::orderBy('name')->get(['id', 'name', 'role']),
        ];

        // Query base
        $base = Formulario008::query()
            ->with(['creador', 'paciente'])
            ->whereBetween('created_at', [$desdeDate, $hastaDate]);

        // Estado
        if ($estado === 'activos') {
            $base->where('estado', '!=', 'archivado');
        } elseif (in_array($estado, ['completo', 'borrador', 'archivado'], true)) {
            $base->where('estado', $estado);
        } // 'todos' => no filtra

        // Rol / Usuario (creador)
        if ($role !== '') {
            $base->whereHas('creador', fn($q) => $q->where('role', $role));
        }
        if ($userId !== '') {
            $base->where('created_by', (int)$userId);
        }

        // Filtros por paciente (solo si aplica)
        if ($sexo !== '' || $edadMin !== null || $edadMax !== null || $provincia !== '' || $canton !== '' || $parroquia !== '') {
            $base->whereHas('paciente', function ($q) use ($sexo, $edadMin, $edadMax, $provincia, $canton, $parroquia) {
                if ($sexo !== '') $q->where('sexo', $sexo);
                if ($edadMin !== null) $q->where('edad', '>=', $edadMin);
                if ($edadMax !== null) $q->where('edad', '<=', $edadMax);
                if ($provincia !== '') $q->where('provincia', 'like', "%{$provincia}%");
                if ($canton !== '') $q->where('canton', 'like', "%{$canton}%");
                if ($parroquia !== '') $q->where('parroquia', 'like', "%{$parroquia}%");
            });
        }

        // Construcción de reportes
        $report = match ($tipo) {
            'prod' => $this->reportProduccion(clone $base, $filters),
            'prod_prof' => $this->reportProductividadProfesional(clone $base, $filters),
            'demo' => $this->reportDemografia(clone $base, $filters),
            'dx_ingreso' => $this->reportTopDiagnosticosPorcentaje(clone $base, $filters, 'diagnosticos_ingreso', 'Top diagnósticos de ingreso'),
            'dx_alta' => $this->reportTopDiagnosticosPorcentaje(clone $base, $filters, 'diagnosticos_alta', 'Top diagnósticos de alta'),
            'tiempos' => $this->reportTiempos(clone $base, $filters),
            default => $this->reportProduccion(clone $base, $filters),
        };

        return [$filters, $report, $ui];
    }

    /**
     * 1) Producción: agrupa por día/semana/mes + tasa completitud
     */
    private function reportProduccion($query, array $filters): array
    {
        $group = $filters['group'] ?? 'day';

        $selectGroup = match ($group) {
            'week' => "YEARWEEK(created_at, 1) as grp",
            'month' => "DATE_FORMAT(created_at, '%Y-%m') as grp",
            default => "DATE(created_at) as grp",
        };

        $rows = $query
            ->selectRaw($selectGroup)
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN estado='completo' THEN 1 ELSE 0 END) as completos")
            ->selectRaw("SUM(CASE WHEN estado='borrador' THEN 1 ELSE 0 END) as borradores")
            ->selectRaw("SUM(CASE WHEN estado='archivado' THEN 1 ELSE 0 END) as archivados")
            ->groupBy('grp')
            ->orderBy('grp', 'desc')
            ->get()
            ->map(function ($r) {
                $total = (int)$r->total;
                $comp  = (int)$r->completos;
                $pct   = $total > 0 ? round(($comp / $total) * 100, 1) : 0.0;
                return [
                    (string)$r->grp,
                    $total,
                    $comp,
                    (int)$r->borradores,
                    (int)$r->archivados,
                    $pct . '%',
                ];
            })
            ->toArray();

        $totTotal = array_sum(array_column($rows, 1));
        $totComp  = array_sum(array_column($rows, 2));
        $pctTot   = $totTotal > 0 ? round(($totComp / $totTotal) * 100, 1) : 0.0;

        $titleSuffix = match ($group) {
            'week' => ' (por semana)',
            'month' => ' (por mes)',
            default => ' (por día)',
        };

        return [
            'title' => 'Producción de Formularios 008' . $titleSuffix,
            'orientation' => 'P',
            'columns' => [
                ['label' => 'Grupo', 'w' => 35],
                ['label' => 'Total', 'w' => 20],
                ['label' => 'Completos', 'w' => 24],
                ['label' => 'Borrador', 'w' => 24],
                ['label' => 'Archivados', 'w' => 24],
                ['label' => '% Completitud', 'w' => 35],
            ],
            'rows' => $rows,
            'totals' => ['TOTAL', $totTotal, $totComp, array_sum(array_column($rows, 3)), array_sum(array_column($rows, 4)), $pctTot . '%'],
        ];
    }

    /**
     * 2) Productividad por profesional (ranking)
     */
    private function reportProductividadProfesional($query, array $filters): array
    {
        $data = $query
            ->selectRaw("created_by")
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN estado='completo' THEN 1 ELSE 0 END) as completos")
            ->selectRaw("SUM(CASE WHEN estado='borrador' THEN 1 ELSE 0 END) as borradores")
            ->selectRaw("SUM(CASE WHEN estado='archivado' THEN 1 ELSE 0 END) as archivados")
            ->groupBy('created_by')
            ->orderByDesc('total')
            ->get();

        $userIds = $data->pluck('created_by')->filter()->unique()->values()->all();
        $users = User::whereIn('id', $userIds)->get(['id', 'name', 'role'])->keyBy('id');

        $rows = $data->map(function ($r) use ($users) {
            $u = $r->created_by ? ($users[$r->created_by] ?? null) : null;
            $name = $u ? ($u->name . ' (' . strtoupper($u->role) . ')') : '—';

            $total = (int)$r->total;
            $comp  = (int)$r->completos;
            $pct   = $total > 0 ? round(($comp / $total) * 100, 1) : 0.0;

            return [$name, $total, $comp, (int)$r->borradores, (int)$r->archivados, $pct . '%'];
        })->toArray();

        $totTotal = array_sum(array_column($rows, 1));
        $totComp  = array_sum(array_column($rows, 2));
        $pctTot   = $totTotal > 0 ? round(($totComp / $totTotal) * 100, 1) : 0.0;

        return [
            'title' => 'Productividad por profesional',
            'orientation' => 'L',
            'columns' => [
                ['label' => 'Profesional', 'w' => 95],
                ['label' => 'Total', 'w' => 22],
                ['label' => 'Completos', 'w' => 28],
                ['label' => 'Borrador', 'w' => 28],
                ['label' => 'Archivados', 'w' => 28],
                ['label' => '% Completitud', 'w' => 35],
            ],
            'rows' => $rows,
            'totals' => ['TOTAL', $totTotal, $totComp, array_sum(array_column($rows, 3)), array_sum(array_column($rows, 4)), $pctTot . '%'],
        ];
    }

    /**
     * 3) Demografía: sexo + rangos de edad (bucket)
     */
    private function reportDemografia($query, array $filters): array
    {
        // Para demografía tiene más sentido mirar el paciente
        $forms = $query->with('paciente')->get();

        $sexoCounts = [];
        $ageBuckets = [
            '0-5' => 0,
            '6-12' => 0,
            '13-17' => 0,
            '18-29' => 0,
            '30-44' => 0,
            '45-59' => 0,
            '60+' => 0,
            'N/D' => 0,
        ];

        foreach ($forms as $f) {
            $p = $f->paciente;
            if (!$p) continue;

            // Sexo
            $sx = trim((string)($p->sexo ?? 'N/D'));
            if ($sx === '') $sx = 'N/D';
            $sexoCounts[$sx] = ($sexoCounts[$sx] ?? 0) + 1;

            // Edad
            $edad = $p->edad;
            if (!is_numeric($edad)) {
                $ageBuckets['N/D']++;
                continue;
            }
            $edad = (int)$edad;

            if ($edad <= 5) {
                $ageBuckets['0-5']++;
            } elseif ($edad <= 12) {
                $ageBuckets['6-12']++;
            } elseif ($edad <= 17) {
                $ageBuckets['13-17']++;
            } elseif ($edad <= 29) {
                $ageBuckets['18-29']++;
            } elseif ($edad <= 44) {
                $ageBuckets['30-44']++;
            } elseif ($edad <= 59) {
                $ageBuckets['45-59']++;
            } else {
                $ageBuckets['60+']++;
            }
        }

        // Construimos una tabla combinada (se ve pro en PDF)
        $total = array_sum($sexoCounts);

        $rows = [];
        $rows[] = ['--- SEXO ---', '', ''];
        foreach ($sexoCounts as $k => $v) {
            $pct = $total > 0 ? round(($v / $total) * 100, 1) : 0.0;
            $rows[] = [$k, $v, $pct . '%'];
        }

        $rows[] = ['--- EDAD ---', '', ''];
        $totalA = array_sum($ageBuckets);
        foreach ($ageBuckets as $k => $v) {
            $pct = $totalA > 0 ? round(($v / $totalA) * 100, 1) : 0.0;
            $rows[] = [$k, $v, $pct . '%'];
        }

        return [
            'title' => 'Demografía (sexo y rangos de edad)',
            'orientation' => 'P',
            'columns' => [
                ['label' => 'Categoría', 'w' => 95],
                ['label' => 'Conteo', 'w' => 35],
                ['label' => '%', 'w' => 35],
            ],
            'rows' => $rows,
            'totals' => ['TOTAL', $total, '100%'],
            'note' => 'Nota: se calcula sobre formularios dentro del rango. (No pacientes únicos).',
        ];
    }

    /**
     * 4) Diagnósticos top + porcentaje
     * Campo esperado: array/json en Formulario008 (diagnosticos_ingreso / diagnosticos_alta)
     */
    private function reportTopDiagnosticosPorcentaje($query, array $filters, string $field, string $title): array
    {
        $forms = $query->get([$field]);

        $counts = [];
        $totalRegistros = 0;

        foreach ($forms as $f) {
            $arr = $f->{$field} ?? [];
            if (!is_array($arr)) continue;

            foreach ($arr as $dx) {
                $dx = is_string($dx) ? trim($dx) : '';
                if ($dx === '') continue;

                $key = mb_strtoupper($dx);
                $counts[$key] = ($counts[$key] ?? 0) + 1;
                $totalRegistros++;
            }
        }

        arsort($counts);
        $top = array_slice($counts, 0, 30, true);

        $rows = [];
        foreach ($top as $dx => $n) {
            $pct = $totalRegistros > 0 ? round(($n / $totalRegistros) * 100, 1) : 0.0;
            $rows[] = [$dx, $n, $pct . '%'];
        }

        return [
            'title' => $title . ' (Top 30)',
            'orientation' => 'L',
            'columns' => [
                ['label' => 'Diagnóstico', 'w' => 190],
                ['label' => 'Conteo', 'w' => 30],
                ['label' => '%', 'w' => 25],
            ],
            'rows' => $rows,
            'totals' => ['TOTAL REGISTROS', $totalRegistros, '100%'],
            'note' => 'Nota: se cuentan repeticiones de diagnósticos dentro del rango.',
        ];
    }

    /**
     * 5) Tiempos de atención/cierre
     * - Si existe completed_at en formularios008, usa created_at -> completed_at (ideal).
     * - Si no, usa created_at -> updated_at como aproximación (solo para "completos").
     */
    private function reportTiempos($query, array $filters): array
    {
        $table = (new Formulario008)->getTable();
        $hasCompletedAt = Schema::hasColumn($table, 'completed_at');

        // Para tiempos, tiene sentido centrarse en completos
        $query->where('estado', 'completo');

        if ($hasCompletedAt) {
            $query->whereNotNull('completed_at');
        }

        $data = $query
            ->selectRaw("created_by")
            ->selectRaw("COUNT(*) as total")
            ->selectRaw(
                $hasCompletedAt
                    ? "AVG(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) as avg_min"
                    : "AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_min"
            )
            ->selectRaw(
                $hasCompletedAt
                    ? "MAX(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) as max_min"
                    : "MAX(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as max_min"
            )
            ->groupBy('created_by')
            ->orderByDesc('total')
            ->get();

        $userIds = $data->pluck('created_by')->filter()->unique()->values()->all();
        $users = User::whereIn('id', $userIds)->get(['id', 'name', 'role'])->keyBy('id');

        $rows = $data->map(function ($r) use ($users) {
            $u = $r->created_by ? ($users[$r->created_by] ?? null) : null;
            $name = $u ? ($u->name . ' (' . strtoupper($u->role) . ')') : '—';

            $avg = is_numeric($r->avg_min) ? round((float)$r->avg_min, 1) : 0;
            $max = is_numeric($r->max_min) ? (int)$r->max_min : 0;

            return [$name, (int)$r->total, $avg, $max];
        })->toArray();

        $note = $hasCompletedAt
            ? 'Se usa created_at → completed_at.'
            : 'No existe completed_at: se usa created_at → updated_at como aproximación (recomendado agregar completed_at en fase 2).';

        return [
            'title' => 'Tiempos de atención/cierre (completos)',
            'orientation' => 'L',
            'columns' => [
                ['label' => 'Profesional', 'w' => 120],
                ['label' => 'Completos', 'w' => 28],
                ['label' => 'Promedio (min)', 'w' => 35],
                ['label' => 'Máximo (min)', 'w' => 35],
            ],
            'rows' => $rows,
            'totals' => null,
            'note' => $note,
        ];
    }
}
