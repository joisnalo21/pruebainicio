<?php

namespace Tests\Unit;

use App\Models\Paciente;
use App\Services\Formulario008PdfService;
use setasign\Fpdi\Fpdi;
use Tests\TestCase;

class Formulario008PdfServiceHelpersTest extends TestCase
{
    private function callPrivate(object $obj, string $method, array $args = [])
    {
        $ref = new \ReflectionClass($obj);
        $m = $ref->getMethod($method);
        $m->setAccessible(true);
        return $m->invokeArgs($obj, $args);
    }

    public function test_ubicaciones_ecuador_se_carga_desde_public_provincias_json(): void
    {
        $service = new Formulario008PdfService();

        $map = $this->callPrivate($service, 'ubicacionesEcuador');

        $this->assertIsArray($map);
        $this->assertNotEmpty($map, 'public/provincias.json debería existir y tener datos.');

        // Debe tener estructura mínima
        $firstKey = array_key_first($map);
        $this->assertIsArray($map[$firstKey]);
        $this->assertArrayHasKey('provincia', $map[$firstKey]);
        $this->assertArrayHasKey('cantones', $map[$firstKey]);
    }

    public function test_resolver_ubicacion_paciente_devuelve_nombres_segun_provincias_json(): void
    {
        $service = new Formulario008PdfService();
        $map = $this->callPrivate($service, 'ubicacionesEcuador');

        // Elegimos códigos reales desde el JSON para que el test no sea frágil
        $provCode = (string) array_key_first($map);
        $prov = $map[$provCode];
        $cantCode = (string) array_key_first($prov['cantones']);
        $cant = $prov['cantones'][$cantCode];
        $parrCode = (string) array_key_first($cant['parroquias']);

        $p = new Paciente([
            'provincia' => $provCode,
            'canton' => $cantCode,
            'parroquia' => $parrCode,
            'barrio' => 'Mi barrio',
        ]);

        $resolved = $this->callPrivate($service, 'resolverUbicacionPaciente', [$p]);

        $this->assertSame($prov['provincia'], $resolved['provincia']);
        $this->assertSame($cant['canton'], $resolved['canton']);
        $this->assertSame($cant['parroquias'][$parrCode], $resolved['parroquia']);
        $this->assertSame('Mi barrio', $resolved['barrio']);
    }

    public function test_resolver_ubicacion_paciente_con_null_devuelve_vacios(): void
    {
        $service = new Formulario008PdfService();

        $resolved = $this->callPrivate($service, 'resolverUbicacionPaciente', [null]);

        $this->assertSame([
            'provincia' => '',
            'canton' => '',
            'parroquia' => '',
            'barrio' => '',
        ], $resolved);
    }

    public function test_wrap_text_to_width_no_excede_el_ancho_y_normaliza_espacios(): void
    {
        $service = new Formulario008PdfService();

        $pdf = new Fpdi('P', 'mm');
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 8);

        $maxW = 30.0;
        $text = '  Esto   es   una   prueba   con   espacios   repetidos   para   envolver  ';

        $lines = $this->callPrivate($service, 'wrapTextToWidth', [$pdf, $text, $maxW]);

        $this->assertIsArray($lines);
        $this->assertNotEmpty($lines);

        foreach ($lines as $line) {
            $this->assertLessThanOrEqual($maxW + 0.001, $pdf->GetStringWidth($line));
            $this->assertStringNotContainsString('  ', $line, 'Cada línea debería venir con espacios normalizados.');
        }
    }

    public function test_wrap_text_to_width_con_texto_vacio_devuelve_array_vacio(): void
    {
        $service = new Formulario008PdfService();

        $pdf = new Fpdi('P', 'mm');
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 8);

        $lines = $this->callPrivate($service, 'wrapTextToWidth', [$pdf, '   ', 30.0]);

        $this->assertSame([], $lines);
    }

    public function test_wrap_text_to_width_con_palabra_muy_larga_no_se_rompe(): void
    {
        $service = new Formulario008PdfService();

        $pdf = new Fpdi('P', 'mm');
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 8);

        $maxW = 10.0;
        $longWord = 'SUPERCALIFRAGILISTICOESPIALIDOSO';

        $lines = $this->callPrivate($service, 'wrapTextToWidth', [$pdf, $longWord, $maxW]);

        // Por diseño del helper: si la palabra no cabe y current está vacío, devuelve la palabra como línea.
        $this->assertSame([$longWord], $lines);
    }
}
