<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class Formulario008RelationshipsTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_relaciones_paciente_y_creador_funcionan(): void
    {
        $medico = $this->createUser('medico');
        $paciente = $this->createPaciente();

        $form = $this->createFormulario($medico, $paciente);
        $form = $form->fresh();

        $this->assertNotNull($form->paciente);
        $this->assertSame($paciente->id, $form->paciente->id);

        $this->assertNotNull($form->creador);
        $this->assertSame($medico->id, $form->creador->id);
    }
}
