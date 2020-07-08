<?php

namespace MeteoConcept\HCaptchaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('meteo_concept_h_captcha');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('hcaptcha')
                    ->children()
                        ->scalarNode('site_key')
                        ->end()
                        ->scalarNode('secret')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
