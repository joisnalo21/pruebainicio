<?php

namespace Tests\Feature\Formulario008;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class WizardStepAccessTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_no_permite_saltar_pasos_hacia_adelante(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 2]);

        $response = $this->actingAs($medico)
            ->get("/medico/formularios/{$form->id}/paso/5");

        $response->assertRedirect("/medico/formularios/{$form->id}/paso/2");
        $response->assertSessionHas('success');
    }
}
