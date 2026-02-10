<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class MiNuevaPruebaTest extends DuskTestCase
{
    public function testPaginaPrincipalCarga(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertPathIs('/login')
                ->assertSee('Iniciar sesión');
        });

    }
}
