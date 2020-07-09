<?php

namespace MeteoConcept\HCaptchaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsValidCaptcha extends Constraint
{
    public $message = 'The CAPTCHA is invalid.';
}

