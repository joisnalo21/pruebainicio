<?php

namespace Tests\Unit;

use App\Http\Controllers\MedicoPacienteController;
use ReflectionClass;
use Tests\TestCase;

class CedulaValidationTest extends TestCase
{
    private function calcularDigitoVerificador(string $base9): int
    {
        $coef = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $d = (int) $base9[$i] * $coef[$i];
            if ($d >= 10) $d -= 9;
            $sum += $d;
        }
        $mod = $sum % 10;
        return $mod === 0 ? 0 : (10 - $mod);
    }

    private function generarCedulaValida(string $provincia = '13'): string
    {
        $provincia = str_pad($provincia, 2, '0', STR_PAD_LEFT);
        // 7 dígitos adicionales para completar 9 (2+7)
        $resto = str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
        $base9 = $provincia . $resto;

        $dv = $this->calcularDigitoVerificador($base9);

        return $base9 . $dv;
    }

    public function test_validacion_de_cedula_ecuatoriana(): void
    {
        $controller = new MedicoPacienteController();

        $ref = new ReflectionClass($controller);
        $method = $ref->getMethod('cedulaEcuatorianaValida');
        $method->setAccessible(true);

        $cedulaValida = $this->generarCedulaValida('13');
        $this->assertTrue($method->invoke($controller, $cedulaValida));

        // Mismo número pero con dígito verificador alterado
        $cedulaInvalida = substr($cedulaValida, 0, 9) . ((int) $cedulaValida[9] + 1) % 10;
        $this->assertFalse($method->invoke($controller, $cedulaInvalida));

        // Provincia inválida
        $cedulaProvinciaInvalida = $this->generarCedulaValida('99');
        $this->assertFalse($method->invoke($controller, $cedulaProvinciaInvalida));
    }
}
