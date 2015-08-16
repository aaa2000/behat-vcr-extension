<?php

namespace BVCR\Behat\Resolver;

use Behat\Gherkin\Node\ScenarioInterface;
use BVCR\Behat\Cassette\ExtensionDecoratorFileNamingStrategy;
use BVCR\Behat\Cassette\BasicFileNamingStrategyFactory;

class ConfigurationThroughTagsResolver implements ConfigurationResolver
{
    private $extensionConfiguration;
    /**
     * @var \BVCR\Behat\Cassette\FileNamingStrategyFactory
     */
    private $cassetteFileNamingStrategyFactory;

    public function __construct(array $extensionConfiguration)
    {
        if (!isset($extensionConfiguration['behat_tags'])) {
            throw new \UnexpectedValueException('behat_tags key missing in VCRExtension configuration');
        }
        $this->extensionConfiguration = $extensionConfiguration;
        $this->cassetteFileNamingStrategyFactory = new BasicFileNamingStrategyFactory();
    }

    public function resolve(ScenarioInterface $scenario)
    {
        $scenarioTags = $scenario->getTags();

        foreach ($this->extensionConfiguration['behat_tags'] as $tagConfiguration) {
            if ($this->isConfigurationMatchToTags($tagConfiguration, $scenarioTags)) {
                return $this->createConfiguration($tagConfiguration);
            }
        }

        return null;
    }

    private function createConfiguration(array $tagConfiguration)
    {
        $configuration = new Configuration(
            $tagConfiguration['cassette_path'],
            $tagConfiguration['cassette_storage'],
            $tagConfiguration['tags'],
            new ExtensionDecoratorFileNamingStrategy(
                $this->cassetteFileNamingStrategyFactory->createFileNamingStrategy(
                    $tagConfiguration['cassette_filenaming_strategy']
                ),
                $this->guessExtensionFromCassetteStorage($tagConfiguration['cassette_storage'])
            )
        );

        if (isset($tagConfiguration['library_hooks'])) {
            $configuration->setLibraryHooks($tagConfiguration['library_hooks']);
        }
        if (isset($tagConfiguration['match_requests_on'])) {
            $configuration->setRequestMatchers($tagConfiguration['match_requests_on']);
        }
        if (isset($tagConfiguration['mode'])) {
            $configuration->setMode($tagConfiguration['mode']);
        }

        return $configuration;
    }

    private function guessExtensionFromCassetteStorage($cassetteStorage)
    {
        $mapping = array(
            'json' => 'json',
            'yaml' => 'yml',
        );
        return isset($mapping[$cassetteStorage]) ? $mapping[$cassetteStorage] : '';
    }

    private function isConfigurationMatchToTags(array $tagConfiguration, array $tags)
    {
        if (!isset($tagConfiguration['tags'])) {
            return false;
        }
        $intersection = array_intersect($tags, $tagConfiguration['tags']);
        return !empty($intersection);
    }
}
