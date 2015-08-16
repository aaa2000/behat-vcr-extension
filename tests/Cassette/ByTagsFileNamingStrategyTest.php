<?php

namespace BVCR\Behat\Cassette;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioNode;

class ByTagsFileNamingStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateFilenameWithOneTag()
    {
        $fileNamingStrategy = new ByTagsFileNamingStrategy();
        $filename = $fileNamingStrategy->createFilename(
            new FeatureNode('feature example', '', array(), null, array(), null, null, null, 1),
            new ScenarioNode('my scenario', array('tag1'), array(), null, 2)
        );
        $this->assertEquals('behat_tags' . DIRECTORY_SEPARATOR . 'tag1', $filename);
    }

    public function testCreateFilenameWithMultipleTag()
    {
        $fileNamingStrategy = new ByTagsFileNamingStrategy();
        $filename = $fileNamingStrategy->createFilename(
            new FeatureNode('feature example', '', array(), null, array(), null, null, null, 1),
            new ScenarioNode('my scenario', array('tag1', 'tag2', 'tag3'), array(), null, 2)
        );
        $this->assertEquals('behat_tags' . DIRECTORY_SEPARATOR . 'tag1_tag2_tag3', $filename);
    }
}
