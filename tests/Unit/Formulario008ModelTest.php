<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class Formulario008ModelTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_helpers_de_estado(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['estado' => 'borrador']);

        $this->assertTrue($form->esBorrador());
        $this->assertFalse($form->esCompleto());
        $this->assertFalse($form->esArchivado());

        $form->estado = 'completo';
        $this->assertTrue($form->esCompleto());

        $form->estado = 'archivado';
        $this->assertTrue($form->esArchivado());
    }
}
