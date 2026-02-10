<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class AdminPacienteControllerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_index_filters_by_buscar(): void
    {
        $admin = $this->createUser('admin');

        $target = $this->createPaciente([
            'cedula' => '1234567890',
            'primer_nombre' => 'Ana',
            'apellido_paterno' => 'Lopez',
        ]);
        $this->createPaciente([
            'cedula' => '9999999999',
            'primer_nombre' => 'Pedro',
            'apellido_paterno' => 'Perez',
        ]);

        $response = $this->actingAs($admin)->get('/admin/pacientes?buscar=Ana');

        $response->assertOk();
        $response->assertViewHas('pacientes', function ($pacientes) use ($target) {
            $ids = $pacientes->getCollection()->pluck('id')->all();
            return in_array($target->id, $ids, true);
        });
    }

    public function test_show_displays_paciente(): void
    {
        $admin = $this->createUser('admin');
        $paciente = $this->createPaciente([
            'cedula' => '1234567890',
            'primer_nombre' => 'Ana',
        ]);

        $response = $this->actingAs($admin)->get("/admin/pacientes/{$paciente->id}");

        $response->assertOk();
        $response->assertViewHas('paciente', function ($viewPaciente) use ($paciente) {
            return $viewPaciente->id === $paciente->id;
        });
    }
}
