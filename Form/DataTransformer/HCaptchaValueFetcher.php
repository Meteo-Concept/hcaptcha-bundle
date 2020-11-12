<?php

namespace MeteoConcept\HCaptchaBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

use MeteoConcept\HCaptchaBundle\Form\HCaptchaResponse;

/**
 * @brief A weird data transformer that actually does not act on the
 * field value but on a specific POST variables in the request
 */
class HCaptchaValueFetcher implements DataTransformerInterface
{
    /**
     * @var RequestStack The service needed to get access to the POST
     * variables
     */
    private $requestStack;

    /**
     * @brief Constructs an instance of the HCaptchaValueFetcher from
     * injected dependencies
     *
     * This class does nothing useful when displaying the form, it's
     * only useful on a POST request, when the form is submitted.
     *
     * @param RequestStack $requestStack The Symfony service used to
     * access the object representing the user's HTTP request.
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function transform($value)
    {
        /*
         * There's nothing to transform, CAPTCHAs are not persisted and it's not
         * possible to set a value to the hCaptcha widget before the user solves
         * the CAPTCHA.
         */
        return null;
    }

    public function reverseTransform($value)
    {
        /*
         * We need to get the data directly from the request since hCaptcha uses
         * the POST variable h-captcha-response instead of a nicely named
         * variable that would let the Symfony Form component find it on its own.
         */
        $masterRequest = $this->requestStack->getMasterRequest();
        $response      = $masterRequest->get("h-captcha-response");

        // Can happen if the Captcha JS has failed to load for instance
        if (null === $response)
            return null;

        $remoteIp = $masterRequest->getClientIp();

        return new HCaptchaResponse($response, $remoteIp);
    }
}
