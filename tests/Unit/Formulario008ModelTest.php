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

    public function test_numero_y_pdf_filename_se_forman_con_datos_del_paciente(): void
    {
        $medico = $this->createUser('medico');
        $paciente = $this->createPaciente([
            'cedula' => '0102030405',
            'primer_nombre' => 'Juan',
            'segundo_nombre' => 'Carlos',
            'apellido_paterno' => 'Perez',
            'apellido_materno' => 'Lopez',
        ]);

        $form = $this->createFormulario($medico, $paciente, [
            'fecha_admision' => '2025-01-02',
        ]);

        $this->assertSame('008-000001', $form->numero);

        $filename = $form->pdfFilename();
        $this->assertStringStartsWith('MSP_Form008_008-000001_0102030405_', $filename);
        $this->assertStringContainsString('juan_carlos_perez_lopez', $filename);
        $this->assertStringEndsWith('_20250102.pdf', $filename);
    }
}
