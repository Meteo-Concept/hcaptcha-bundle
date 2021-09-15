HCaptcha bundle for Symfony 3+ [![Build Status](https://api.travis-ci.com/Meteo-Concept/hcaptcha-bundle.svg?branch=master)](https://travis-ci.com/Meteo-Concept/hcaptcha-bundle)
============

This bundle brings into your Symfony website a new Form type, namely
HCaptchaType, that is used to display and validate a CAPTCHA served by
https://www.hcaptcha.com.

This bundle is tested for Symfony major versions 3, 4 and 5. It works
with PHP 7.1 but if you want to run the tests the dev dependencies
require PHP 7.2+.

Installation
----------


### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
$ composer require meteo-concept/hcaptcha-bundle
```

In order to avoid making you install another HTTP client if you already have a
compatible one, this bundle depends on *virtual* packages, namely PSR-18
psr/http-client-interface and PSR-17 psr/http-factory-interface. If you don't
have any real package already installed in your application providing an
implementation for these, composer will complain that the bundle is not
installable. In this case, you have to provide a real implementation at the
same time as the bundle.

For instance, for Symfony 4 and 5:

```console
$ composer require meteo-concept/hcaptcha-bundle symfony/http-client nyholm/psr7
```

For Symfony 3:

```console
$ composer require meteo-concept/hcaptcha-bundle guzzlehttp/guzzle nyholm/psr7
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Install the bundle with one of the commands above. You now have to enable
it and configure it without the recipe.

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    MeteoConcept\HCaptchaBundle\MeteoConceptHCaptchaBundle::class => ['all' => true],
];
```

Configuration
------

This captcha is provided with a  Symfony flex contrib recipe so it should come
with a configuration if you have those enabled. Otherwise, you can copy the
configuration from the contrib repository:
https://github.com/symfony/recipes-contrib/tree/master/meteo-concept/hcaptcha-bundle.

Configure the bundle, for instance in `config/packages/meteo_concept_hcaptcha.yml`:

```yaml
parameters:
    hcaptcha_site_key: '%env(resolve:HCAPTCHA_SITE_KEY)%'
    hcaptcha_secret: '%env(resolve:HCAPTCHA_SECRET)%'

meteo_concept_h_captcha:
  hcaptcha:
    site_key: '%hcaptcha_site_key%'
    secret: '%hcaptcha_secret%'
  validation: 'strict' # this is the default
```

with the corresponding change in `.env`:

```ini
HCAPTCHA_SITE_KEY="10000000-ffff-ffff-ffff-000000000001"
HCAPTCHA_SECRET="0x0000000000000000000000000000000000000000"
```

The site key and secret are the values hCaptcha gives you at
https://dashboard.hcaptcha.com. The global configuration makes all captchas use
the same site key by default but it's possible to change it in the definition of
each form.

The values shown here are dummy values usable for integration testing
(https://docs.hcaptcha.com/#integrationtest). Put the real values in
`.env.local` (at least, the secret, the site key is public).

The validation can be set to 'strict' or 'lax'. If it's 'lax', then the CAPTCHA
will be considered valid even if the hCaptcha endpoint times out or return a
HTTP 500 error for instance (so as to not frustrate the users too much). If it's
strict (the default), then the CAPTCHA will not be considered valid unless the
endpoint returns a "success: true" answer.

Configure Twig to load the specific template for the hCaptcha widget (or provide
your own).

```yaml
twig:
    ...
    form_themes:
        - '@MeteoConceptHCaptcha/hcaptcha_form.html.twig'
        - ...
```

Usage
------

Use the captcha in your forms:

```php
<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use MeteoConcept\HCaptchaBundle\Form\HCaptchaType;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
            ])
            ->add('message', TextareaType::class, [
                'label' => 'How can we help you ?',
            ])
            ->add('captcha', HCaptchaType::class, [
                'label' => 'Anti-bot test',
                // optionally: use a different site key than the default one:
                'hcaptcha_site_key' => '10000000-ffff-ffff-ffff-000000000001',
            ])
        ;
    }
}
```

By default, the HCaptchaFormType class validates the field against constraints `NotBlank` and `IsValidCaptcha` (a new constraint installed with this bundle whose validator makes the CAPTCHA check by calling the hCaptcha API). You can override this set of constraints by passing the `constraints` option to the form builder. Also, HCaptchaFormType fields are passed `'mapped' => false` by default since it doesn't make much sense to persist CAPTCHA values.


Updates and breaking changes
----------------------------

- In major version 2, support for PHP7.1 has been dropped and support for PHP8.0
added.
