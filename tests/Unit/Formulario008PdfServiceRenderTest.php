<?php

namespace Tests\Unit;

use App\Services\Formulario008PdfService;
use Tests\TestCase;

class Formulario008PdfServiceRenderTest extends TestCase
{
    public function test_render_with_grid_generates_pdf_bytes(): void
    {
        $service = new Formulario008PdfService();
        $form = new \App\Models\Formulario008();

        $bytes = $service->render($form, true);

        $this->assertIsString($bytes);
        $this->assertSame('%PDF', substr($bytes, 0, 4));
    }

    public function test_render_without_grid_generates_pdf_bytes(): void
    {
        $service = new Formulario008PdfService();
        $paciente = new \App\Models\Paciente([
            'cedula' => '0102030405',
            'primer_nombre' => 'Juan',
            'segundo_nombre' => 'Carlos',
            'apellido_paterno' => 'Perez',
            'apellido_materno' => 'Lopez',
            'direccion' => 'Av. Principal',
            'provincia' => '13',
            'canton' => '01',
            'parroquia' => '01',
            'barrio' => 'Centro',
            'sexo' => 'M',
            'edad' => 30,
        ]);

        $form = new \App\Models\Formulario008([
            'estado' => 'borrador',
        ]);
        $form->setRelation('paciente', $paciente);

        $bytes = $service->render($form, false);

        $this->assertIsString($bytes);
        $this->assertSame('%PDF', substr($bytes, 0, 4));
    }
}
