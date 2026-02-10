<?php

namespace Tests\Feature\Medico;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class MedicoGuardarPasoTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_guardar_paso_1_actualiza_y_redirige(): void
    {
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico, null, ['paso_actual' => 1]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$formulario->id}/paso/1", [
            'fecha_admision' => '2025-01-01',
            'forma_llegada' => 'ambulancia',
            'accion' => 'next',
        ]);

        $response->assertRedirect("/medico/formularios/{$formulario->id}/paso/2");

        $formulario->refresh();
        $this->assertSame('borrador', $formulario->estado);
        $this->assertSame(2, (int) $formulario->paso_actual);
    }

    public function test_guardar_paso_2_requiere_detalle_si_motivo_es_otro(): void
    {
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico, null, ['paso_actual' => 2]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$formulario->id}/paso/2", [
            'hora_inicio_atencion' => '10:00',
            'motivo_causa' => 'otro',
        ]);

        $response->assertSessionHasErrors('otro_motivo_detalle');
    }

    public function test_guardar_paso_2_limpia_campos_si_no_aplica_apartado_3(): void
    {
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico, null, ['paso_actual' => 2]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$formulario->id}/paso/2", [
            'hora_inicio_atencion' => '10:00',
            'motivo_causa' => 'trauma',
            'no_aplica_apartado_3' => true,
            'evento_fecha_hora' => '2025-01-01 09:00:00',
            'evento_lugar' => 'Calle',
            'evento_direccion' => 'Av. Principal',
            'evento_tipos' => ['caida'],
            'custodia_policial' => true,
            'aliento_etilico' => true,
            'valor_alcochek' => 20,
            'evento_observaciones' => 'Obs',
            'accion' => 'save',
        ]);

        $response->assertSessionHas('success');

        $formulario->refresh();
        $this->assertNull($formulario->evento_fecha_hora);
        $this->assertNull($formulario->evento_lugar);
        $this->assertNull($formulario->evento_direccion);
        $this->assertNull($formulario->evento_tipos);
        $this->assertFalse((bool) $formulario->custodia_policial);
        $this->assertFalse((bool) $formulario->aliento_etilico);
        $this->assertNull($formulario->valor_alcochek);
        $this->assertNull($formulario->evento_observaciones);
    }

    public function test_guardar_paso_3_requiere_texto_si_antecedente_es_otro(): void
    {
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico, null, ['paso_actual' => 3]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$formulario->id}/paso/3", [
            'antecedentes_tipos' => ['otro'],
        ]);

        $response->assertSessionHasErrors('antecedentes_otro_texto');
    }

    public function test_guardar_paso_3_limpia_campos_si_no_aplica(): void
    {
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico, null, ['paso_actual' => 3]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$formulario->id}/paso/3", [
            'antecedentes_no_aplica' => true,
            'antecedentes_tipos' => ['alergico'],
            'antecedentes_otro_texto' => 'Texto',
            'antecedentes_detalle' => 'Detalle',
            'accion' => 'save',
        ]);

        $response->assertSessionHas('success');

        $formulario->refresh();
        $this->assertNull($formulario->antecedentes_tipos);
        $this->assertNull($formulario->antecedentes_otro_texto);
        $this->assertNull($formulario->antecedentes_detalle);
    }

    public function test_guardar_paso_4_limpia_campos_si_no_aplica(): void
    {
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico, null, ['paso_actual' => 4]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$formulario->id}/paso/4", [
            'no_aplica_enfermedad_actual' => true,
            'via_aerea' => 'libre',
            'condicion' => 'estable',
            'enfermedad_actual_revision' => 'Revision',
            'accion' => 'save',
        ]);

        $response->assertSessionHas('success');

        $formulario->refresh();
        $this->assertNull($formulario->via_aerea);
        $this->assertNull($formulario->condicion);
        $this->assertNull($formulario->enfermedad_actual_revision);
    }
}
