<?php

namespace Tests\Support;

use App\Models\Formulario008;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Support\Str;

trait CreatesTestData
{
    protected function createUser(string $role = 'medico', array $overrides = []): User
    {
        $factory = User::factory();

        $factory = match ($role) {
            'admin' => $factory->admin(),
            'enfermero' => $factory->enfermero(),
            default => $factory->medico(),
        };

        return $factory->create($overrides);
    }

    protected function createPaciente(array $overrides = []): Paciente
    {
        $defaults = [
            // Cedula: 10 dígitos (para tests no necesitamos que sea "real" salvo que el test lo pida)
            'cedula' => (string) random_int(1000000000, 1999999999),
            'primer_nombre' => 'Juan',
            'segundo_nombre' => 'Carlos',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'Gómez',
            'fecha_nacimiento' => '2000-01-01',
            'direccion' => 'Av. Principal',
            'sexo' => 'M',
            // En tu app se guardan códigos; para tests da igual
            'provincia' => '13',
            'canton' => '01',
            'parroquia' => '01',
            'telefono' => '0999999999',
            'ocupacion' => 'Estudiante',
            // opcionales
            'nacionalidad' => 'Ecuador',
        ];

        return Paciente::create(array_merge($defaults, $overrides));
    }

    protected function createFormulario(User $medico, ?Paciente $paciente = null, array $overrides = []): Formulario008
    {
        $paciente ??= $this->createPaciente();

        $defaults = [
            'paciente_id' => $paciente->id,
            'created_by' => $medico->id,
            'estado' => 'borrador',
            'paso_actual' => 1,
        ];

        return Formulario008::create(array_merge($defaults, $overrides));
    }
}
