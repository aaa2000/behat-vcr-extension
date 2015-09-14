<?php

namespace BVCR\Behat\Cassette;

use UnexpectedValueException;

class BasicFileNamingStrategyFactory implements FileNamingStrategyFactory
{
    public function createFileNamingStrategy($strategyName)
    {
        switch($strategyName) {
            case 'by_tags':
                return new ByTagsFileNamingStrategy();
            case 'by_scenario_name':
                return new ByScenarioNameFileNamingStrategy();
            default:
                throw new UnexpectedValueException(sprintf('No strategy for `s', $strategyName));
        }
    }
}
