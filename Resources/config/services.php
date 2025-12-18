<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->defaults()
        ->private()
        ->autowire(false)
        ->autoconfigure(false);

    $services->set('meteo_concept_h_captcha.captcha_verifier', \MeteoConcept\HCaptchaBundle\Service\HCaptchaVerifier::class)
        ->public()
        ->args([
            service(\Psr\Http\Client\ClientInterface::class),
            service(\Psr\Http\Message\RequestFactoryInterface::class),
            service(\Psr\Http\Message\StreamFactoryInterface::class),
            '',
            service('logger')->nullOnInvalid(),
        ]);

    $services->alias(\MeteoConcept\HCaptchaBundle\Service\HCaptchaVerifier::class, 'meteo_concept_h_captcha.captcha_verifier')
        ->private();

    $services->set('meteo_concept_h_captcha.captcha_validator', \MeteoConcept\HCaptchaBundle\Validator\Constraints\IsValidCaptchaValidator::class)
        ->public()
        ->args([
            service('meteo_concept_h_captcha.captcha_verifier'),
            '',
        ])
        ->tag('validator.constraint_validator');

    $services->alias(\MeteoConcept\HCaptchaBundle\Validator\Constraints\IsValidCaptchaValidator::class, 'meteo_concept_h_captcha.captcha_validator')
        ->private();

    $services->set('meteo_concept_h_captcha.hcaptcha_form_type', \MeteoConcept\HCaptchaBundle\Form\HCaptchaType::class)
        ->public()
        ->args([
            service('meteo_concept_h_captcha.hcaptcha_value_fetcher'),
            '',
        ])
        ->tag('form.type', ['alias' => 'hcaptcha']);

    $services->alias(\MeteoConcept\HCaptchaBundle\Form\HCaptchaType::class, 'meteo_concept_h_captcha.hcaptcha_form_type')
        ->private();

    $services->set('meteo_concept_h_captcha.hcaptcha_value_fetcher', \MeteoConcept\HCaptchaBundle\Form\DataTransformer\HCaptchaValueFetcher::class)
        ->public()
        ->args([service('request_stack')]);

    $services->alias(\MeteoConcept\HCaptchaBundle\Form\DataTransformer\HCaptchaValueFetcher::class, 'meteo_concept_h_captcha.hcaptcha_value_fetcher')
        ->private();
};
