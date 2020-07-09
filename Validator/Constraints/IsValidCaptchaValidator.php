<?php

namespace MeteoConcept\HCaptchaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

use MeteoConcept\HCaptchaBundle\Exception\BadAnswerFromHCaptchaException;
use MeteoConcept\HCaptchaBundle\Form\HCaptchaResponse;

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
     * @var HCaptchaVerifier The service that sends the verification request
     * to the hCaptcha endpoint.
     */
    private $verifier;

    /**
     * @brief Constructs the validator from injected dependencies
     *
     * @param HCaptchaVerifier $verifier The service that sends the verification
     * request to the hCaptcha endpoint
     */
    public function __construct(HCaptchaVerifier $verifier)
    {
        $this->verifier = $verifier;
    }

    private function setAsInvalid(Constraint $constraint, string $output)
    {
        $this->context->buildViolation($constraint->message)
             ->addViolation();

        // If the logger is present, log the error message from hCaptcha
        if ($this->logger) {
            $this->logger->error("Failed to validate captcha: " . print_r($output, TRUE));
        }
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
                $this->setAsInvalid($constraint, $output);
            }
        } catch (BadAnswerFromHCaptchaException $e) {
            /*
             * The CAPTCHA is considered solved if hCaptcha sends a 'success' answer
             * (https://docs.hcaptcha.com/#server)
             * TODO: This is a bit dangerous as no user will be able to submit their
             * form if hCaptcha is down. We need a 'lax' and 'strict' mode here to
             * let the developpers choose whether they want to set a violation in
             * case of an HTTP error 500+.
             */
            $this->setAsInvalid($constraint, $e->getMessage());
        }
    }
}
