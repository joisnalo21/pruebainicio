<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class HomePageTest extends DuskTestCase
{
    /**
     * Verifica que "/" redirige a /login y que la página de login carga.
     */
    public function testHomePageLoads(): void
    {
        $this->browse(function (Browser $browser) {
            // Sin sesión limpia, si un test previo dejó sesión activa "/"
            // redirige al dashboard en lugar de /login y la prueba se rompe.
            $this->limpiarSesion($browser);

            $browser->visit('/')
                ->assertPathIs('/login')
                // El texto VISIBLE del body es "Iniciar sesión" (s minúscula).
                // "Iniciar Sesión" (S mayúscula) solo está en el <title>, que
                // no cuenta como texto del body para assertSee y por eso fallaba:
                //   Did not see expected text [Iniciar Sesión] within element [body].
                ->assertSee('Iniciar sesión')
                ->assertSee('Usuario');
        });
    }
}