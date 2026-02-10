<?php

namespace Tests\Feature\Routes;

use App\Services\Formulario008PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class EnfermeriaRoutesTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_enfermeria_routes_are_accessible(): void
    {
        $enfermero = $this->createUser('enfermero');
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico);

        $this->actingAs($enfermero)->get('/enfermeria/dashboard')->assertOk();
        $this->actingAs($enfermero)->get('/enfermeria/formularios')->assertOk();
        $this->actingAs($enfermero)->get("/enfermeria/formularios/{$formulario->id}/resumen")->assertOk();
        $this->actingAs($enfermero)->get("/enfermeria/formularios/{$formulario->id}/ver/paso/1")->assertOk();
    }

    public function test_enfermeria_pacientes_routes_are_accessible(): void
    {
        $enfermero = $this->createUser('enfermero');
        $paciente = $this->createPaciente();

        $this->actingAs($enfermero)->get('/enfermeria/pacientes')->assertOk();
        $this->actingAs($enfermero)->get('/enfermeria/pacientes/create')->assertOk();
        $this->actingAs($enfermero)->get("/enfermeria/pacientes/{$paciente->id}/edit")->assertOk();
        $this->actingAs($enfermero)->get('/enfermeria/pacientes/validar-cedula/1234567890')->assertOk();
    }

    public function test_enfermeria_pdf_route_returns_pdf_for_completed_form(): void
    {
        $enfermero = $this->createUser('enfermero');
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico, null, ['estado' => 'completo']);

        $this->mock(Formulario008PdfService::class, function ($mock) use ($formulario) {
            $mock->shouldReceive('render')
                ->once()
                ->withArgs(function ($bound, $grid) use ($formulario) {
                    return $bound->id === $formulario->id && $grid === false;
                })
                ->andReturn('PDFBYTES');
        });

        $this->actingAs($enfermero)
            ->get("/enfermeria/formularios/{$formulario->id}/pdf")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
