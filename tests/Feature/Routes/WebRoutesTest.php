<?php

namespace Tests\Feature\Routes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class WebRoutesTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_root_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_dashboard_requires_auth(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_dashboard_redirects_by_role(): void
    {
        $admin = $this->createUser('admin');
        $medico = $this->createUser('medico');
        $enfermero = $this->createUser('enfermero');

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertRedirect('/admin/dashboard');

        $this->actingAs($medico)
            ->get('/dashboard')
            ->assertRedirect('/medico/dashboard');

        $this->actingAs($enfermero)
            ->get('/dashboard')
            ->assertRedirect('/enfermeria/dashboard');
    }

    public function test_medico_debug_db_endpoint_returns_json(): void
    {
        $medico = $this->createUser('medico');

        $response = $this->actingAs($medico)->get('/medico/debug/db');

        $response->assertOk();
        $response->assertJsonStructure([
            'database',
            'server',
            'has_no_aplica_apartado_3',
            'has_custodia_policial',
        ]);
        $this->assertNotNull($response->json('database'));
    }
}
