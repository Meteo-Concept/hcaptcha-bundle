<?php

namespace MeteoConcept\HCaptchaBundle\Tests\Functional;

use Symfony\Component\Panther\PantherTestCase;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class SubmitFormWebTest extends PantherTestCase
{
    public function testEverythingCompilesAndAppRuns()
    {
        $client = self::bootKernel();
        $this->assertNotNull(self::$container);
    }

    public function testFormIsBuiltAndDisplayed()
    {
        $client = self::createPantherClient([
            'webServerDir' => __DIR__.'/public/',
            'router' => 'index.php',
            'browser' => static::FIREFOX,
        ]);

        $crawler = $client->request('GET', '/form');
        $this->assertSelectorExists('script[src="https://hcaptcha.com/1/api.js"]');
        $client->waitFor('iframe[data-hcaptcha-widget-id]', 10); // failure after 10s
    }

    public function testSuccessfulCaptchaIsHandledCorrectly()
    {
        $client = self::createPantherClient([
            'webServerDir' => __DIR__.'/public/',
            'router' => 'index.php',
            'browser' => static::FIREFOX,
        ]);

        $crawler = $client->request('GET', '/form');

        // Trigger the hCaptcha
        $client->waitFor('iframe[data-hcaptcha-widget-id]');
        $frame = $client->findElement(WebDriverBy::cssSelector('iframe[data-hcaptcha-widget-id]'));
        $client->switchTo()->frame($frame);
        $client->waitFor('div#checkbox');
        $client->findElement(WebDriverBy::id('anchor'))->click();

        // Submit the form with the hCaptcha validated
        $client->switchTo()->defaultContent();
        $form = $crawler->selectButton('form_submit')->form();
        $client->submit($form, [
            'form[witness]' => 'test',
        ]);

        // Check that we have been redirected to the 'ok' webpage
        $this->assertSelectorWillContain('#status', 'ok');
        $this->assertSelectorWillContain('#witness', 'test');

        // sadly, HCaptcha doesn't yet provide a way to force a bad answer for
        // testing (except in the Enterprise tier) so we can't test for failures
    }
}

