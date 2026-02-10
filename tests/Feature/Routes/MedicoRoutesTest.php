<?php

namespace Tests\Feature\Routes;

use App\Services\Formulario008PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class MedicoRoutesTest extends TestCase
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

    public function test_medico_routes_are_accessible(): void
    {
        $medico = $this->createUser('medico');

        $this->actingAs($medico)->get('/medico/dashboard')->assertOk();
        $this->actingAs($medico)->get('/medico/formularios')->assertOk();
        $this->actingAs($medico)->get('/medico/formularios/nuevo')->assertOk();
        $this->actingAs($medico)->get('/medico/pacientes')->assertOk();
        $this->actingAs($medico)->get('/medico/reportes')->assertOk();
    }

    public function test_medico_formulario_archivar_desarchivar_and_ver_paso_routes(): void
    {
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico, null, ['estado' => 'borrador', 'paso_actual' => 2]);

        $this->actingAs($medico)
            ->patch("/medico/formularios/{$formulario->id}/archivar")
            ->assertSessionHas('success');

        $this->actingAs($medico)
            ->patch("/medico/formularios/{$formulario->id}/desarchivar")
            ->assertSessionHas('success');

        $formulario->estado = 'completo';
        $formulario->save();

        $this->actingAs($medico)
            ->get("/medico/formularios/{$formulario->id}/ver/paso/1")
            ->assertOk();
    }

    public function test_medico_paciente_edit_update_destroy_routes(): void
    {
        $medico = $this->createUser('medico');
        $paciente = $this->createPaciente();

        $this->actingAs($medico)
            ->get("/medico/pacientes/{$paciente->id}/editar")
            ->assertOk();

        $this->actingAs($medico)
            ->put("/medico/pacientes/{$paciente->id}", array_merge($paciente->toArray(), [
                'cedula' => $paciente->cedula,
                'primer_nombre' => 'Nuevo',
                'segundo_nombre' => 'Nombre',
                'apellido_paterno' => 'Apellido',
                'apellido_materno' => 'Materno',
                'fecha_nacimiento' => '2000-01-01',
                'direccion' => 'Direccion',
                'sexo' => 'M',
                'provincia' => '13',
                'canton' => '01',
                'parroquia' => '01',
                'telefono' => '0999999999',
                'ocupacion' => 'Ocupacion',
                'zona' => 'Urbana',
                'barrio' => 'Centro',
                'lugar_nacimiento' => 'Jipijapa',
                'nacionalidad' => 'Ecuador',
            ]))
            ->assertRedirect('/medico/pacientes');

        $this->actingAs($medico)
            ->delete("/medico/pacientes/{$paciente->id}")
            ->assertRedirect('/medico/pacientes');
    }

    public function test_medico_paciente_store_route(): void
    {
        $medico = $this->createUser('medico');
        $payload = $this->pacientePayload();

        $this->actingAs($medico)
            ->post('/medico/pacientes', $payload)
            ->assertRedirect('/medico/pacientes');
    }

    public function test_medico_pdf_route_returns_pdf_for_completed_form(): void
    {
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico, null, ['estado' => 'completo']);

        $this->mock(Formulario008PdfService::class, function ($mock) use ($formulario) {
            $mock->shouldReceive('render')
                ->once()
                ->withArgs(function ($bound, $grid) use ($formulario) {
                    return $bound->id === $formulario->id && $grid === false;
                })
                ->andReturn('PDFBYTES');
        });

        $this->actingAs($medico)
            ->get("/medico/formularios/{$formulario->id}/pdf")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
