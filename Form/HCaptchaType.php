<?php

namespace MeteoConcept\HCaptchaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

use MeteoConcept\HCaptchaBundle\Form\DataTransformer\HCaptchaValueFetcher;
use MeteoConcept\HCaptchaBundle\Validator\Constraints\IsValidCaptcha;

class HCaptchaType extends AbstractType
{
    private $valueFetcher;

    private $hcaptchaSiteKey;

    public function __construct(HCaptchaValueFetcher $hcaptchaValueFetcher, string $hcaptchaSiteKey = null)
    {
        $this->valueFetcher = $hcaptchaValueFetcher;
        $this->hcaptchaSiteKey = $hcaptchaSiteKey;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->valueFetcher);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['hcaptcha_site_key'] = $options['hcaptcha_site_key'];
    }

    public function getBlockPrefix(): string
    {
        return "hcaptcha";
    }

    public function getParent()
    {
        // Take TextareaType as the parent because the hCaptcha widget kind of
        // takes up the same amount of space in a form (it's a rectangular box...)
        // so maybe this is a good default for layout?
        return TextareaType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'empty_data' => null,
            'mapped' => false,
            'constraints' => [
                new NotBlank(["message" => "The CAPTCHA is required."]),
                new IsValidCaptcha(),
            ],
        ]);
        if (null !== $this->hcaptchaSiteKey) {
            $resolver->setDefault('hcaptcha_site_key', $this->hcaptchaSiteKey);
        }
        $resolver->setRequired('hcaptcha_site_key');
    }
}
