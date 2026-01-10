<?php

namespace Tests\Feature\Formulario008;

use App\Models\Formulario008;
use App\Services\Formulario008PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class PdfAccessTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_pdf_se_prohibe_si_formulario_no_esta_completo(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['estado' => 'borrador', 'paso_actual' => 13]);

        $this->actingAs($medico)
            ->get("/medico/formularios/{$form->id}/pdf")
            ->assertStatus(403);
    }

    public function test_pdf_se_entrega_si_formulario_esta_completo(): void
    {
        // Mock del servicio para no depender de FPDI en tests
        $this->app->instance(Formulario008PdfService::class, new class extends Formulario008PdfService {
            // IMPORTANTE: misma firma que el servicio real
            public function render(Formulario008 $f, bool $grid = false): string
            {
                return '%PDF-1.4\n%mock\n';
            }
        });

        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['estado' => 'completo', 'paso_actual' => 13]);

        $response = $this->actingAs($medico)->get("/medico/formularios/{$form->id}/pdf");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('%PDF-1.4', $response->getContent());
    }
}
