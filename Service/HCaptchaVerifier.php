<?php

namespace MeteoConcept\HCaptchaBundle\Service;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

use MeteoConcept\HCaptchaBundle\Exception\BadAnswerFromHCaptchaException;
use MeteoConcept\HCaptchaBundle\Form\HCaptchaResponse;

/**
 * A service able to communicate with the HCaptcha API to send a submitted
 * CAPTCHA for verification, parse the answer, and pass it on to the caller
 */
class HCaptchaVerifier
{
    /**
     * @var ClientInterface A PSR-18 HTTP client service usable to make the
     * API call to hCaptcha.
     */
    private ClientInterface $client;

    /**
     * @var RequestFactoryInterface A PSR-17 HTTP message factory service usable
     * to construct the HTTP request to the hCaptcha endpoints.
     */
    private RequestFactoryInterface $requestFactory;

    /**
     * @var StreamFactoryInterface A PSR-17 stream factory service usable to
     * construct the body of the HTTP request to the hCaptcha endpoint.
     */
    private StreamFactoryInterface $streamFactory;

    /**
     * @var string The secret that authenticates the website in the hCaptcha
     * request
     */
    private string $hcaptchaSecret;

    /**
     * @var LoggerInterface|null An optional logger to log the output from
     * the hCaptcha endpoint
     */
    private ?LoggerInterface $logger;

    /**
     * @var string The hCaptcha verification endpoint
     */
    const HCAPTCHA_VERIFY_URL = 'https://hcaptcha.com/siteverify';

    /**
     * Constructs the validator from injected dependencies
     *
     * @param ClientInterface $client The PSR-18 HTTP client service to make the
     * API call to hCaptcha
     * @param RequestFactoryInterface $requestFactory The PSR-17 HTTP message
     * factory to construct the request to hCaptcha
     * @param StreamFactoryInterface $requestFactory The PSR-17 HTTP stream
     * factory to construct the body of the request to hCaptcha
     * @param string $hcaptchaSecret The secret provided by and sent back to
     * hCaptcha
     * @param LoggerInterface|null $logger An optional logger service, if it's
     * absent, it must be null
     */
    public function __construct(ClientInterface $client,
        RequestFactoryInterface $requestFactory, StreamFactoryInterface $streamFactory,
        string $hcaptchaSecret, LoggerInterface $logger = null)
    {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->hcaptchaSecret = $hcaptchaSecret;
        $this->logger = $logger;
    }

    /**
     * Verifies a submitted hCaptcha
     *
     * This method takes a HCaptchaResponse object (typically constructed by the
     * HCaptchaValueFetcher data transformer from a HCaptcha field in a form),
     * sends the appropriate data to the HCaptcha API, receive the answer from
     * said API, parses it and tells whether the user has validated the CAPTCHA.
     *
     * @param HCaptchaResponse $value A CAPTCHA submitted by a user
     * @param string &$output An optional output string where the entire
     * response from the API will be dumped, if not null
     * @return true if, and only if, the HCaptcha service has signaled the
     * CAPTCHA as valid
     * @throws BadAnswerFromHCaptchaException Thrown if the HCaptcha API has
     * failed to answer in due time or if the answer is out of specification.
     */
    public function verify(HCaptchaResponse $value, string &$output = null): bool
    {
        // Make the validation request to hCaptcha
        $stream = $this->streamFactory->createStream(
            "response=" . urlencode($value->getResponse() ?? "") . "&" .
            "remoteip=" . urlencode($value->getRemoteIp() ?? "") . "&" .
            "secret="   . urlencode($this->hcaptchaSecret ?? "") . "&" .
            "sitekey="  . urlencode($value->getSiteKey()  ?? "")
        );
        $request = $this->requestFactory->createRequest('POST', self::HCAPTCHA_VERIFY_URL)
            ->withBody($stream)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $this->client->sendRequest($request);

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            // Catch timeouts, hCaptcha service downs, etc.
            throw new BadAnswerFromHCaptchaException("Bad answer from hCaptcha endpoint: " . $statusCode . " " . $response->getReasonPhrase());
        }

        $body = $response->getBody();
        $size = $body->getSize();
        if (null === $size || $size > 10 * 1024 * 1024) { // the answer is supposed to be a simple JSON object
            throw new BadAnswerFromHCaptchaException("Unexpectedly large answer from hCaptcha endpoint");
        }

        $content = $body->__toString();
        $json = json_decode($content, TRUE);
        if (null === $json || !isset($json['success'])) {
            // Catch answers out of the specification
            throw new BadAnswerFromHCaptchaException("Missing 'success' key in hCaptcha answer: " . $content);
        }

        if (isset($output)) {
            // Send the raw hCaptcha response to the caller if it wants it
            $output = $content;

            if ($this->logger) {
                $this->logger->notice("The hCaptcha endpoint returned: {$output}");
            }
        }
        return $json['success'];
    }
}
