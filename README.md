HCaptcha bundle for Symfony 4+
============

Basically, this bundle brings into your Symfony website a new Form type, namely
HCaptchaType, that is used to display and validate a CAPTCHA served by
https://www.hcaptcha.com.

This bundle is still in development, so you have to install it by hand. When it has
proper documentation and tests, I'll publish it.

Meanwhile, if you want to alpha-test it:

Step 1
------

Add the repository to `composer.json`:

```
    "repositories": [
        {
              "type": "vcs",
              "url": "https://github.com/Meteo-Concept/hcaptcha-bundle.git"
        }
    ]
```

and the package in the `require` section:

```
  "require": {
      ...
      "meteo-concept/hcaptcha-bundle": "dev-master"
  }
```

Step 2
------

Run `composer update meteo-concept/hcaptcha-bundle`.

Step 3
------

Configure the bundle, for instance in `config/packages/meteo_concept_hcaptcha.yml`:

```
parameters:
    hcaptcha_site_key: '%env(resolve:HCAPTCHA_SITE_KEY)%'
    hcaptcha_secret: '%env(resolve:HCAPTCHA_SECRET)%'

meteo_concept_h_captcha:
  hcaptcha:
    site_key: '%hcaptcha_site_key%'
    secret: '%hcaptcha_secret%'
```

with the corresponding change in `.env`:

```
HCAPTCHA_SITE_KEY="10000000-ffff-ffff-ffff-000000000001"
HCAPTACHA_SECRET="0x0000000000000000000000000000000000000000"
```

The site key and secret are the value hCaptcha gives you at https://dashboard.hcaptcha.com. The global configuration makes all captchas use the same site key by default but it's possible to change it in the definition of each form.
The values shown here are dummy values usable for integration testing (https://docs.hcaptcha.com/#integrationtest). Put the real values in `.env.local` (at least, the secret, the site key is public).

Step 4
------

Use the captcha in your forms:

```
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

By default, the HCaptchaFormType class validates ths input againt constraints `NotBlank` and `IsValidCaptcha` (a new constraint installed with this bundle whose validator makes the CAPTCHA check by calling hCaptcha API). You can override this set of constraints by passing the `constraints` option to the form builder. Also, HCaptchaFormType fields are passed `'mapped' => false` by default since it doesn't make mush sense to persist CAPTCHA values.
