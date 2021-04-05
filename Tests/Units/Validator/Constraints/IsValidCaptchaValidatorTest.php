<?php

namespace MeteoConcept\HCaptchaBundle\Tests\Units\Validator\Constraints;

use PHPUnit\Framework\TestCase;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

use MeteoConcept\HCaptchaBundle\Validator\Constraints\IsValidCaptchaValidator;
use MeteoConcept\HCaptchaBundle\Validator\Constraints\IsValidCaptcha;
use MeteoConcept\HCaptchaBundle\Service\HCaptchaVerifier;
use MeteoConcept\HCaptchaBundle\Form\HCaptchaResponse;
use MeteoConcept\HCaptchaBundle\Exception\BadAnswerFromHCaptchaException;

class IsValidCaptchaValidatorTest extends TestCase
{
    private $hCaptchaVerifier;

    private $executionContext;

    private $violationBuilder;

    private $validator;

    private $constraint;

    public function setUp(): void
    {
        $this->hCaptchaVerifier = $this->createMock(HCaptchaVerifier::class);
        $this->executionContext = $this->createMock(ExecutionContextInterface::class);
        $this->violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->validator = new IsValidCaptchaValidator($this->hCaptchaVerifier, 'strict');
        $this->validator->initialize($this->executionContext);
        $this->constraint = new IsValidCaptcha();
    }

    public function test_The_validator_sets_a_violation_if_the_response_is_empty()
    {
        $value = new HCaptchaResponse("", "", "");

        $this->executionContext
            ->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->message)
            ->willReturn($this->violationBuilder);

        $this->violationBuilder
            ->expects($this->once())
            ->method('addViolation')
            ->willReturn(null);

        $this->validator->validate($value, $this->constraint);
    }

    public function test_The_validator_sets_a_violation_if_the_verification_fails()
    {
        $value = new HCaptchaResponse("ok", "", "");

        $this->hCaptchaVerifier
             ->expects($this->once())
             ->method('verify')
             ->with($value, "")
             ->willReturn(false);

        $this->executionContext
            ->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->message)
            ->willReturn($this->violationBuilder);

        $this->violationBuilder
            ->expects($this->once())
            ->method('addViolation')
            ->willReturn(null);

        $this->validator->validate($value, $this->constraint);
    }

    public function test_The_validator_sets_a_violation_if_the_verification_throws_in_strict_mode()
    {
        $value = new HCaptchaResponse("ok", "", "");

        $this->hCaptchaVerifier
             ->expects($this->once())
             ->method('verify')
             ->with($value, "")
             ->will($this->throwException(new BadAnswerFromHCaptchaException("oops")));

        $this->executionContext
            ->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->message)
            ->willReturn($this->violationBuilder);

        $this->violationBuilder
            ->expects($this->once())
            ->method('addViolation')
            ->willReturn(null);

        $this->validator->validate($value, $this->constraint);
    }

    public function test_The_validator_does_not_set_a_violation_if_the_verification_throws_in_lax_mode()
    {
        $this->validator = new IsValidCaptchaValidator($this->hCaptchaVerifier, 'lax');
        $this->validator->initialize($this->executionContext);

        $value = new HCaptchaResponse("ok", "", "");

        $this->hCaptchaVerifier
             ->expects($this->once())
             ->method('verify')
             ->with($value, "")
             ->will($this->throwException(new BadAnswerFromHCaptchaException("oops")));

        $this->executionContext
            ->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($value, $this->constraint);
    }

    public function test_The_validator_does_not_set_a_violation_if_the_verification_succeeds()
    {
        $value = new HCaptchaResponse("ok", "", "");

        $this->hCaptchaVerifier
             ->expects($this->once())
             ->method('verify')
             ->with($value, "")
             ->willReturn(true);

        $this->executionContext
            ->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($value, $this->constraint);
    }
}
