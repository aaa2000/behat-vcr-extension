<?php

namespace BVCR\Behat\EventDispatcher;

use Behat\Behat\EventDispatcher\Event\BeforeOutlineTested;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Gherkin\Node\OutlineNode;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use VCR\Videorecorder;
use BVCR\Behat\Resolver\ConfigurationResolver;
use BVCR\Behat\Resolver\Configuration;

class VCRSubscriber implements EventSubscriberInterface
{
    /** @var \VCR\Videorecorder */
    private $videorecorder;
    /** @var \BVCR\Behat\Resolver\ConfigurationResolver */
    private $configurationResolver;
    /** @var OutlineNode */
    private $outline;


    public function __construct(Videorecorder $videorecorder, ConfigurationResolver $configurationResolver)
    {
        $this->videorecorder = $videorecorder;
        $this->configurationResolver = $configurationResolver;
    }

    public static function getSubscribedEvents()
    {
        return array(
            ScenarioTested::BEFORE => 'turnOnVCR',
            ScenarioTested::AFTER => 'turnOffVCR',
            ExampleTested::BEFORE => 'turnOnVCR',
            ExampleTested::AFTER => 'turnOffVCR',
            OutlineTested::BEFORE => 'captureOutlineOnBeforeOutlineEvent',
            OutlineTested::AFTER => 'forgetOutlineOnAfterOutlineEvent',
        );
    }

    public function turnOnVCR(ScenarioTested $event)
    {
        $configuration = $this->configurationResolver->resolve($event->getScenario());

        if ($configuration) {
            $this->videorecorder->turnOn();
            $this->configureVCR($configuration, $event);
        }
    }

    /**
     * Captures outline into the ivar on outline BEFORE event.
     *
     * @param Event $event
     */
    public function captureOutlineOnBeforeOutlineEvent(Event $event)
    {
        if (!$event instanceof BeforeOutlineTested) {
            return;
        }

        $this->outline = $event->getOutline();
    }

    /**
     * Removes outline from the ivar on outline AFTER event.
     *
     * @param Event $event
     */
    public function forgetOutlineOnAfterOutlineEvent(Event $event)
    {
        if (!$event instanceof BeforeOutlineTested) {
            return;
        }

        $this->outline = null;
    }

    private function configureVCR(Configuration $configuration, ScenarioTested $event)
    {
        $currentVcrConfiguration = $this->videorecorder->configure();

        if ($configuration->getCassettePath()) {
            $currentVcrConfiguration->setCassettePath($configuration->getCassettePath());
        }
        if ($configuration->getCassetteStorage()) {
            $currentVcrConfiguration->setStorage($configuration->getCassetteStorage());
        }
        if ($configuration->getLibraryHooks()) {
            $currentVcrConfiguration->enableLibraryHooks($configuration->getLibraryHooks());
        }
        if ($configuration->getRequestMatchers()) {
            $currentVcrConfiguration->enableRequestMatchers($configuration->getRequestMatchers());
        }
        if ($configuration->getMode()) {
            $currentVcrConfiguration->setMode($configuration->getMode());
        }

        $filename = $configuration->getFileNamingStrategy()->createFilename(
            $event->getFeature(),
            $event->getScenario(),
            $this->outline
        );
        $this->videorecorder->insertCassette($filename);
    }

    public function turnOffVCR(ScenarioTested $event)
    {
        $this->videorecorder->turnOff();
    }
}
