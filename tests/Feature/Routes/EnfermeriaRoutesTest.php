<?php

namespace Tests\Feature\Routes;

use App\Services\Formulario008PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class EnfermeriaRoutesTest extends TestCase
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
    public function test_enfermeria_routes_are_accessible(): void
    {
        $enfermero = $this->createUser('enfermero');
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico);

        $this->actingAs($enfermero)->get('/enfermeria/dashboard')->assertOk();
        $this->actingAs($enfermero)->get('/enfermeria/formularios')->assertOk();
        $this->actingAs($enfermero)->get("/enfermeria/formularios/{$formulario->id}/resumen")->assertOk();
        $this->actingAs($enfermero)->get("/enfermeria/formularios/{$formulario->id}/ver/paso/1")->assertOk();
    }

    public function test_enfermeria_pacientes_routes_are_accessible(): void
    {
        $enfermero = $this->createUser('enfermero');
        $paciente = $this->createPaciente();

        $this->actingAs($enfermero)->get('/enfermeria/pacientes')->assertOk();
        $this->actingAs($enfermero)->get('/enfermeria/pacientes/create')->assertOk();
        $this->actingAs($enfermero)->get("/enfermeria/pacientes/{$paciente->id}/edit")->assertOk();
        $this->actingAs($enfermero)->get('/enfermeria/pacientes/validar-cedula/1234567890')->assertOk();
    }

    public function test_enfermeria_paciente_store_route(): void
    {
        $enfermero = $this->createUser('enfermero');
        $payload = $this->pacientePayload();

        $this->actingAs($enfermero)
            ->post('/enfermeria/pacientes', $payload)
            ->assertRedirect('/enfermeria/pacientes');
    }

    public function test_enfermeria_pdf_route_returns_pdf_for_completed_form(): void
    {
        $enfermero = $this->createUser('enfermero');
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

        $this->actingAs($enfermero)
            ->get("/enfermeria/formularios/{$formulario->id}/pdf")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
