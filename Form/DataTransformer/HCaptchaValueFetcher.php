<?php

namespace MeteoConcept\HCaptchaBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

use MeteoConcept\HCaptchaBundle\Form\HCaptchaResponse;

class HCaptchaValueFetcher implements DataTransformerInterface
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function transform($value)
    {
        // There's nothing to prepopulate, CAPTCHAs are not persisted
        return null;
    }

    public function reverseTransform($value)
    {
        // Actually, we need to get the data directly from the request since HCaptcha uses POST variable
        // h-captcha-response instead of a nicely named variable that would let Symfony find it on its own.
        $masterRequest = $this->requestStack->getMasterRequest();
        $remoteIp      = $masterRequest->getClientIp();
        $response      = $masterRequest->get("h-captcha-response");

        return new HCaptchaResponse($response, $remoteIp);
    }
}
