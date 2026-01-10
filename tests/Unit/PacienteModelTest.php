<?php

namespace Tests\Unit;

use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PacienteModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_calcula_edad_automaticamente_al_setear_fecha_nacimiento(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 10, 12, 0, 0));

        $p = Paciente::create([
            'cedula' => '1300000001',
            'primer_nombre' => 'Juan',
            'segundo_nombre' => 'Carlos',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'Gómez',
            'fecha_nacimiento' => '2010-01-10',
            'direccion' => 'Av. Principal',
            'sexo' => 'M',
            'provincia' => '13',
            'canton' => '01',
            'parroquia' => '01',
            'telefono' => '0999999999',
            'ocupacion' => 'Estudiante',
            'nacionalidad' => 'Ecuador',
        ]);

        $this->assertSame(16, $p->edad);
    }

    public function test_nombre_completo_accessor(): void
    {
        $p = new Paciente([
            'primer_nombre' => 'Juan',
            'segundo_nombre' => 'Carlos',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'Gómez',
        ]);

        $this->assertSame('Juan Carlos Pérez Gómez', $p->nombre_completo);
    }
}
