<?php

namespace BVCR\Behat\Cassette;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\ScenarioInterface;

class ExtensionDecoratorFileNamingStrategy implements FileNamingStrategy
{
    private $fileNamingStrategy;
    private $extension;

    public function __construct(FileNamingStrategy $fileNamingStrategy, $extension = '')
    {
        $this->fileNamingStrategy = $fileNamingStrategy;
        $this->extension = $extension;
    }

    public function createFilename(FeatureNode $feature, ScenarioInterface $scenario, OutlineNode $outline = null)
    {
        return rtrim(
            sprintf(
                '%s.%s',
                $this->fileNamingStrategy->createFilename($feature, $scenario, $outline),
                $this->extension
            ),
            '.'
        );
    }
}
