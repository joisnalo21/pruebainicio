<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class HomePageTest extends DuskTestCase
{
    /**
     * Test to ensure the home page loads properly.
     */
    public function testHomePageLoads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertPathIs('/login')
                    ->assertSee('Iniciar Sesión');
        });
    }
}
