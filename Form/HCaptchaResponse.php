<?php

namespace MeteoConcept\HCaptchaBundle\Form;

final class HCaptchaResponse
{
    private $response;
    private $remoteIp;

    public function __construct(string $response, string $remoteIp)
    {
        $this->response = $response;
        $this->remoteIp = $remoteIp;
    }

    public function getResponse(): string
    {
        return $this->response;
    }

    public function getRemoteIp(): string
    {
        return $this->remoteIp;
    }
}
