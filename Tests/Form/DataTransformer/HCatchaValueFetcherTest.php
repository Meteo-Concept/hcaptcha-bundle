<?php

namespace MeteoConcept\HCaptchaBundle\Tests\Form\DataTransformer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use MeteoConcept\HCaptchaBundle\Form\HCaptchaResponse;
use MeteoConcept\HCaptchaBundle\Form\DataTransformer\HCaptchaValueFetcher;

class HCaptchaValueFetcherTest extends TestCase
{
    private $requestStack;

    private $request;

    public function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->request = Request::create(
            '/some_route',
            'POST',
            [ 'h-captcha-response' => 'some_response' ], // request parameters
            [], // cookies
            [], // files
            [ 'REMOTE_ADDR' => '10.0.1.1' ], // server
            'h-captcha-value=some_response'
        );
        $this->requestStack->expects($this->any())
                           ->method('getMasterRequest')
                           ->willReturn($this->request);
    }

    public function test_The_value_fetcher_builds_the_correct_form_value_from_the_request()
    {
        $valueFetcher = new HCaptchaValueFetcher($this->requestStack);

        $value = $valueFetcher->reverseTransform(null);
        $expected = new HCaptchaResponse('some_response', '10.0.1.1');
        $this->assertEquals($expected, $value);
    }
}
