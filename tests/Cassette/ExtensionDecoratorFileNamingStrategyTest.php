<?php

namespace BVCR\Behat\Cassette;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioNode;

class ExtensionDecoratorFileNamingStrategyTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateFilename()
    {
        /** @var \BVCR\Behat\Cassette\FileNamingStrategy|\PHPUnit_Framework_MockObject_MockObject $filenamingStrategyMock */
        $filenamingStrategyMock = $this->getMockBuilder('BVCR\Behat\Cassette\FileNamingStrategy')
            ->getMock();
        $filenamingStrategyMock->expects($this->any())
            ->method('createFilename')
            ->willReturn('filename');
        $filenamingStrategy = new ExtensionDecoratorFileNamingStrategy($filenamingStrategyMock, 'json');
        $filename = $filenamingStrategy->createFilename(
            new FeatureNode('feature example', '', array(), null, array(), null, null, null, 1),
            new ScenarioNode('my scenario', array(), array(), null, 2)
        );

        $this->assertEquals('filename.json', $filename);
    }
}
