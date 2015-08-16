<?php

namespace BVCR\Behat\Cassette;

class BasicFileNamingStrategyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BVCR\Behat\Cassette\FileNamingStrategyFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new BasicFileNamingStrategyFactory();
    }

    public function fileNamingStrategyNamesProvider()
    {
        return array(
            array('by_tags', '\BVCR\Behat\Cassette\ByTagsFileNamingStrategy'),
            array('by_scenario_name', '\BVCR\Behat\Cassette\ByScenarioNameFileNamingStrategy'),
        );
    }

    /**
     * @dataProvider fileNamingStrategyNamesProvider
     * @param string $fileNamingStrategyName
     * @param string $expectedClass
     */
    public function testCreateFileNamingStrategy($fileNamingStrategyName, $expectedClass)
    {
        $this->assertInstanceOf($expectedClass, $this->factory->createFileNamingStrategy($fileNamingStrategyName));
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testCreateFileNamingStrategyWhenStrategyNotFoundShouldThrowsException()
    {
        $this->factory->createFileNamingStrategy('strategy_not_found');
    }
}
