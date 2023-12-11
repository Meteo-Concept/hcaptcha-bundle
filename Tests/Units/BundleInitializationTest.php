<?php

namespace MeteoConcept\HCaptchaBundle\Tests\Units;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Nyholm\BundleTest\TestKernel;

use MeteoConcept\HCaptchaBundle\MeteoConceptHCaptchaBundle;
use MeteoConcept\HCaptchaBundle\Form\HCaptchaType;
use MeteoConcept\HCaptchaBundle\Form\DataTransformer\HCaptchaValueFetcher;
use MeteoConcept\HCaptchaBundle\Service\HCaptchaVerifier;
use MeteoConcept\HCaptchaBundle\Validator\Constraints\IsValidCaptchaValidator;

class BundleInitializationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        /**
         * @var TestKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(MeteoConceptHCaptchaBundle::class);
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function setUp(): void
    {
        $kernel = self::bootKernel(['config' => static function(TestKernel $kernel){
            $kernel->addTestConfig(__DIR__.'/test_config.yml');
            if (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION >= 6 &&
                \Symfony\Component\HttpKernel\Kernel::MINOR_VERSION >= 4) {
                $kernel->addTestConfig(__DIR__.'/test_framework_config_sf6.yml');
            } else if (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION >= 5) {
                $kernel->addTestConfig(__DIR__.'/test_framework_config_sf5.yml');
            } else if (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION >= 4) {
                $kernel->addTestConfig(__DIR__.'/test_framework_config_sf4.yml');
            }
        }]);
    }

    public function test_the_container_is_buildable()
    {
        $container = $this->getContainer();
        $this->assertTrue(null !== $container);
    }

    public function test_the_bundle_has_the_captcha_verifier_service()
    {
        $container = $this->getContainer();
        $this->assertTrue($container->has('meteo_concept_h_captcha.captcha_verifier'));
        $service = $container->get('meteo_concept_h_captcha.captcha_verifier');
        $this->assertInstanceOf(HCaptchaVerifier::class, $service);
    }

    public function test_the_bundle_has_the_captcha_validation_service()
    {
        $container = $this->getContainer();
        $this->assertTrue($container->has('meteo_concept_h_captcha.captcha_validator'));
        $service = $container->get('meteo_concept_h_captcha.captcha_validator');
        $this->assertInstanceOf(IsValidCaptchaValidator::class, $service);
    }

    public function test_the_bundle_has_the_hCaptcha_form_type_service()
    {
        $container = $this->getContainer();
        $this->assertTrue($container->has('meteo_concept_h_captcha.hcaptcha_form_type'));
        $service = $container->get('meteo_concept_h_captcha.hcaptcha_form_type');
        $this->assertInstanceOf(HCaptchaType::class, $service);
    }

    public function test_the_bundle_has_the_hCaptcha_form_data_transformer_service()
    {
        $container = $this->getContainer();
        $this->assertTrue($container->has('meteo_concept_h_captcha.hcaptcha_value_fetcher'));
        $service = $container->get('meteo_concept_h_captcha.hcaptcha_value_fetcher');
        $this->assertInstanceOf(HCaptchaValueFetcher::class, $service);
    }
}
