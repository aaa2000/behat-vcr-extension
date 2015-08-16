<?php

namespace BVCR\Behat\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class VCRExtension implements Extension
{
    const VCR_RECORDER_ID = 'vcr.recorder';
    const VCR_CONFIG_ID = 'vcr.config';
    const VCR_HTTP_CLIENT_ID = 'vcr.http_client';
    const VCR_FACTORY_ID = 'vcr.factory';
    const VCR_SUBSCRIBER_ID = 'vcr.subscriber';
    const VCR_CONTEXT_INITIALIZE_ID = 'vcr.context_initialize';
    const VCR_CONFIGURATION_RESOLVER_ID = 'vcr.configuration_resolver';

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'vcr';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $normalizeTags = function ($tags) {
            return array_map(function ($tag) {
                return ltrim($tag, '@');
            }, $tags);
        };

        $defineCassetteFilenamingStrategy = function ($item) {
            if (isset($item['use_scenario_name']) && true === $item['use_scenario_name']) {
                $item['cassette_filenaming_strategy'] = 'by_scenario_name';
            }
            return $item;
        };

        $builder
            ->children()
                ->arrayNode('behat_tags')
                    ->prototype('array')
                    ->beforeNormalization()
                    ->always()
                    ->then($defineCassetteFilenamingStrategy)
                    ->end()
                    ->children()
                            ->arrayNode('tags')
                                ->info('Behat tags to tell VCR to use a cassette for the tagged scenario')
                                ->prototype('scalar')
                                ->end()
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->beforeNormalization()
                                    ->always()
                                    ->then($normalizeTags)
                                ->end()
                            ->end()
                            ->scalarNode('cassette_path')
                                ->info('Path where cassette files should be stored')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('cassette_storage')
                                ->info('Format in which is stored the cassette')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->variableNode('library_hooks')
                                ->info('Enable only some library hooks')
                            ->end()
                            ->variableNode('match_requests_on')
                                ->info('Customize how VCR matches requests')
                            ->end()
                            ->scalarNode('mode')
                                ->info('Record mode determines how requests are handled')
                            ->end()
                            ->booleanNode('use_scenario_name')
                                ->info('VCR name the cassettes automatically according to the scenario name')
                                ->defaultFalse()
                            ->end()
                            ->enumNode('cassette_filenaming_strategy')
                                ->info('VCR cassette filenaming strategy')
                                ->defaultValue('by_tags')
                                ->values(array('by_scenario_name', 'by_tags'))
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadEventSubscriber($container);
        $this->loadVcrConfig($container);
        $this->loadVcrClient($container);
        $this->loadVcrFactory($container);
        $this->loadVcrRecorder($container);
        $this->loadContextInitializer($container);
        $this->loadConfigurationResolver($container, $config);
    }

    private function loadEventSubscriber(ContainerBuilder $container)
    {
        $definition = new Definition('BVCR\Behat\EventDispatcher\VCRSubscriber', array(
            new Reference(self::VCR_RECORDER_ID),
            new Reference(self::VCR_CONFIGURATION_RESOLVER_ID)
        ));
        $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG);
        $container->setDefinition(self::VCR_SUBSCRIBER_ID, $definition);
    }

    private function loadVcrConfig(ContainerBuilder $container)
    {
        $definition = new Definition('VCR\Configuration');
        $container->setDefinition(self::VCR_CONFIG_ID, $definition);
    }

    private function loadVcrClient(ContainerBuilder $container)
    {
        $definition = new Definition('VCR\Util\HttpClient');
        $container->setDefinition(self::VCR_HTTP_CLIENT_ID, $definition);
    }

    private function loadVcrFactory(ContainerBuilder $container)
    {
        $definition = new Definition('VCR\VCRFactory', array(new Reference(self::VCR_CONFIG_ID)));
        $definition->setFactory(array('VCR\VCRFactory', 'getInstance'));
        $container->setDefinition(self::VCR_FACTORY_ID, $definition);
    }

    private function loadVcrRecorder(ContainerBuilder $container)
    {
        $definition = new Definition(
            'VCR\Videorecorder',
            array(
                new Reference(self::VCR_CONFIG_ID),
                new Reference(self::VCR_HTTP_CLIENT_ID),
                new Reference(self::VCR_FACTORY_ID),
            )
        );
        $definition->addMethodCall('setEventDispatcher', array(new Reference('event_dispatcher')));
        $container->setDefinition(self::VCR_RECORDER_ID, $definition);
    }

    private function loadContextInitializer(ContainerBuilder $container)
    {
        $definition = new Definition('BVCR\Behat\Context\Initializer\VCRAwareInitializer', array(
            new Reference(self::VCR_RECORDER_ID)
        ));
        $definition->addTag(ContextExtension::INITIALIZER_TAG);
        $container->setDefinition(self::VCR_CONTEXT_INITIALIZE_ID, $definition);
    }

    private function loadConfigurationResolver(ContainerBuilder $container, $config)
    {
        $definition = new Definition('BVCR\Behat\Resolver\ConfigurationThroughTagsResolver', array($config));
        $container->setDefinition(self::VCR_CONFIGURATION_RESOLVER_ID, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
