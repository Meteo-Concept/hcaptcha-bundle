<?php

namespace MeteoConcept\HCaptchaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

use MeteoConcept\HCaptchaBundle\Exception\BadAnswerFromHCaptchaException;
use MeteoConcept\HCaptchaBundle\Form\HCaptchaResponse;
use MeteoConcept\HCaptchaBundle\Service\HCaptchaVerifier;

/**
 * @brief Validates a CAPTCHA using the hCaptcha API
 *
 * The value that the validator works with must be an instance of
 * HCaptchaResponse, such as the one built by
 * MeteoConcept\HCaptchaBundle\Form\DataTransform\HCaptchaValueFetcher.
 */
class IsValidCaptchaValidator extends ConstraintValidator
{
    /**
     * @var string The 'strict' level of validation: raise a violation when
     * hCaptcha endpoint doesn't return a "success: true" answer
     */
    const STRICT_VALIDATION = 'strict';

    /**
     * @var string The 'lax' level of validation: do not raise a violation when
     * hCaptcha endpoint times out or return an unexpected answer
     */
    const LAX_VALIDATION = 'lax';

    /**
     * @var HCaptchaVerifier The service that sends the verification request
     * to the hCaptcha endpoint.
     */
    private $verifier;

    /**
     * @var string The level of validation, see STRICT_VALIDATION and
     * LAX_VALIDATION above
     */
    private $validation;

    /**
     * @brief Constructs the validator from injected dependencies
     *
     * @param HCaptchaVerifier $verifier The service that sends the verification
     * request to the hCaptcha endpoint
     * @param string $validation The level of validation, strict or lax
     */
    public function __construct(HCaptchaVerifier $verifier, string $validation)
    {
        $this->verifier = $verifier;
        $this->validation = $validation;
    }

    private function setAsInvalid(Constraint $constraint)
    {
        $this->context->buildViolation($constraint->message)
             ->addViolation();
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IsValidCaptcha) {
            throw new UnexpectedTypeException($constraint, IsValidCaptchaValidator::class);
        }

        /*
         * Use the NotBlank() constraint to add a violation if the CAPTCHA
         * response is missing, this is not the responsability of this
         * validator.
         */
        if (null === $value) {
            return;
        }

        /*
         * In order to enforce some strictness in the use of this validator,
         * use a specific type for all validated CAPTCHA responses.
         */
        if (!$value instanceof HCaptchaResponse) {
            throw new UnexpectedValueException($value, HCaptchaResponse::class);
        }

        /*
         * Avoid making the API call if the response is empty and set the
         * violation right away.
         */
        if ("" === $value->getResponse()) {
            $this->context->buildViolation($constraint->message)
                 ->addViolation();
            return;
        }

        // Make the validation request to hCaptcha
        try {
            $output = "";
            $verified = $this->verifier->verify($value, $output);

            if (!$verified) {
                $this->setAsInvalid($constraint);
            }
        } catch (BadAnswerFromHCaptchaException $e) {
            if ($this->validation === self::STRICT_VALIDATION) {
                /*
                 * The CAPTCHA is considered solved if hCaptcha sends a 'success' answer
                 * (https://docs.hcaptcha.com/#server)
                 */
                $this->setAsInvalid($constraint);
            }
            // else ('lax' validation mode), if the hCaptcha endpoint is
            // unresponsive (timeout, maintainance, etc.), we still validate
            // the answer so that we don't frustrate the users too much.
        }
    }
}
