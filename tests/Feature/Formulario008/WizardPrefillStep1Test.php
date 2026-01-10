<?php

namespace Tests\Feature\Formulario008;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class WizardPrefillStep1Test extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_paso1_prefill_carga_datos_del_hospital_si_estan_vacios(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, [
            'paso_actual' => 1,
            'institucion_sistema' => null,
            'unidad_operativa' => null,
        ]);

        $this->actingAs($medico)
            ->get("/medico/formularios/{$form->id}/paso/1")
            ->assertOk();

        $form->refresh();

        $this->assertSame(config('form008.hospital.institucion_sistema'), $form->institucion_sistema);
        $this->assertSame(config('form008.hospital.unidad_operativa'), $form->unidad_operativa);
    }
}
