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
                    ->assertSee("Let's get started")
                    ->assertSee("Deploy now"); // Asegura que ambos textos estén visibles
        });
    }
}
