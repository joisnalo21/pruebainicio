<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    public function test_redirects_to_login_or_loads_login(): void
    {
        $this->browse(function (Browser $browser) {
            // Si / redirige a /login, esto lo soporta.
            $browser->visit('/')->pause(500)->assertPathIs('/login');

        });
    }
}
