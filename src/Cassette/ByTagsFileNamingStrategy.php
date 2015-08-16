<?php

namespace BVCR\Behat\Cassette;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Transliterator\Transliterator;

class ByTagsFileNamingStrategy implements FileNamingStrategy
{
    const DEFAULT_SEPARATOR = '_';
    private $separator;

    public function __construct($directory = 'behat_tags', $separator = self::DEFAULT_SEPARATOR)
    {
        $this->directory = $directory;
        $this->separator = $separator;
    }

    public function createFilename(FeatureNode $feature, ScenarioInterface $scenario, OutlineNode $outline = null)
    {
        return $this->directory
            . DIRECTORY_SEPARATOR
            . Transliterator::transliterate(implode($this->separator, $scenario->getTags()), $this->separator);
    }
}
