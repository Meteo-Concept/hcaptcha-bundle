<?php

namespace MeteoConcept\HCaptchaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Screams when the user has failed to solve the CAPTCHA
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class IsValidCaptcha extends Constraint
{
    /**
     * @var string The error message displayed on failing to
     * solve the CAPTCHA
     */
    public $message = 'The CAPTCHA is invalid.';
}

