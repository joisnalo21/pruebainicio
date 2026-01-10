<?php

namespace Tests\Feature\Formulario008;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class GlasgowTotalTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_calcula_glasgow_total_cuando_hay_valores(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 6]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/6", [
            'accion' => 'save',
            'glasgow_ocular' => 4,
            'glasgow_verbal' => 5,
            'glasgow_motora' => 6,
        ]);

        $response->assertSessionHasNoErrors();
        $form->refresh();
        $this->assertSame(15, $form->glasgow_total);
    }

    public function test_glasgow_total_es_null_si_falta_alguna_parte(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 6]);

        $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/6", [
            'accion' => 'save',
            'glasgow_ocular' => 4,
            'glasgow_verbal' => 5,
            // falta motora
        ])->assertSessionHasNoErrors();

        $form->refresh();
        $this->assertNull($form->glasgow_total);
    }
}
