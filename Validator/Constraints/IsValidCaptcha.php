<?php

namespace MeteoConcept\HCaptchaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @brief Screams when the user has failed to solve the CAPTCHA
 * @Annotation
 */
class IsValidCaptcha extends Constraint
{
    /**
     * @var string The error message displayed on failing to
     * solve the CAPTCHA
     */
    public $message = 'The CAPTCHA is invalid.';
}

