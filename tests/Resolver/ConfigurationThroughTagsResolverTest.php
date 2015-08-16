<?php

namespace BVCR\Behat\Tests\Resolver;

use Behat\Gherkin\Node\ScenarioNode;
use BVCR\Behat\Resolver\ConfigurationThroughTagsResolver;

class ConfigurationThroughTagsResolverTest extends \PHPUnit_Framework_TestCase
{
    private $configuration;
    
    protected function setUp()
    {
        $this->configuration = array(
            'behat_tags' => array(
                array(
                    'tags' => array('tag'),
                    'cassette_path' => 'features/cassettes',
                    'cassette_storage' => 'yaml',
                    'library_hooks' => array('curl', 'soap', 'stream_wrapper'),
                    'match_requests_on' => array('method', 'url', 'host'),
                    'cassette_filenaming_strategy' => 'by_tags',
                    'mode' => 'new_episodes',
                ),
                array(
                    'tags' => array('my_tag'),
                    'cassette_path' => 'features/local_cassettes/',
                    'cassette_storage' => 'yaml',
                    'library_hooks' => array('curl', 'soap', 'stream_wrapper'),
                    'match_requests_on' => array('method', 'url', 'host'),
                    'cassette_filenaming_strategy' => 'by_tags',
                    'mode' => 'none',
                ),
            )
        );
    }

    public function testResolveWhenScenarioTagIsNotConfiguredShouldReturnNull()
    {
        $resolver = new ConfigurationThroughTagsResolver($this->configuration);
        $scenario = new ScenarioNode('scenario test', array('not_exists'), array(), null, null);
        $scenarioConfiguration = $resolver->resolve($scenario);
        $this->assertNull($scenarioConfiguration);
    }

    public function testResolveWhenScenarioTagIsConfiguredShouldReturnConfiguration()
    {
        $resolver = new ConfigurationThroughTagsResolver($this->configuration);
        $scenario = new ScenarioNode('scenario test', array('my_tag'), array(), null, null);
        $scenarioConfiguration = $resolver->resolve($scenario);
        $this->assertInstanceOf('\BVCR\Behat\Resolver\Configuration', $scenarioConfiguration);
        $this->assertEquals(array('my_tag'), $scenarioConfiguration->getTags());
        $this->assertEquals('features/local_cassettes/', $scenarioConfiguration->getCassettePath());
        $this->assertEquals('yaml', $scenarioConfiguration->getCassetteStorage());
        $this->assertEquals(array('curl', 'soap', 'stream_wrapper'), $scenarioConfiguration->getLibraryHooks());
        $this->assertEquals(array('method', 'url', 'host'), $scenarioConfiguration->getRequestMatchers());
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testResolveWhenConfigurationHasNotBehatTagsKeyShouldThrowException()
    {
        new ConfigurationThroughTagsResolver(array());
    }
}
