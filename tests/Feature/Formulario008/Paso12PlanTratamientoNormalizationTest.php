<?php

namespace Tests\Feature\Formulario008;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class Paso12PlanTratamientoNormalizationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_plan_se_normaliza_a_4_filas_con_nulls(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 12]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/12", [
            'accion' => 'next',
            'plan_tratamiento' => [
                1 => ['indicaciones' => 'Reposo', 'medicamento' => 'Paracetamol', 'posologia' => '500mg c/8h'],
                2 => ['indicaciones' => '', 'medicamento' => '', 'posologia' => ''],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/medico/formularios/{$form->id}/paso/13");

        $form->refresh();
        $plan = $form->plan_tratamiento;

        $this->assertIsArray($plan);
        $this->assertCount(4, $plan);

        $rows = array_values($plan);
        $this->assertSame([1, 2, 3, 4], array_map(fn ($r) => $r['n'] ?? null, $rows));

        $this->assertSame('Reposo', $rows[0]['indicaciones']);
        $this->assertSame('Paracetamol', $rows[0]['medicamento']);
        $this->assertSame('500mg c/8h', $rows[0]['posologia']);

        $this->assertNull($rows[1]['indicaciones']);
        $this->assertNull($rows[1]['medicamento']);
        $this->assertNull($rows[1]['posologia']);

        $this->assertNull($rows[2]['indicaciones']);
        $this->assertNull($rows[3]['indicaciones']);
    }
}
