<?php

namespace Tests\Unit;

use App\Services\ReportesPdfService;
use Tests\TestCase;

class ReportesPdfServiceTest extends TestCase
{
    public function test_render_generates_pdf_bytes(): void
    {
        $service = new ReportesPdfService();

        $report = [
            'title' => 'Reporte de prueba',
            'columns' => [
                ['label' => 'Columna 1', 'w' => 100],
                ['label' => 'Columna 2', 'w' => 100],
            ],
            'rows' => [
                ['Fila 1', 10],
                ['Fila 2', 20],
            ],
            'totals' => ['TOTAL', 30],
        ];

        $filters = [
            'tipo' => 'prod',
            'desde' => '2025-01-01',
            'hasta' => '2025-01-31',
            'estado' => 'completo',
        ];

        $bytes = $service->render($report, $filters);

        $this->assertIsString($bytes);
        $this->assertNotEmpty($bytes);
        $this->assertSame('%PDF', substr($bytes, 0, 4));
    }

    public function test_render_handles_note_and_auto_orientation(): void
    {
        $service = new ReportesPdfService();

        $report = [
            'title' => 'Reporte ancho',
            'columns' => [
                ['label' => 'Columna 1', 'w' => 120],
                ['label' => 'Columna 2', 'w' => 120],
            ],
            'rows' => [
                ['Fila 1', 10],
            ],
            'note' => 'Nota de prueba.',
        ];

        $filters = [
            'tipo' => 'prod',
            'desde' => '2025-01-01',
            'hasta' => '2025-01-31',
            'estado' => 'activos',
        ];

        $bytes = $service->render($report, $filters);

        $this->assertIsString($bytes);
        $this->assertSame('%PDF', substr($bytes, 0, 4));
    }
}
