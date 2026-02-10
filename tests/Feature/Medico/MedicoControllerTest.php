<?php

namespace Tests\Feature\Medico;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class MedicoControllerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_listar_formularios_excludes_archived_by_default(): void
    {
        $medico = $this->createUser('medico');

        $active = $this->createFormulario($medico, null, ['estado' => 'borrador']);
        $archived = $this->createFormulario($medico, null, ['estado' => 'archivado', 'archivado_en' => now()]);

        $response = $this->actingAs($medico)->get('/medico/formularios');

        $response->assertOk();
        $response->assertViewHas('formularios', function ($formularios) use ($active, $archived) {
            $ids = $formularios->getCollection()->pluck('id')->all();
            return in_array($active->id, $ids, true) && !in_array($archived->id, $ids, true);
        });
    }

    public function test_seleccionar_paciente_filters_by_query(): void
    {
        $medico = $this->createUser('medico');

        $target = $this->createPaciente([
            'primer_nombre' => 'Andrea',
            'apellido_paterno' => 'Lopez',
        ]);
        $this->createPaciente([
            'primer_nombre' => 'Carlos',
            'apellido_paterno' => 'Gomez',
        ]);

        $response = $this->actingAs($medico)->get('/medico/formularios/nuevo?q=Andrea');

        $response->assertOk();
        $response->assertViewHas('pacientes', function ($pacientes) use ($target) {
            $ids = $pacientes->getCollection()->pluck('id')->all();
            return in_array($target->id, $ids, true) && count($ids) === 1;
        });
    }

    public function test_iniciar_formulario_creates_draft_and_redirects(): void
    {
        $medico = $this->createUser('medico');
        $paciente = $this->createPaciente();

        $response = $this->actingAs($medico)->post('/medico/formularios/iniciar', [
            'paciente_id' => $paciente->id,
        ]);

        $formulario = \App\Models\Formulario008::where('created_by', $medico->id)->latest()->first();

        $response->assertRedirect("/medico/formularios/{$formulario->id}/paso/1");
        $this->assertDatabaseHas('formularios008', [
            'id' => $formulario->id,
            'paciente_id' => $paciente->id,
            'created_by' => $medico->id,
            'estado' => 'borrador',
            'paso_actual' => 1,
        ]);
    }

    public function test_wizard_paso_requires_owner(): void
    {
        $owner = $this->createUser('medico');
        $other = $this->createUser('medico');
        $formulario = $this->createFormulario($owner);

        $response = $this->actingAs($other)->get("/medico/formularios/{$formulario->id}/paso/1");

        $response->assertStatus(403);
    }

    public function test_wizard_paso_redirects_when_form_is_complete(): void
    {
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico, null, ['estado' => 'completo']);

        $response = $this->actingAs($medico)->get("/medico/formularios/{$formulario->id}/paso/2");

        $response->assertRedirect("/medico/formularios/{$formulario->id}/ver/paso/2");
    }
}
