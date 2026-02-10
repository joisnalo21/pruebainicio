<?php

namespace Tests\Feature\Enfermeria;

use App\Models\Formulario008;
use App\Services\Formulario008PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class EnfermeriaFormulario008ControllerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_index_excludes_archived_by_default(): void
    {
        $enfermero = $this->createUser('enfermero');
        $medico = $this->createUser('medico');

        $active = $this->createFormulario($medico, null, ['estado' => 'borrador']);
        $archived = $this->createFormulario($medico, null, ['estado' => 'archivado', 'archivado_en' => now()]);

        $response = $this->actingAs($enfermero)->get('/enfermeria/formularios');

        $response->assertOk();
        $response->assertViewHas('formularios', function ($formularios) use ($active, $archived) {
            $ids = $formularios->getCollection()->pluck('id')->all();
            return in_array($active->id, $ids, true) && !in_array($archived->id, $ids, true);
        });
    }

    public function test_resumen_displays_formulario(): void
    {
        $enfermero = $this->createUser('enfermero');
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico);

        $response = $this->actingAs($enfermero)->get("/enfermeria/formularios/{$formulario->id}/resumen");

        $response->assertOk();
        $response->assertViewHas('formulario', function ($viewFormulario) use ($formulario) {
            return $viewFormulario->id === $formulario->id;
        });
    }

    public function test_pdf_requires_completed_form(): void
    {
        $enfermero = $this->createUser('enfermero');
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico, null, ['estado' => 'borrador']);

        $response = $this->actingAs($enfermero)->get("/enfermeria/formularios/{$formulario->id}/pdf");

        $response->assertStatus(403);
    }

    public function test_pdf_uses_service_for_completed_form(): void
    {
        $enfermero = $this->createUser('enfermero');
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico, null, ['estado' => 'completo']);

        $this->mock(Formulario008PdfService::class, function ($mock) use ($formulario) {
            $mock->shouldReceive('render')
                ->once()
                ->withArgs(function (Formulario008 $bound, bool $grid) use ($formulario) {
                    return $bound->id === $formulario->id && $grid === false;
                })
                ->andReturn('PDFBYTES');
        });

        $response = $this->actingAs($enfermero)->get("/enfermeria/formularios/{$formulario->id}/pdf");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertSee('PDFBYTES');
    }

    public function test_ver_paso_invalid_returns_404(): void
    {
        $enfermero = $this->createUser('enfermero');
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico);

        $response = $this->actingAs($enfermero)->get("/enfermeria/formularios/{$formulario->id}/ver/paso/99");

        $response->assertStatus(404);
    }
}
