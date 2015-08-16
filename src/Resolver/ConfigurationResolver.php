<?php

namespace BVCR\Behat\Resolver;

use Behat\Gherkin\Node\ScenarioInterface;

interface ConfigurationResolver
{
    /**
     * @param ScenarioInterface $scenario
     * @return \BVCR\Behat\Resolver\Configuration
     */
    public function resolve(ScenarioInterface $scenario);
}
