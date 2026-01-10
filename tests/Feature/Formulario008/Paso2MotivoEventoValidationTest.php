<?php

namespace Tests\Feature\Formulario008;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class Paso2MotivoEventoValidationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_motivo_otro_exige_detalle(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 2]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/2", [
            'accion' => 'save',
            'hora_inicio_atencion' => '08:00',
            'motivo_causa' => 'otro',
        ]);

        $response->assertSessionHasErrors('otro_motivo_detalle');
    }

    public function test_no_aplica_apartado_3_limpia_campos_dependientes(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 2]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/2", [
            'accion' => 'next',
            'hora_inicio_atencion' => '08:00',
            'motivo_causa' => 'trauma',
            'no_aplica_apartado_3' => '1',

            // Intentamos mandar datos "basura" que deberían limpiarse
            'evento_fecha_hora' => '2026-01-10 08:30:00',
            'evento_lugar' => 'Calle 1',
            'evento_direccion' => 'Dir',
            'evento_tipos' => ['caida'],
            'custodia_policial' => '1',
            'aliento_etilico' => '1',
            'valor_alcochek' => '0.5',
            'evento_observaciones' => 'Obs',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/medico/formularios/{$form->id}/paso/3");

        $form->refresh();
        $this->assertTrue($form->no_aplica_apartado_3);
        $this->assertNull($form->evento_fecha_hora);
        $this->assertNull($form->evento_lugar);
        $this->assertNull($form->evento_direccion);
        $this->assertNull($form->evento_tipos);
        $this->assertFalse((bool) $form->custodia_policial);
        $this->assertFalse((bool) $form->aliento_etilico);
        $this->assertNull($form->valor_alcochek);
        $this->assertNull($form->evento_observaciones);
    }
}
