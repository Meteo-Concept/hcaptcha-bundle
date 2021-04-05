<?php

namespace MeteoConcept\HCaptchaBundle\Tests\Units\Form;

use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

use MeteoConcept\HCaptchaBundle\Form\HCaptchaType;
use MeteoConcept\HCaptchaBundle\Form\HCaptchaResponse;
use MeteoConcept\HCaptchaBundle\Form\DataTransformer\HCaptchaValueFetcher;

class HCaptchaTypeTest extends TypeTestCase
{
    private $valueFetcher;

    const SITE_KEY = "10000000-ffff-ffff-ffff-000000000001";

    protected function setUp(): void
    {
        $this->valueFetcher = $this->createMock(HCaptchaValueFetcher::class);

        parent::setUp();
    }

    protected function getExtensions()
    {
        // create a type instance with the mocked dependencies
        $type = new HCaptchaType($this->valueFetcher, self::SITE_KEY);

        return [
            // register the type instances with the PreloadedExtension
            new PreloadedExtension([$type], []),
        ];
    }

    public function test_The_form_type_compiles_and_gets_the_correct_data()
    {
        $expected = new HCaptchaResponse('response', 'remoteip', 'sitekey');

        $this->valueFetcher->expects($this->any())
                           ->method('setSiteKey')
                           ->with(self::SITE_KEY);

        $this->valueFetcher->expects($this->any())
                           ->method('reverseTransform')
                           ->willReturn($expected);

        $this->valueFetcher->expects($this->any())
                           ->method('transform')
                           ->willReturn(null);

        // Instead of creating a new instance, the one created in
        // getExtensions() will be used.
        $form = $this->factory->create(HCaptchaType::class);

        // no data, because this form type is a bit special
        $form->submit("");

        // This check ensures there are no transformation failures
        $this->assertTrue($form->isSynchronized());

        // check that the form now has the expected data after submission
        // That can only fail if the data transformer is somehow not set correctly
        $this->assertEquals($expected, $form->getData());
    }

    public function test_The_form_type_has_the_default_site_key_as_option()
    {
        $form = $this->factory->create(HCaptchaType::class);

        // if we don't pass a custom site key, the site key passed in the
        // service configuration should be used
        $this->assertEquals($form->getConfig()->getOption('hcaptcha_site_key'), self::SITE_KEY);
    }
}
