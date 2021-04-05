<?php

use MeteoConcept\HCaptchaBundle\Tests\Functional\AppKernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/../../vendor/autoload.php';

umask(0000);
Debug::enable();

$kernel = new AppKernel('test', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
