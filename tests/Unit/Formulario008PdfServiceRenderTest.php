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
}
