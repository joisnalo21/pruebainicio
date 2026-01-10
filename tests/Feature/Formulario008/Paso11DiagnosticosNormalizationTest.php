<?php

namespace Tests\Feature\Formulario008;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class Paso11DiagnosticosNormalizationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_diagnosticos_se_normalizan_a_3_filas_con_nulls(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 11]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/11", [
            'accion' => 'next',
            'diagnosticos_ingreso' => [
                1 => ['dx' => 'Gastritis', 'cie' => 'K29', 'tipo' => 'pre'],
                2 => ['dx' => '', 'cie' => '', 'tipo' => 'def'],
                3 => ['dx' => '  ', 'cie' => '  '], // sin tipo -> queda null
            ],
            'diagnosticos_alta' => [
                1 => ['dx' => 'Alta dx', 'cie' => 'A00', 'tipo' => 'def'],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/medico/formularios/{$form->id}/paso/12");

        $form->refresh();

        $ingreso = $form->diagnosticos_ingreso;
        $this->assertIsArray($ingreso);
        $this->assertCount(3, $ingreso);

        $rows = array_values($ingreso);
        $this->assertSame([1, 2, 3], array_map(fn ($r) => $r['n'] ?? null, $rows));

        $this->assertSame('Gastritis', $rows[0]['dx']);
        $this->assertSame('K29', $rows[0]['cie']);
        $this->assertSame('pre', $rows[0]['tipo']);

        $this->assertNull($rows[1]['dx']);
        $this->assertNull($rows[1]['cie']);
        $this->assertSame('def', $rows[1]['tipo']);

        $this->assertNull($rows[2]['dx']);
        $this->assertNull($rows[2]['cie']);
        $this->assertNull($rows[2]['tipo']);

        $alta = $form->diagnosticos_alta;
        $this->assertIsArray($alta);
        $this->assertCount(3, $alta);

        $altaRows = array_values($alta);
        $this->assertSame('Alta dx', $altaRows[0]['dx']);
        $this->assertSame('A00', $altaRows[0]['cie']);
        $this->assertSame('def', $altaRows[0]['tipo']);
        $this->assertNull($altaRows[1]['dx']);
        $this->assertNull($altaRows[2]['dx']);
    }
}
