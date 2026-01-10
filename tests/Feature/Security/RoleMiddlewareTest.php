<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_guest_no_puede_acceder_a_rutas_protegidas(): void
    {
        $this->get('/medico/dashboard')->assertRedirect('/login');
    }

    public function test_admin_no_puede_acceder_a_rutas_de_medico(): void
    {
        $admin = $this->createUser('admin');

        $this->actingAs($admin)
            ->get('/medico/dashboard')
            ->assertStatus(403);
    }

    public function test_medico_no_puede_acceder_a_rutas_de_admin(): void
    {
        $medico = $this->createUser('medico');

        $this->actingAs($medico)
            ->get('/admin/dashboard')
            ->assertStatus(403);
    }

    public function test_medico_si_puede_acceder_a_su_dashboard(): void
    {
        $medico = $this->createUser('medico');

        $this->actingAs($medico)
            ->get('/medico/dashboard')
            ->assertOk();
    }
}
