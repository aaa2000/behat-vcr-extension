<?php

namespace BVCR\Behat\Tests\ServiceContainer;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Processor;
use Behat\Testwork\ServiceContainer\Configuration\ConfigurationTree;
use BVCR\Behat\ServiceContainer\VCRExtension;

class VCRExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var VCRExtension */
    private $extension;
    private $configuration;

    protected function setUp()
    {
        $this->extension = new VCRExtension();
        $this->configuration = array(
            'behat_tags' => array(
                array(
                    'tags' => array('@tag2', '@tag3'),
                    'cassette_path' => 'testapp/fixtures',
                    'cassette_storage' => 'yaml',
                    'library_hooks' => array('curl', 'soap', 'stream_wrapper'),
                    'match_requests_on' => array('method', 'url', 'host', 'headers', 'body', 'post_fields'),
                    'mode' => 'new_episodes',
                    'use_scenario_name' => true,
                ),
                array(
                    'tags' => array('@tag2', '@tag4'),
                    'cassette_path' => 'testapp/other_fixtures',
                    'cassette_storage' => 'json',
                    'library_hooks' => array('curl'),
                    'match_requests_on' => array('method', 'url', 'host'),
                    'mode' => 'none',
                    'use_scenario_name' => false,
                ),
            ),
        );
    }

    public function testLoad()
    {
        $container = new ContainerBuilder();
        $this->extension->load($container, $this->configuration);

        $this->assertTrue($container->hasDefinition('vcr.recorder'));
        $this->assertTrue($container->hasDefinition('vcr.config'));
        $this->assertTrue($container->hasDefinition('vcr.http_client'));
        $this->assertTrue($container->hasDefinition('vcr.factory'));
        $this->assertTrue($container->hasDefinition('vcr.subscriber'));
        $this->assertTrue($container->hasDefinition('vcr.context_initialize'));

    }

    public function testConfigure()
    {
        $configurationTree = new ConfigurationTree();
        $tree = $configurationTree->getConfigTree(array($this->extension));
        $processor = new Processor();
        $config = $processor->process($tree, array('testwork' => array('vcr' => $this->configuration)));

        $this->assertArrayHasKey('vcr', $config);
        $this->assertArrayHasKey('behat_tags', $config['vcr']);

        $vcrBehatTagsConfig = $config['vcr']['behat_tags'];
        $this->assertEquals(array('tag2', 'tag3'), $vcrBehatTagsConfig[0]['tags']);
        $this->assertEquals('testapp/fixtures', $vcrBehatTagsConfig[0]['cassette_path']);
        $this->assertEquals('yaml', $vcrBehatTagsConfig[0]['cassette_storage']);
        $this->assertEquals(array('curl', 'soap', 'stream_wrapper'), $vcrBehatTagsConfig[0]['library_hooks']);
        $this->assertEquals(
            array('method', 'url', 'host', 'headers', 'body', 'post_fields'),
            $vcrBehatTagsConfig[0]['match_requests_on']
        );
        $this->assertEquals('new_episodes', $vcrBehatTagsConfig[0]['mode']);

        $this->assertTrue($vcrBehatTagsConfig[0]['use_scenario_name']);
        $this->assertEquals('by_scenario_name', $vcrBehatTagsConfig[0]['cassette_filenaming_strategy']);

        $this->assertFalse($vcrBehatTagsConfig[1]['use_scenario_name']);
        $this->assertEquals('by_tags', $vcrBehatTagsConfig[1]['cassette_filenaming_strategy']);
    }
}
