<?php

namespace Tests\Feature\Formulario008;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class Paso13AltaRulesTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_referencia_exige_servicio_y_establecimiento(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 13]);

        // 1) falta servicio -> el controller retorna aquí (no valida establecimiento aún)
        $r1 = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/13", [
            'accion' => 'save',
            'alta_destino' => 'referencia',
            'alta_resultado' => 'vivo',
            'alta_condicion' => 'estable',
            // falta alta_servicio_referencia
            // falta alta_establecimiento_referencia
        ]);
        $r1->assertStatus(302);
        $r1->assertSessionHasErrors(['alta_servicio_referencia']);

        // 2) ya con servicio, ahora debe exigir establecimiento
        $r2 = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/13", [
            'accion' => 'save',
            'alta_destino' => 'referencia',
            'alta_servicio_referencia' => 'Emergencias',
            'alta_resultado' => 'vivo',
            'alta_condicion' => 'estable',
            // falta alta_establecimiento_referencia
        ]);
        $r2->assertStatus(302);
        $r2->assertSessionHasErrors(['alta_establecimiento_referencia']);
    }

    public function test_resultado_vivo_exige_condicion(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 13]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/13", [
            'accion' => 'save',
            'alta_destino' => 'domicilio',
            'alta_resultado' => 'vivo',
            // falta alta_condicion
        ]);

        $response->assertSessionHasErrors('alta_condicion');
    }

    public function test_resultado_muerto_exige_causa(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 13]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/13", [
            'accion' => 'save',
            'alta_destino' => 'observacion',
            'alta_resultado' => 'muerto_emergencia',
            // falta alta_causa
        ]);

        $response->assertSessionHasErrors('alta_causa');
    }

    public function test_finish_exige_hora_finalizacion_y_codigo_profesional_y_marca_completo(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 13]);

        // 1) falta hora -> retorna primero con ese error
        $badHora = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/13", [
            'accion' => 'finish',
            'alta_destino' => 'domicilio',
            'alta_resultado' => 'vivo',
            'alta_condicion' => 'estable',
            'alta_profesional_codigo' => 'ABC-123',
            // falta alta_hora_finalizacion
        ]);
        $badHora->assertStatus(302);
        $badHora->assertSessionHasErrors(['alta_hora_finalizacion']);

        // 2) ya con hora, falta código
        $badCodigo = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/13", [
            'accion' => 'finish',
            'alta_destino' => 'domicilio',
            'alta_resultado' => 'vivo',
            'alta_condicion' => 'estable',
            'alta_hora_finalizacion' => '10:45',
            // falta alta_profesional_codigo
        ]);
        $badCodigo->assertStatus(302);
        $badCodigo->assertSessionHasErrors(['alta_profesional_codigo']);

        // 3) ok con todo
        $ok = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/13", [
            'accion' => 'finish',
            'alta_destino' => 'domicilio',
            'alta_resultado' => 'vivo',
            'alta_condicion' => 'estable',
            'alta_hora_finalizacion' => '10:45',
            'alta_profesional_codigo' => 'ABC-123',
            'alta_numero_hoja' => 1,
        ]);

        $ok->assertSessionHasNoErrors();
        $ok->assertRedirect('/medico/formularios');

        $form->refresh();
        $this->assertSame('completo', $form->estado);
    }
}
