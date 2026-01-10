<?php

namespace Tests\Feature\Formulario008;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class Paso3AntecedentesValidationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_si_selecciona_otro_exige_texto(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 3]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/3", [
            'accion' => 'save',
            'antecedentes_no_aplica' => '0',
            'antecedentes_tipos' => ['otro'],
            'antecedentes_detalle' => 'Detalle cualquiera',
            // falta antecedentes_otro_texto
        ]);

        $response->assertSessionHasErrors('antecedentes_otro_texto');
    }

    public function test_no_aplica_limpia_campos_de_antecedentes(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 3]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/3", [
            'accion' => 'next',
            'antecedentes_no_aplica' => '1',
            'antecedentes_tipos' => ['clinico', 'otro'],
            'antecedentes_otro_texto' => 'No debería guardarse',
            'antecedentes_detalle' => 'No debería guardarse',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/medico/formularios/{$form->id}/paso/4");

        $form->refresh();
        $this->assertTrue($form->antecedentes_no_aplica);
        $this->assertNull($form->antecedentes_tipos);
        $this->assertNull($form->antecedentes_otro_texto);
        $this->assertNull($form->antecedentes_detalle);
    }
}
