<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * En este proyecto no existe ruta "/" (o puede redirigir),
     * así que usamos una pantalla real del sistema.
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')->assertStatus(200);
    }
}
