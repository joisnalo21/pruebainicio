<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
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
    ]);

    $seleniumUrl = env('DUSK_DRIVER_URL', 'http://selenium:4444/wd/hub');

    return RemoteWebDriver::create(
        $seleniumUrl,
        DesiredCapabilities::chrome()->setCapability(
            ChromeOptions::CAPABILITY,
            $options
        ),
        60000, // connection timeout
        60000  // request timeout
    );
}

}
