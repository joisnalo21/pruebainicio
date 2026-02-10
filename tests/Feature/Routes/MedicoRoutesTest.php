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
