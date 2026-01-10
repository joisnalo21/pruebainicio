<?php

namespace Tests\Feature\Formulario008;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class Paso10ExamenesValidationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_no_aplica_examenes_limpia_lista_y_comentarios(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 10]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/10", [
            'accion' => 'next',
            'no_aplica_examenes' => '1',
            'examenes_solicitados' => ['1_biometria', '6_electrocardiograma'],
            'examenes_comentarios' => 'No debería guardarse',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/medico/formularios/{$form->id}/paso/11");

        $form->refresh();
        $this->assertTrue($form->no_aplica_examenes);
        $this->assertNull($form->examenes_solicitados);
        $this->assertNull($form->examenes_comentarios);
    }
}
