<?php

namespace BVCR\Behat\Cassette;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Transliterator\Transliterator;

class ByScenarioNameFileNamingStrategy implements FileNamingStrategy
{
    const DEFAULT_SEPARATOR = '_';
    private $separator;

    public function __construct($separator = self::DEFAULT_SEPARATOR)
    {
        $this->separator = $separator;
    }

    public function createFilename(FeatureNode $feature, ScenarioInterface $scenario, OutlineNode $outline = null)
    {
        $filename = Transliterator::transliterate($feature->getTitle(), $this->separator) . DIRECTORY_SEPARATOR;

        if ($outline) {
            $filename .= Transliterator::transliterate($outline->getTitle(), $this->separator)
                . DIRECTORY_SEPARATOR . $this->separator;
        }

        $filename .= Transliterator::transliterate($scenario->getTitle(), $this->separator);

        if ($outline) {
            $filename .= $this->separator;
        }

        return $filename;
    }
}
