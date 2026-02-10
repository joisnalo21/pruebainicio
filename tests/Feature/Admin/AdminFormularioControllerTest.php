<?php

namespace Tests\Feature\Admin;

use App\Models\Formulario008;
use App\Services\Formulario008PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class AdminFormularioControllerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_index_excludes_archived_by_default(): void
    {
        $admin = $this->createUser('admin');
        $medico = $this->createUser('medico');

        $active = $this->createFormulario($medico, null, ['estado' => 'borrador']);
        $archived = $this->createFormulario($medico, null, ['estado' => 'archivado', 'archivado_en' => now()]);

        $response = $this->actingAs($admin)->get('/admin/formularios');

        $response->assertOk();
        $response->assertViewHas('formularios', function ($formularios) use ($active, $archived) {
            $ids = $formularios->getCollection()->pluck('id')->all();
            return in_array($active->id, $ids, true) && !in_array($archived->id, $ids, true);
        });
    }

    public function test_show_redirects_to_readonly_step(): void
    {
        $admin = $this->createUser('admin');
        $medico = $this->createUser('medico');

        $formulario = $this->createFormulario($medico, null, [
            'estado' => 'completo',
            'paso_actual' => 4,
        ]);

        $response = $this->actingAs($admin)->get("/admin/formularios/{$formulario->id}");

        $response->assertRedirect("/admin/formularios/{$formulario->id}/ver/paso/13");
    }

    public function test_ver_paso_invalid_returns_404(): void
    {
        $admin = $this->createUser('admin');
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico);

        $response = $this->actingAs($admin)->get("/admin/formularios/{$formulario->id}/ver/paso/99");

        $response->assertStatus(404);
    }

    public function test_pdf_uses_service_for_completed_form(): void
    {
        $admin = $this->createUser('admin');
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

        $response = $this->actingAs($admin)->get("/admin/formularios/{$formulario->id}/pdf");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertSee('PDFBYTES');
    }

    public function test_archivar_and_desarchivar_workflow(): void
    {
        $admin = $this->createUser('admin');
        $medico = $this->createUser('medico');

        $formulario = $this->createFormulario($medico, null, ['estado' => 'borrador']);

        $archivar = $this->actingAs($admin)->patch("/admin/formularios/{$formulario->id}/archivar");
        $archivar->assertSessionHas('success');
        $formulario->refresh();
        $this->assertSame('archivado', $formulario->estado);
        $this->assertNotNull($formulario->archivado_en);

        $desarchivar = $this->actingAs($admin)->patch("/admin/formularios/{$formulario->id}/desarchivar");
        $desarchivar->assertSessionHas('success');
        $formulario->refresh();
        $this->assertSame('borrador', $formulario->estado);
        $this->assertNull($formulario->archivado_en);
    }

    public function test_archivar_already_archived_shows_error(): void
    {
        $admin = $this->createUser('admin');
        $medico = $this->createUser('medico');

        $formulario = $this->createFormulario($medico, null, ['estado' => 'archivado', 'archivado_en' => now()]);

        $response = $this->actingAs($admin)->patch("/admin/formularios/{$formulario->id}/archivar");

        $response->assertSessionHas('error');
    }

    public function test_destroy_deletes_form(): void
    {
        $admin = $this->createUser('admin');
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico);

        $response = $this->actingAs($admin)->delete("/admin/formularios/{$formulario->id}");

        $response->assertRedirect('/admin/formularios');
        $this->assertDatabaseMissing('formularios008', ['id' => $formulario->id]);
    }
}
