<?php

namespace MeteoConcept\HCaptchaBundle\Tests\Units\Service;

use PHPUnit\Framework\TestCase;

use Http\Mock\Client;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

use MeteoConcept\HCaptchaBundle\Form\HCaptchaResponse;
use MeteoConcept\HCaptchaBundle\Service\HCaptchaVerifier;
use MeteoConcept\HCaptchaBundle\Exception\BadAnswerFromHCaptchaException;

class HCaptchaVerifierTest extends TestCase
{
    private $client;

    private $hCaptchaVerifier;

    private $psr17factory;

    private $universalRequestMatcher;

    public function setUp(): void
    {
        $this->client = new Client();
        $this->psr17factory = new Psr17Factory();
        $this->hcaptchaSecret = "0x00000000000000000000000000000000";
        $this->hCaptchaVerifier = new HCaptchaVerifier($this->client, $this->psr17factory, $this->psr17factory, $this->hcaptchaSecret);
    }

    public function test_The_request_is_properly_formed_and_correct_answer_is_handled_correctly()
    {
        $body = $this->psr17factory->createStream('{ "success": true }');
        $response = $this->psr17factory->createResponse(200)->withBody($body);
        $this->client->addResponse($response);

        $captchaValue = new HCaptchaResponse("response", "remoteip", "sitekey");
        $verification = $this->hCaptchaVerifier->verify($captchaValue);

        $requests = $this->client->getRequests();
        $this->assertEquals(1, count($requests));
        $request = $requests[0];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
        $content = explode('&', $request->getBody());
        $this->assertTrue(in_array('response=response', $content));
        $this->assertTrue(in_array('remoteip=remoteip', $content));
        $this->assertTrue(in_array("secret={$this->hcaptchaSecret}", $content));
        $this->assertTrue(in_array('sitekey=sitekey', $content));

        $this->assertTrue($verification);
    }

    public function test_The_answer_from_HCaptcha_is_logged_if_a_logger_is_configured()
    {
        $body = $this->psr17factory->createStream('{ "success": true }');
        $response = $this->psr17factory->createResponse(200)->withBody($body);
        $this->client->addResponse($response);

        $logger = $this->createMock(LoggerInterface::class);
        $hCaptchaVerifierWithLogger = new HCaptchaVerifier($this->client, $this->psr17factory, $this->psr17factory, $this->hcaptchaSecret, $logger);

        $logger->expects($this->once())
               ->method('notice')
               ->with($this->stringContains('{ "success": true }'));

        $captchaValue = new HCaptchaResponse("response", "remoteip", "sitekey");
        $output = "";
        $verification = $hCaptchaVerifierWithLogger->verify($captchaValue, $output);
        $this->assertEquals($output, '{ "success": true }');
    }

    public function test_The_verifier_returns_false_without_throwing_if_the_answer_is_no()
    {
        $body = $this->psr17factory->createStream('{ "success": false }');
        $response = $this->psr17factory->createResponse(200)->withBody($body);
        $this->client->addResponse($response);

        $captchaValue = new HCaptchaResponse("badresponse", "remoteip", "sitekey");
        $verification = $this->hCaptchaVerifier->verify($captchaValue);

        $this->assertFalse($verification);
    }

    public function test_The_verifier_throws_if_the_answer_is_not_200_OK()
    {
        $response = $this->psr17factory->createResponse(500, 'Not working');
        $this->client->addResponse($response);

        $this->expectException(BadAnswerFromHCaptchaException::class);

        $captchaValue = new HCaptchaResponse("response", "remoteip", "sitekey");
        $verification = $this->hCaptchaVerifier->verify($captchaValue);
    }

    public function test_The_verifier_throws_if_the_answer_body_is_not_JSON()
    {
        $body = $this->psr17factory->createStream('Bad content; that\'s not good at all.');
        $response = $this->psr17factory->createResponse(200);
        $this->client->addResponse($response);

        $this->expectException(BadAnswerFromHCaptchaException::class);

        $captchaValue = new HCaptchaResponse("response", "remoteip", "sitekey");
        $verification = $this->hCaptchaVerifier->verify($captchaValue);
    }
}
