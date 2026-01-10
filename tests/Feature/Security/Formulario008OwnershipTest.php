<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class Formulario008OwnershipTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_otro_medico_no_puede_ver_wizard_de_un_formulario_ajeno(): void
    {
        $medico1 = $this->createUser('medico');
        $medico2 = $this->createUser('medico');

        $form = $this->createFormulario($medico1, null, ['paso_actual' => 1]);

        $this->actingAs($medico2)
            ->get("/medico/formularios/{$form->id}/paso/1")
            ->assertForbidden();
    }

    public function test_otro_medico_no_puede_postear_pasos_de_un_formulario_ajeno(): void
    {
        $medico1 = $this->createUser('medico');
        $medico2 = $this->createUser('medico');

        $form = $this->createFormulario($medico1, null, ['paso_actual' => 1]);

        $this->actingAs($medico2)
            ->post("/medico/formularios/{$form->id}/paso/1", [
                'accion' => 'save',
                'fecha_admision' => '2026-01-10',
                'forma_llegada' => 'ambulatorio',
                'hora_inicio_atencion' => '08:00',
            ])
            ->assertForbidden();
    }
}
