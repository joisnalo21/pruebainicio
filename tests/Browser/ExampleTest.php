<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    public function test_login_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->assertPathIs('/login')
                    ->assertSee('username'); // o 'Correo' según tu idioma
        });
    }
}
