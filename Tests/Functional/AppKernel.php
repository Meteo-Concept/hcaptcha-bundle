<?php

namespace MeteoConcept\HCaptchaBundle\Tests\Functional;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        $bundles = array();

        if (in_array($this->getEnvironment(), array('test'))) {
            $bundles[] = new \Symfony\Bundle\FrameworkBundle\FrameworkBundle();
            $bundles[] = new \Symfony\Bundle\TwigBundle\TwigBundle();
            $bundles[] = new \MeteoConcept\HCaptchaBundle\MeteoConceptHCaptchaBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/test_config_functional.yml');
        if (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION > 5) {
            $kernel->addConfigFile(__DIR__.'/test_framework_config_sf6.yml');
        } else {
            $kernel->addConfigFile(__DIR__.'/test_framework_config.yml');
        }
    }
}

