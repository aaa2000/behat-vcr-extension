<?php

namespace BVCR\Behat\Resolver;

use BVCR\Behat\Cassette\FileNamingStrategy;

class Configuration
{
    /** @var string */
    private $cassettePath;
    /** @var string */
    private $cassetteStorage;
    /** @var array */
    private $libraryHooks;
    /** @var array */
    private $requestMatchers;
    /** @var string */
    private $mode;
    /** @var array */
    private $tags;
    /** @var \BVCR\Behat\Cassette\FileNamingStrategy */
    private $fileNamingStrategy;

    public function __construct($cassettePath, $cassetteStorage, array $tags, $fileNamingStrategy)
    {
        $this->setCassettePath($cassettePath);
        $this->setCassetteStorage($cassetteStorage);
        $this->setTags($tags);
        $this->setFileNamingStrategy($fileNamingStrategy);
    }
    /**
     * @return string
     */
    public function getCassettePath()
    {
        return $this->cassettePath;
    }

    /**
     * @param string $cassettePath
     */
    public function setCassettePath($cassettePath)
    {
        $this->cassettePath = $cassettePath;
    }

    /**
     * @return string
     */
    public function getCassetteStorage()
    {
        return $this->cassetteStorage;
    }

    /**
     * @param string $cassetteStorage
     */
    public function setCassetteStorage($cassetteStorage)
    {
        $this->cassetteStorage = $cassetteStorage;
    }

    /**
     * @return array
     */
    public function getLibraryHooks()
    {
        return $this->libraryHooks;
    }

    /**
     * @param array $libraryHooks
     */
    public function setLibraryHooks(array $libraryHooks)
    {
        $this->libraryHooks = $libraryHooks;
    }

    /**
     * @return array
     */
    public function getRequestMatchers()
    {
        return $this->requestMatchers;
    }

    /**
     * @param array $requestMatchers
     */
    public function setRequestMatchers(array $requestMatchers)
    {
        $this->requestMatchers = $requestMatchers;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;
    }

    /**
     * @param FileNamingStrategy $fileNamingStrategy
     */
    public function setFileNamingStrategy(FileNamingStrategy $fileNamingStrategy)
    {
        $this->fileNamingStrategy = $fileNamingStrategy;
    }

    /**
     * @return \BVCR\Behat\Cassette\FileNamingStrategy
     */
    public function getFileNamingStrategy()
    {
        return $this->fileNamingStrategy;
    }
}
