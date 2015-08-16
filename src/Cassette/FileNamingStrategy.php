<?php

namespace BVCR\Behat\Cassette;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\ScenarioInterface;

interface FileNamingStrategy
{

    /**
     * Create a file name from a scenario
     *
     * @param FeatureNode $feature
     * @param ScenarioInterface $scenario
     * @param OutlineNode|null $outline
     * @return string
     */
    public function createFilename(FeatureNode $feature, ScenarioInterface $scenario, OutlineNode $outline = null);
}
