<?php

namespace Tests\Feature\Routes;

use App\Services\Formulario008PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class AdminRoutesTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_admin_routes_are_accessible(): void
    {
        $admin = $this->createUser('admin');

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/admin/usuarios')->assertOk();
        $this->actingAs($admin)->get('/admin/usuarios/crear')->assertOk();
        $this->actingAs($admin)->get('/admin/formularios')->assertOk();
        $this->actingAs($admin)->get('/admin/pacientes')->assertOk();
        $this->actingAs($admin)->get('/admin/reportes')->assertOk();
    }

    public function test_admin_user_management_routes(): void
    {
        $admin = $this->createUser('admin');
        $target = $this->createUser('medico', ['username' => 'targetuser', 'email' => 'target@example.com']);

        $this->actingAs($admin)->get("/admin/usuarios/{$target->id}/editar")->assertOk();

        $this->actingAs($admin)->put("/admin/usuarios/{$target->id}", [
            'username' => 'targetuser',
            'name' => 'Target Updated',
            'email' => 'target@example.com',
            'role' => 'medico',
            'is_active' => true,
        ])->assertRedirect('/admin/usuarios');

        $this->actingAs($admin)->patch("/admin/usuarios/{$target->id}/toggle")->assertSessionHas('success');
        $this->actingAs($admin)->post("/admin/usuarios/{$target->id}/reset-password")->assertSessionHas('success');

        $this->actingAs($admin)->delete("/admin/usuarios/{$target->id}")->assertRedirect('/admin/usuarios');
    }

    public function test_admin_formulario_routes_are_accessible(): void
    {
        $admin = $this->createUser('admin');
        $medico = $this->createUser('medico');
        $formulario = $this->createFormulario($medico, null, ['estado' => 'borrador', 'paso_actual' => 2]);

        $this->actingAs($admin)
            ->get("/admin/formularios/{$formulario->id}")
            ->assertRedirect("/admin/formularios/{$formulario->id}/ver/paso/2");

        $this->actingAs($admin)
            ->get("/admin/formularios/{$formulario->id}/ver/paso/1")
            ->assertOk();

        $this->actingAs($admin)
            ->patch("/admin/formularios/{$formulario->id}/archivar")
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->patch("/admin/formularios/{$formulario->id}/desarchivar")
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->delete("/admin/formularios/{$formulario->id}")
            ->assertRedirect('/admin/formularios');
    }

    public function test_admin_formulario_pdf_route_returns_pdf(): void
    {
        $admin = $this->createUser('admin');
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

        $this->actingAs($admin)
            ->get("/admin/formularios/{$formulario->id}/pdf")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_admin_paciente_show_route_is_accessible(): void
    {
        $admin = $this->createUser('admin');
        $paciente = $this->createPaciente();

        $this->actingAs($admin)->get("/admin/pacientes/{$paciente->id}")->assertOk();
    }
}
