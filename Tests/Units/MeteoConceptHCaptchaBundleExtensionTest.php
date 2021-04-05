<?php

namespace MeteoConcept\HCaptchaBundle\Tests\Units;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

use MeteoConcept\HCaptchaBundle\DependencyInjection\MeteoConceptHCaptchaExtension;

class MeteoConceptHCaptchaExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return array(
            new MeteoConceptHCaptchaExtension()
        );
    }

    protected function getMinimalConfiguration(): array
    {
        return [
            'hcaptcha' => [
                'site_key' => '10000000-ffff-ffff-ffff-000000000001',
                'secret'   => '0x0000000000000000000000000000000000000000',
            ],
            'validation' => 'lax',
        ];
    }

    public function test_the_captcha_verifier_service_definition_is_passed_the_hCaptcha_secret()
    {
        $this->load();
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('meteo_concept_h_captcha.captcha_verifier', 3, '0x0000000000000000000000000000000000000000');
    }

    public function test_the_hCaptcha_form_type_definition_is_passed_the_hCaptcha_site_key()
    {
        $this->load();
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('meteo_concept_h_captcha.hcaptcha_form_type', 1, '10000000-ffff-ffff-ffff-000000000001');
    }

    public function test_the_captcha_validator_definition_is_passed_the_validation_mode()
    {
        $this->load();
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('meteo_concept_h_captcha.captcha_validator', 1, 'lax');
    }
}
