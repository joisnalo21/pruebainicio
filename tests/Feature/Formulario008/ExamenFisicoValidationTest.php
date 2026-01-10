<?php

namespace Tests\Feature\Formulario008;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class ExamenFisicoValidationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    private function examenKeys(): array
    {
        return [
            // Regional (R)
            '1-R','2-R','3-R','4-R','5-R','6-R','7-R','8-R','9-R','10-R','11-R','12-R','13-R','14-R','15-R',
            // Sistémico (S)
            '1-S','2-S','3-S','4-S','5-S','6-S','7-S','8-S','9-S','10-S',
        ];
    }

    public function test_para_avanzar_debe_marcar_todos_los_items(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 7]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/7", [
            'accion' => 'next',
            'examen_fisico_checks' => [
                '1-R' => 'SP',
                // faltan el resto
            ],
        ]);

        $response->assertSessionHasErrors('examen_fisico_checks');
    }

    public function test_si_hay_cp_y_se_quiere_avanzar_exige_descripcion(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 7]);

        $checks = array_fill_keys($this->examenKeys(), 'SP');
        $checks['3-R'] = 'CP';

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/7", [
            'accion' => 'next',
            'examen_fisico_checks' => $checks,
            'examen_fisico_descripcion' => 'corto',
        ]);

        $response->assertSessionHasErrors('examen_fisico_descripcion');
    }

    public function test_puede_guardar_y_avanzar_si_todo_es_valido(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 7]);

        $checks = array_fill_keys($this->examenKeys(), 'SP');
        $checks['3-R'] = 'CP';

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/7", [
            'accion' => 'next',
            'examen_fisico_checks' => $checks,
            'examen_fisico_descripcion' => 'Hallazgos presentes en la evaluación física.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/medico/formularios/{$form->id}/paso/8");

        $form->refresh();
        $this->assertSame('borrador', $form->estado);
        $this->assertSame(8, $form->paso_actual);
        $this->assertNotEmpty($form->examen_fisico_checks);
    }
}
