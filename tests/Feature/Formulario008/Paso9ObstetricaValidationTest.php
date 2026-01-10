<?php

namespace Tests\Feature\Formulario008;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class Paso9ObstetricaValidationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_no_aplica_obstetrica_limpia_campos_y_apaga_booleans(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 9]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/9", [
            'accion' => 'next',
            'no_aplica_obstetrica' => '1',
            'obst_gestas' => 2,
            'obst_fum' => '2025-12-01',
            'obst_membranas_rotas' => '1',
            'obst_tiempo_membranas_rotas' => '2 horas',
            'obst_sangrado_vaginal' => '1',
            'obst_contracciones' => '1',
            'obst_texto' => 'Texto',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/medico/formularios/{$form->id}/paso/10");

        $form->refresh();
        $this->assertTrue($form->no_aplica_obstetrica);

        $this->assertNull($form->obst_gestas);
        $this->assertNull($form->obst_fum);
        $this->assertNull($form->obst_tiempo_membranas_rotas);
        $this->assertNull($form->obst_texto);

        $this->assertFalse((bool) $form->obst_membranas_rotas);
        $this->assertFalse((bool) $form->obst_sangrado_vaginal);
        $this->assertFalse((bool) $form->obst_contracciones);
    }

    public function test_si_membranas_no_rotas_limpia_tiempo_membranas_rotas(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 9]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/9", [
            'accion' => 'next',
            'no_aplica_obstetrica' => '0',
            'obst_membranas_rotas' => '0',
            'obst_tiempo_membranas_rotas' => '3 horas',
        ]);

        $response->assertSessionHasNoErrors();

        $form->refresh();
        $this->assertFalse((bool) $form->no_aplica_obstetrica);
        $this->assertFalse((bool) $form->obst_membranas_rotas);
        $this->assertNull($form->obst_tiempo_membranas_rotas);
    }
}
