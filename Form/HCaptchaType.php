<?php

namespace MeteoConcept\HCaptchaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

use MeteoConcept\HCaptchaBundle\Form\DataTransformer\HCaptchaValueFetcher;
use MeteoConcept\HCaptchaBundle\Validator\Constraints\IsValidCaptcha;

/**
 * @brief A form type that represents a hCaptcha field/widget that the user
 * must solve before submitting the form
 */
class HCaptchaType extends AbstractType
{
    /**
     * @var HCaptchValueFetcher The data transformer used to get the CAPTCHA
     * response
     */
    private $valueFetcher;

    /**
     * @var string The hCaptcha site key used to generate the hCaptcha widget
     */
    private $hcaptchaSiteKey;

    /**
     * @brief Constructs the form type from injected dependencies
     *
     * @param HCaptchaValueFetcher $hcaptchaValueFetcher An instance of the
     * HCaptchaValueFetcher service
     * @param string $hcaptchaSiteKey The globally set hCaptcha site key
     * If the site key is not set globally by configuration, then it must be
     * passed as an option to the form type.
     */
    public function __construct(HCaptchaValueFetcher $hcaptchaValueFetcher, string $hcaptchaSiteKey = null)
    {
        $this->valueFetcher = $hcaptchaValueFetcher;
        $this->hcaptchaSiteKey = $hcaptchaSiteKey;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->valueFetcher);

        $builder->addEventListener(FormEvents::PRE_SUBMIT,
            function (FormEvent $event)
            {
                $form = $event->getForm();
                $this->valueFetcher->setSiteKey(
                    $form->getConfig()->getOption('hcaptcha_site_key')
                );
            }
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // This is the variable that Twig can use to display the hCaptcha widget
        $view->vars['hcaptcha_site_key'] = $options['hcaptcha_site_key'];
    }

    public function getBlockPrefix(): string
    {
        return "hcaptcha";
    }

    public function getParent(): ?string
    {
        // Take TextareaType as the parent because the hCaptcha widget kind of
        // takes up the same amount of space in a form (it's a rectangular box...)
        // so maybe this is a good default for layout?
        return TextareaType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        /*
         * Set the 'mapped' option to false since CAPTCHAs are not meant to be
         * persisted.
         * Set the constraints 'NotBlank' and 'IsValidCaptcha' because
         * this is almost always the desired combination of constraints
         * (as usual with custom constraints, the constraint IsValidCaptcha
         * returns silently if the value is null, which is why NotBlank()
         * is also needed).
         */
        $resolver->setDefaults([
            'empty_data' => null,
            'mapped' => false,
            'constraints' => [
                new NotBlank(["message" => "The CAPTCHA is required."]),
                new IsValidCaptcha(),
            ],
        ]);

        /*
         * The hCaptcha globally configured site key is only used by default if
         * it's available. In any wase, the setRequired() call below will ensure
         * that the form has the hcaptcha_site_key option set.
         */
        if (null !== $this->hcaptchaSiteKey) {
            $resolver->setDefault('hcaptcha_site_key', $this->hcaptchaSiteKey);
        }
        $resolver->setRequired('hcaptcha_site_key');
    }
}
