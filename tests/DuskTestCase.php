<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        // No usamos Sail, ejecutamos con Selenium en Docker
    }

    /**
     * Create the RemoteWebDriver instance.
     */
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

    return RemoteWebDriver::create(
        'http://172.17.0.1:4444', // URL de Selenium Grid en Docker
        DesiredCapabilities::chrome()->setCapability(
            ChromeOptions::CAPABILITY, $options
        )
    );
}

}
