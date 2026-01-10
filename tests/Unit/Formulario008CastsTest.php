<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class Formulario008CastsTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_casts_de_arrays_booleans_y_fechas_funcionan_correctamente(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 10, 12, 0, 0));

        $medico = $this->createUser('medico');

        $form = $this->createFormulario($medico, null, [
            // booleans
            'notificacion_policia' => true,
            'no_aplica_apartado_3' => false,
            'custodia_policial' => true,
            'aliento_etilico' => true,
            'antecedentes_no_aplica' => false,
            'no_aplica_dolor' => false,
            'no_aplica_lesiones' => false,
            'no_aplica_obstetrica' => false,
            'obst_membranas_rotas' => true,
            'no_aplica_examenes' => false,

            // arrays
            'evento_tipos' => ['accidente', 'violencia'],
            'antecedentes_tipos' => ['alergias', 'quirurgicos'],
            'dolor_items' => [
                ['region' => 'Abdomen', 'punto' => 'Epigastrio', 'intensidad' => 7],
            ],
            'examen_fisico_checks' => ['cabeza' => 'SP'],
            'lesiones' => [
                ['view' => 'front', 'x' => 0.25, 'y' => 0.40, 'tipo' => 1, 'detalle' => 'Corte'],
            ],
            'examenes_solicitados' => ['rx', 'lab'],
            'diagnosticos_ingreso' => [
                ['cie' => 'S09.9', 'descripcion' => 'Trauma en cabeza'],
            ],
            'plan_tratamiento' => [
                ['descripcion' => 'Analgésicos'],
            ],

            // fechas
            'evento_fecha_hora' => '2026-01-10 11:30:00',
            'obst_fum' => '2025-12-31',
            'alta_fecha_control' => '2026-01-20',

            // decimales
            'temp_bucal' => 36.5,
            'peso' => 77.1,
            'tiempo_llenado_capilar' => 2.5,
        ]);

        $fresh = $form->fresh();

        // booleans
        $this->assertTrue($fresh->notificacion_policia);
        $this->assertFalse($fresh->no_aplica_apartado_3);
        $this->assertTrue($fresh->custodia_policial);
        $this->assertTrue($fresh->aliento_etilico);
        $this->assertTrue($fresh->obst_membranas_rotas);

        // arrays
        $this->assertIsArray($fresh->evento_tipos);
        $this->assertSame(['accidente', 'violencia'], $fresh->evento_tipos);

        $this->assertIsArray($fresh->dolor_items);
        $this->assertSame('Abdomen', $fresh->dolor_items[0]['region']);

        $this->assertIsArray($fresh->lesiones);
        $this->assertSame('front', $fresh->lesiones[0]['view']);

        // fechas
        $this->assertInstanceOf(Carbon::class, $fresh->evento_fecha_hora);
        $this->assertInstanceOf(Carbon::class, $fresh->obst_fum);
        $this->assertInstanceOf(Carbon::class, $fresh->alta_fecha_control);

        // decimales (casts decimal:* retornan string formateado)
        $this->assertSame('36.5', (string) $fresh->temp_bucal);
        $this->assertSame('77.10', (string) $fresh->peso);
        $this->assertSame('2.5', (string) $fresh->tiempo_llenado_capilar);
    }
}
