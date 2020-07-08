<?php

namespace MeteoConcept\HCaptchaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

use MeteoConcept\HCaptchaBundle\Form\HCaptchaResponse;

class IsValidCaptchaValidator extends ConstraintValidator
{
    private $client;

    private $hcaptchaSecret;

    private $logger;

    const HCAPTCHA_VERIFY_URL = 'https://hcaptcha.com/siteverify';

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

        if (null === $value) {
            return;
        }

        if (!$value instanceof HCaptchaResponse) {
            throw new UnexpectedValueException($value, HCaptchaResponse::class);
        }

        if ("" === $value->getResponse()) {
            $this->context->buildViolation($constraint->message)
                 ->addViolation();
            return;
        }

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

        if ($statusCode != 200 || !isset($content['success']) || !$content['success']) {
            $this->context->buildViolation($constraint->message)
                 ->addViolation();
            if ($this->logger) {
                $this->logger->error("Failed to validate captcha: " . print_r($content, TRUE));
            }
        }
    }
}
