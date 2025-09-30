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
            ->waitFor('ul.flex li a', 10) // espera hasta 10 segundos
            ->assertSeeIn('ul.flex li a', 'Deploy now');
});

    }
}
