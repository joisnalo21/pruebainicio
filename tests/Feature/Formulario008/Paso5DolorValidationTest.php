<?php

namespace Tests\Feature\Formulario008;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class Paso5DolorValidationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_no_aplica_dolor_setea_null_y_bandera(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 5]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/5", [
            'accion' => 'next',
            'no_aplica_dolor' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/medico/formularios/{$form->id}/paso/6");

        $form->refresh();
        $this->assertTrue($form->no_aplica_dolor);
        $this->assertNull($form->dolor_items);
    }

    public function test_si_hay_dolor_exige_region_y_punto(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 5]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/5", [
            'accion' => 'save',
            'no_aplica_dolor' => '0',
            'dolor' => [
                [
                    'region' => '',
                    'punto' => 'Epigastrio',
                    'intensidad' => 7,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('dolor.0.region');
    }

    public function test_filtra_filas_totalmente_vacias_y_guarda_solo_las_validas(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['paso_actual' => 5]);

        $response = $this->actingAs($medico)->post("/medico/formularios/{$form->id}/paso/5", [
            'accion' => 'next',
            'no_aplica_dolor' => '0',
            'dolor' => [
                [], // debe eliminarse
                [
                    'region' => 'Abdomen',
                    'punto' => 'Fosa ilíaca derecha',
                    'situacion' => 'localizado',
                    'evolucion' => 'agudo',
                    'tipo' => 'continuo',
                    'se_modifica_con' => ['posicion'],
                    'alivia_con' => ['analgesico'],
                    'intensidad' => 8,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/medico/formularios/{$form->id}/paso/6");

        $form->refresh();
        $this->assertFalse($form->no_aplica_dolor);

        $items = $form->dolor_items;
        $this->assertIsArray($items);
        $this->assertCount(1, $items);
        $this->assertSame('Abdomen', $items[0]['region']);
        $this->assertSame('Fosa ilíaca derecha', $items[0]['punto']);
        $this->assertSame(8, $items[0]['intensidad']);
    }
}
