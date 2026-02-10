<?php

namespace Tests\Feature\Medico;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class MedicoPacienteControllerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

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
        $resto = str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
        $base9 = $provincia . $resto;
        $dv = $this->calcularDigitoVerificador($base9);
        return $base9 . $dv;
    }

    private function pacientePayload(array $overrides = []): array
    {
        return array_merge([
            'cedula' => $this->generarCedulaValida('13'),
            'primer_nombre' => 'Ana',
            'segundo_nombre' => 'Maria',
            'apellido_paterno' => 'Lopez',
            'apellido_materno' => 'Perez',
            'fecha_nacimiento' => '2000-01-01',
            'edad' => 25,
            'direccion' => 'Av. Principal',
            'sexo' => 'F',
            'provincia' => '13',
            'canton' => '01',
            'parroquia' => '01',
            'telefono' => '0999999999',
            'ocupacion' => 'Estudiante',
            'zona' => 'Urbana',
            'barrio' => 'Centro',
            'lugar_nacimiento' => 'Jipijapa',
            'nacionalidad' => 'Ecuador',
            'grupo_cultural' => 'Mestizo',
            'estado_civil' => 'Soltero',
            'instruccion' => 'Secundaria',
            'empresa' => 'N/A',
            'seguro_salud' => 'Ninguno',
        ], $overrides);
    }

    public function test_index_filters_by_buscar(): void
    {
        $medico = $this->createUser('medico');

        $target = $this->createPaciente([
            'cedula' => '1234567890',
            'primer_nombre' => 'Ana',
            'apellido_paterno' => 'Lopez',
        ]);
        $this->createPaciente([
            'cedula' => '9999999999',
            'primer_nombre' => 'Pedro',
            'apellido_paterno' => 'Gomez',
        ]);

        $response = $this->actingAs($medico)->get('/medico/pacientes?buscar=Ana');

        $response->assertOk();
        $response->assertViewHas('pacientes', function ($pacientes) use ($target) {
            $ids = $pacientes->getCollection()->pluck('id')->all();
            return in_array($target->id, $ids, true) && count($ids) === 1;
        });
    }

    public function test_store_creates_paciente_and_sets_age(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-01-01 10:00:00'));

        $medico = $this->createUser('medico');
        $payload = $this->pacientePayload();

        $response = $this->actingAs($medico)->post('/medico/pacientes', $payload);

        $response->assertRedirect('/medico/pacientes');
        $this->assertDatabaseHas('pacientes', [
            'cedula' => $payload['cedula'],
            'primer_nombre' => 'Ana',
            'edad' => 25,
        ]);

        Carbon::setTestNow();
    }

    public function test_store_rejects_invalid_cedula(): void
    {
        $medico = $this->createUser('medico');
        $valid = $this->generarCedulaValida('13');
        $invalid = substr($valid, 0, 9) . (((int) $valid[9] + 1) % 10);

        $payload = $this->pacientePayload([
            'cedula' => $invalid,
        ]);

        $response = $this->actingAs($medico)->post('/medico/pacientes', $payload);

        $response->assertSessionHasErrors('cedula');
        $this->assertDatabaseMissing('pacientes', ['cedula' => $invalid]);
    }

    public function test_update_updates_paciente(): void
    {
        $medico = $this->createUser('medico');
        $paciente = $this->createPaciente([
            'cedula' => $this->generarCedulaValida('13'),
        ]);

        $payload = $this->pacientePayload([
            'cedula' => $paciente->cedula,
            'primer_nombre' => 'Beatriz',
        ]);

        $response = $this->actingAs($medico)->put("/medico/pacientes/{$paciente->id}", $payload);

        $response->assertRedirect('/medico/pacientes');
        $this->assertDatabaseHas('pacientes', [
            'id' => $paciente->id,
            'primer_nombre' => 'Beatriz',
        ]);
    }

    public function test_destroy_deletes_paciente(): void
    {
        $medico = $this->createUser('medico');
        $paciente = $this->createPaciente();

        $response = $this->actingAs($medico)->delete("/medico/pacientes/{$paciente->id}");

        $response->assertRedirect('/medico/pacientes');
        $this->assertDatabaseMissing('pacientes', ['id' => $paciente->id]);
    }

    public function test_validar_cedula_endpoint_returns_status(): void
    {
        $medico = $this->createUser('medico');
        $cedula = $this->generarCedulaValida('13');

        $validResponse = $this->actingAs($medico)->get("/medico/pacientes/validar-cedula/{$cedula}");
        $validResponse->assertOk();
        $validResponse->assertJson(['valido' => true]);

        $invalidResponse = $this->actingAs($medico)->get('/medico/pacientes/validar-cedula/123');
        $invalidResponse->assertOk();
        $invalidResponse->assertJson(['valido' => false]);
    }
}
