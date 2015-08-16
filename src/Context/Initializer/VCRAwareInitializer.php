<?php

namespace BVCR\Behat\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use BVCR\Behat\Context\VCRAwareContext;
use VCR\Videorecorder;

class VCRAwareInitializer implements ContextInitializer
{
    private $videorecorder;

    public function __construct(Videorecorder $videorecorder)
    {
        $this->videorecorder = $videorecorder;
    }

    public function initializeContext(Context $context)
    {
        if ($context instanceof VCRAwareContext) {
            $context->setVideorecorder($this->videorecorder);
        }
    }
}
