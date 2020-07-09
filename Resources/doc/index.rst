The Météo Concept hCaptcha bundle
================================

The Météo Concept hCaptcha bundle aims at making it easy to use `hCaptcha`_ CAPTCHAs
in your Symfony forms, including the ones you don't
process directly yourself, like the ones provided by an external bundle.

It takes care of validating the CAPTCHA against the hCaptcha validation API and
put the appropriate constraint violation in your submitted forms if the CAPTCHA
is invalid.

This bundle uses the Symfony HttpClient component and as such requires Symfony 4.3
or later. It has been tested for Symfony 4.4 but not for Symfony 5.* yet.

Installation
------------

Run this command to install and enable this bundle in your application:

.. code-block:: terminal

    $ composer require meteo-concept/hcaptcha-bundle

Usage
-----

The main contribution of this bundle to the Symfony ecosystem is the addition
of a new FormType, namely HCaptchaFormType. You typically use it like this:

.. code-block:: php

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

By default, the HCaptchaFormType class puts constraints ``NotBlank`` (so that
it's illegal not to solve the CAPTCHA when submitting the form) and
``IsValidCaptcha`` on the form. The constraint ``IsValidCaptcha`` is a new
constraint that is part of this bundle. The validator of this constraint makes
the call to the hCaptcha validation endpoint.

Configuration
-------------

This bundle requires a minimal configuration: the values configured on you
`hCaptcha dashboard`_.

.. code-block:: yaml

    # config/packages/meteo_concept_h_bundle.yaml
    meteo_concept_h_bundle:
        hcaptcha:
            # This is the value that will be used in your forms
            # unless you set the hcaptcha_site_key option explicitly
            site_key: 10000000-ffff-ffff-ffff-000000000001
            # This is the value that will be used by the validator
            # to authenticate your requests to the hCaptcha /siteverify
            # API endpoint
            secret: '%env(resolve:HCAPTCHA_SECRET)%'

This bundle comes with a minimal Twig template for the hCaptcha widget.  It
only overwrites the widget itself not the entire field row. The
HCaptchaFormType declares TextareaType as its parent so the overall layout of
the form field (including the label and the help and error messages) will be
based on whichever theme is currently active for TextareaType fields. If you
wish to use the custom widget, you must configure it at the beginning of you
list of form themes (before any more generic themes that would overwrite it).

.. code-block:: yaml↲
twig:↲
    ...↲
    form_themes:↲
        - '@MeteoConceptHCaptcha/hcaptcha_form.html.twig'↲
        - ...↲

TODO
----

For now, if the hCaptcha endpoint returns an HTTP error or times out, the
CAPTCHA is considered invalid. This means that your users can no longer
submit forms if hCaptcha goes down. We need to make this behaviour configurable.



.. _`hCaptcha`: https://www.hcaptcha.com
.. _`hCaptcha dashboard`: https://dashboard.hcaptcha.com
