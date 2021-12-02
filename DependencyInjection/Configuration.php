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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        // I would prefere meteo_concept_hcaptcha but it's probably safer
        // to stick with strict snake_case to make sure it works.
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder('meteo_concept_h_captcha');
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('meteo_concept_h_captcha');
        }

        $rootNode
            ->children()
                ->arrayNode('hcaptcha')
                    ->info("The configuration value of your hCaptcha account (visit https://dashboard.hcaptcha.com to find them).")
                    ->children()
                        ->scalarNode('site_key')
                            ->info("The site key for this website.")
                            ->defaultValue(null)
                        ->end()
                        ->scalarNode('secret')
                            ->info("The secret used to authenticate requests to hCaptcha verification endpoint.")
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('validation')
                    ->info("If 'lax', the CAPTCHA will be considered valid if the hCaptcha endpoint " .
                    "times out or return an unexpected answer. If 'strict' (the default), the hCaptcha " .
                    "MUST return a \"success: true\" answer for the CAPTCHA to validate.")
                    ->defaultValue('strict')
                    ->validate()
                        ->ifNotInArray(['strict', 'lax'])
                        ->thenInvalid('Invalid validation mode %s')
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
