<?php

namespace Tests\Browser\Auth;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginFlowTest extends DuskTestCase
{
    // SIN DatabaseMigrations: corre contra la MySQL real con usuarios sembrados.
    // Debe coincidir con la contraseña de UsuariosSeeder.
    private const PASSWORD = 'Hospital2025*';

    public function test_admin_puede_iniciar_sesion(): void
    {
        $this->browse(function (Browser $browser) {
            $this->limpiarSesion($browser)
                ->visit('/login')
                ->waitFor('#username')
                ->type('username', 'admin')
                ->type('password', self::PASSWORD)
                ->press('Iniciar sesión')
                ->waitForLocation('/admin/dashboard')
                ->assertPathIs('/admin/dashboard')
                ->assertSee('Panel Administrador');
        });
    }

    public function test_medico_puede_iniciar_sesion(): void
    {
        $this->browse(function (Browser $browser) {
            $this->limpiarSesion($browser)
                ->visit('/login')
                ->waitFor('#username')
                ->type('username', 'drnavia')
                ->type('password', self::PASSWORD)
                ->press('Iniciar sesión')
                ->waitForLocation('/medico/dashboard')
                ->assertPathIs('/medico/dashboard');
        });
    }

    public function test_enfermero_puede_iniciar_sesion(): void
    {
        $this->browse(function (Browser $browser) {
            $this->limpiarSesion($browser)
                ->visit('/login')
                ->waitFor('#username')
                ->type('username', 'enfermera1')
                ->type('password', self::PASSWORD)
                ->press('Iniciar sesión')
                ->waitForLocation('/enfermeria/dashboard')
                ->assertPathIs('/enfermeria/dashboard');
        });
    }
}