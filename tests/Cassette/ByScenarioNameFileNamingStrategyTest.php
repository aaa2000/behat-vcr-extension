<?php

namespace BVCR\Behat\Cassette;

use Behat\Gherkin\Node\ExampleTableNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\StepNode;

class ByScenarioNameFileNamingStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateFilenameWithScenario()
    {
        $fileNamingStrategy = new ByScenarioNameFileNamingStrategy();
        $filename = $fileNamingStrategy->createFilename(
            new FeatureNode('feature example', '', array(), null, array(), null, null, null, 1),
            new ScenarioNode('my scenario', array(), array(), null, 2)
        );
        $this->assertEquals('feature_example' . DIRECTORY_SEPARATOR . 'my_scenario', $filename);
    }

    public function testCreateFilenameWithScenarioOutline()
    {
        $steps = array(
            new StepNode('Hello!', 'Hello <firstname> <lastname>', array(), null, 'Given'),
        );

        $table = new ExampleTableNode(array(
            array('firstname', 'lastname'),
            array('foo', 'bar'),
            array('baz', 'qux'),
        ), 'Examples');

        $fileNamingStrategy = new ByScenarioNameFileNamingStrategy();
        $filename = $fileNamingStrategy->createFilename(
            new FeatureNode('feature example', '', array(), null, array(), null, null, null, 1),
            new ScenarioNode('| foo | bar |', array(), array(), null, 2),
            new OutlineNode('my scenario', array(), $steps, $table, null, 1)
        );
        $this->assertEquals(
            'feature_example' . DIRECTORY_SEPARATOR . 'my_scenario' . DIRECTORY_SEPARATOR . '_foo_bar_',
            $filename
        );
    }
}
