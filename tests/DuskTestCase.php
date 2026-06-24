<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    #[BeforeClass]
    public static function prepare(): void
    {
        // Selenium corre en Docker (devops-net)
    }

    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless=new',
            '--window-size=1920,1080',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--remote-allow-origins=*',
        ]);

        // usa env, fallback a DNS docker
        $seleniumUrl = env('DUSK_DRIVER_URL') ?: 'http://selenium:4444';

        return RemoteWebDriver::create(
            $seleniumUrl,
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }

    /**
     * Arranca el navegador SIN sesión previa.
     *
     * Dusk reutiliza la misma instancia de navegador (y sus cookies) entre
     * tests de una misma clase. Si un test anterior dejó una sesión activa,
     * al hacer visit('/login') el middleware 'guest' redirige al dashboard
     * y el formulario deja de existir, provocando el error:
     *   NoSuchElementException: Unable to locate element {"selector":"body username"}
     *
     * Llamar a este helper al inicio de cada test garantiza una sesión limpia.
     * Primero cargamos el dominio de la app (necesario para que el driver
     * pueda manipular cookies) y luego las borramos.
     */
    protected function limpiarSesion(Browser $browser): Browser
    {
        $browser->visit('/login');
        $browser->driver->manage()->deleteAllCookies();

        return $browser;
    }
}