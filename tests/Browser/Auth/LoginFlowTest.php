<?php

namespace Tests\Browser\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginFlowTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_admin_can_login_and_see_dashboard(): void
    {
        $admin = User::factory()->admin()->create([
            'username' => 'adminuser',
            'password' => 'password',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->visit('/login')
                ->type('username', $admin->username)
                ->type('password', 'password')
                ->press('Iniciar sesión')
                ->waitForLocation('/admin/dashboard')
                ->assertPathIs('/admin/dashboard')
                ->assertSee('Panel Administrador');
        });
    }

    public function test_medico_can_login_and_see_dashboard(): void
    {
        $medico = User::factory()->medico()->create([
            'username' => 'medicouser',
            'password' => 'password',
        ]);

        $this->browse(function (Browser $browser) use ($medico) {
            $browser->visit('/login')
                ->type('username', $medico->username)
                ->type('password', 'password')
                ->press('Iniciar sesión')
                ->waitForLocation('/medico/dashboard')
                ->assertPathIs('/medico/dashboard');
        });
    }
}
