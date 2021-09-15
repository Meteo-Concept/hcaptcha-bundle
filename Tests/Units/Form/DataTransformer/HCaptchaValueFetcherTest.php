<?php

namespace MeteoConcept\HCaptchaBundle\Tests\Units\Form\DataTransformer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use MeteoConcept\HCaptchaBundle\Form\HCaptchaResponse;
use MeteoConcept\HCaptchaBundle\Form\DataTransformer\HCaptchaValueFetcher;

class HCaptchaValueFetcherTest extends TestCase
{
    private $requestStack;

    private $request;

    private $noCaptchaRequestStack;

    private $noCaptchaRequest;

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

        $this->noCaptchaRequestStack = $this->createMock(RequestStack::class);
        $this->noCaptchaRequest = Request::create(
            '/some_route',
            'POST',
            [], // request parameters
            [], // cookies
            [], // files
            [ 'REMOTE_ADDR' => '10.0.1.1' ], // server
            ''
        );
        $this->noCaptchaRequestStack->expects($this->any())
                           ->method('getMasterRequest')
                           ->willReturn($this->noCaptchaRequest);
    }

    public function test_The_value_fetcher_builds_the_correct_form_value_from_the_request()
    {
        $valueFetcher = new HCaptchaValueFetcher($this->requestStack);
        $valueFetcher->setSiteKey('some_site_key');

        $value = $valueFetcher->reverseTransform(null);
        $expected = new HCaptchaResponse('some_response', '10.0.1.1', 'some_site_key');
        $this->assertEquals($expected, $value);
    }

    public function test_The_value_fetcher_builds_a_null_form_value_if_the_request_contains_no_captcha()
    {
        $valueFetcher = new HCaptchaValueFetcher($this->noCaptchaRequestStack);

        $value = $valueFetcher->reverseTransform(null);
        $this->assertNull($value);
    }

    public function test_The_value_fetcher_does_not_do_transformation_from_model_to_form_data()
    {
        $valueFetcher = new HCaptchaValueFetcher($this->requestStack);

        $value = $valueFetcher->transform(null);
        $this->assertEquals(null, $value);
    }
}
