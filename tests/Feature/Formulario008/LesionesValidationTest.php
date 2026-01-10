<?php

namespace Tests\Feature\Formulario008;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class LesionesValidationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_lesiones_rechaza_vista_invalida(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 8]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/8", [
            'accion' => 'save',
            'no_aplica_lesiones' => false,
            'lesiones' => json_encode([
                ['view' => 'side', 'x' => 0.5, 'y' => 0.5, 'tipo' => 1],
            ]),
        ]);

        $response->assertSessionHasErrors('lesiones');
    }

    public function test_guarda_lesiones_validas_y_avanza_al_paso_9(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 8]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/8", [
            'accion' => 'next',
            'no_aplica_lesiones' => false,
            // En el backend se valida: view(front|back) + x/y (0..1) + tipo (1..14)
            'lesiones' => json_encode([
                ['view' => 'front', 'x' => 0.5, 'y' => 0.5, 'tipo' => 1],
            ]),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/medico/formularios/{$form->id}/paso/9");

        $form->refresh();
        $this->assertSame(9, (int) $form->paso_actual);
        $this->assertSame('borrador', $form->estado);

        $this->assertIsArray($form->lesiones);
        $this->assertCount(1, $form->lesiones);

        $lesion = $form->lesiones[0];
        $this->assertSame('front', $lesion['view']);
        $this->assertEquals(0.5, $lesion['x']);
        $this->assertEquals(0.5, $lesion['y']);
        $this->assertSame(1, $lesion['tipo']);
    }

    public function test_no_aplica_lesiones_limpia_el_array(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, [
            'paso_actual' => 8,
            'lesiones' => [['view' => 'front', 'x' => 0.2, 'y' => 0.3, 'tipo' => 2]],
        ]);

        $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/8", [
            'accion' => 'save',
            'no_aplica_lesiones' => true,
        ])->assertSessionHasNoErrors();

        $form->refresh();
        $this->assertTrue((bool) $form->no_aplica_lesiones);
        $this->assertSame([], $form->lesiones);
    }
}
