<?php

namespace BVCR\Behat\Cassette;


interface FileNamingStrategyFactory
{
    /**
     * @param $strategyName
     * @return \BVCR\Behat\Cassette\FileNamingStrategy
     */
    public function createFileNamingStrategy($strategyName);
}
