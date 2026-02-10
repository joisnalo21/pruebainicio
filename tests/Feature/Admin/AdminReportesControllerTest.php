<?php

namespace Tests\Feature\Admin;

use App\Services\ReportesPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class AdminReportesControllerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_index_builds_demografia_report(): void
    {
        $admin = $this->createUser('admin');
        $medico = $this->createUser('medico');

        $paciente = $this->createPaciente([
            'sexo' => 'F',
            'edad' => 25,
        ]);

        $this->createFormulario($medico, $paciente, ['estado' => 'completo']);

        $response = $this->actingAs($admin)->get('/admin/reportes?tipo=demo');

        $response->assertOk();
        $response->assertViewHas('report', function ($report) {
            return isset($report['rows']) && count($report['rows']) > 0;
        });
    }

    public function test_index_builds_top_diagnosticos_report(): void
    {
        $admin = $this->createUser('admin');
        $medico = $this->createUser('medico');

        $paciente = $this->createPaciente(['sexo' => 'M', 'edad' => 40]);

        $this->createFormulario($medico, $paciente, [
            'estado' => 'completo',
            'diagnosticos_ingreso' => ['dx uno', 'dx uno', 'dx dos'],
        ]);

        $response = $this->actingAs($admin)->get('/admin/reportes?tipo=dx_ingreso');

        $response->assertOk();
        $response->assertViewHas('report', function ($report) {
            $rows = $report['rows'] ?? [];
            return count($rows) > 0 && $rows[0][1] >= 1;
        });
    }

    public function test_pdf_uses_service_and_returns_pdf(): void
    {
        $admin = $this->createUser('admin');
        $medico = $this->createUser('medico');

        $paciente = $this->createPaciente(['sexo' => 'M', 'edad' => 30]);
        $this->createFormulario($medico, $paciente, ['estado' => 'completo']);

        $this->mock(ReportesPdfService::class, function ($mock) {
            $mock->shouldReceive('render')
                ->once()
                ->andReturn('PDFBYTES');
        });

        $response = $this->actingAs($admin)->get('/admin/reportes/pdf?tipo=prod');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertSee('PDFBYTES');
    }
}
