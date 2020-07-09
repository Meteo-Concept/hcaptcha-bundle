<?php

namespace MeteoConcept\HCaptchaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

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
     * @var HttpClientInterface A Symfony HTTP client service usable to make the
     * API call to hCaptcha.
     */
    private $client;

    /**
     * @var string The secret that authenticates the website in the hCaptcha
     * request
     */
    private $hcaptchaSecret;

    /**
     * @var LoggerInterface|null An *optional* logger service.
     */
    private $logger;

    /**
     * @var string The hCaptcha verification endpoint
     */
    const HCAPTCHA_VERIFY_URL = 'https://hcaptcha.com/siteverify';

    /**
     * @brief Constructs the validator from injected dependencies
     *
     * @param HttpClientInterface $client The Symfony HTTP client service
     * (to make the API call to hCaptcha)
     * @param string $hcaptchaSecret The secret provided by and sent back to
     * hCaptcha
     * @param LoggerInterface|null $logger An optional logger service, if it's
     * absent, it must be null
     */
    public function __construct(HttpClientInterface $client, string $hcaptchaSecret, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->hcaptchaSecret = $hcaptchaSecret;
        $this->logger = $logger;
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
        $response = $this->client->request(
            'POST',
            self::HCAPTCHA_VERIFY_URL,
            [
                'body' => [
                    'response' => $value->getResponse(),
                    'remoteip' => $value->getRemoteIp(),
                    'secret' => $this->hcaptchaSecret,
                ],
            ]
        );

        $statusCode = $response->getStatusCode();
        $content = $response->toArray();

        /*
         * The CAPTCHA is considered solved if hCaptcha sends a 'success' answer
         * (https://docs.hcaptcha.com/#server)
         * TODO: This is a bit dangerous as no user will be able to submit their
         * form if hCaptcha is down. We need a 'lax' and 'strict' mode here to
         * let the developpers choose whether they want to set a violation in
         * case of an HTTP error 500+.
         */
        if ($statusCode != 200 || !isset($content['success']) || !$content['success']) {
            $this->context->buildViolation($constraint->message)
                 ->addViolation();

            // If the logger is present, log the error message from hCaptcha
            if ($this->logger) {
                $this->logger->error("Failed to validate captcha: " . print_r($content, TRUE));
            }
        }
    }
}
