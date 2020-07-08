<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

use MeteoConcept\HCaptchaBundle\Validator\Constraints\IsValidCaptcha;

class HCaptchaType extends AbstractType implements DataTransformerInterface
{
    private $requestStack;

    private $hcaptchaSiteKey;

    public function __construct(RequestStack $requestStack, string $hcaptchaSiteKey = null)
    {
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this);
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
        return TextareaType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'empty_data' => null,
            'mapped' => false,
            'constraints' => [
                new NotBlank(["message" => "The CAPTCHA in required."]),
                new IsValidCaptcha(),
            ],
        ]);
        if (null !== $this->hcaptchaSiteKey) {
            $resolver->setDefault('hcaptcha_site_key', $this->hcaptchaSiteKey);
        }
        $resolver->setRequired('hcaptchaSiteKey');
    }

    public function transform($value)
    {
        // There's nothing to prepopulate, CAPTCHAs are not persisted
        return null;
    }

    public function reverseTransform($value)
    {
        // Actually, we need to get the data directly from the request since HCaptcha uses POST variable
        // h-captcha-response instead of a nicely named variable that would let Symfony find it on its own.
        $masterRequest = $this->requestStack->getMasterRequest();
        $remoteIp      = $masterRequest->getClientIp();
        $response      = $masterRequest->get("h-captcha-response");

        return new HCaptchaResponse($response, $remoteIp);
    }
}
