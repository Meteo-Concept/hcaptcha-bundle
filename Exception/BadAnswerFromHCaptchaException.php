<?php

namespace MeteoConcept\HCaptchaBundle\Exception;

/**
 * @brief An exception representing a bad answer from the hCaptcha API
 * (either no response, or HTTP code !== 200, or response body not looking
 * like the specification, etc.)
 */
class BadAnswerFromHCaptchaException extends \RuntimeException
{
    /**
     * @brief Constructs the exception with a message
     *
     * @param string $message The error shown to the caller
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
