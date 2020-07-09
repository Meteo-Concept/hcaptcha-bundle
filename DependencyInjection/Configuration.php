<?php

namespace MeteoConcept\HCaptchaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @brief The configuration class for the bundle, fairly straightforward
 *
 * The hCaptcha site key and secret are inside a 'hcaptcha' key in case we
 * need more configuration unrelated to hCaptcha later.
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        // I would prefere meteo_concept_hcaptcha but it's probablu safer
        // to stick with strict snake_case to make sure it works.
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
