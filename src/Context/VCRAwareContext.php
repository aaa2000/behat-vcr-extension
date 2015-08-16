<?php

namespace BVCR\Behat\Context;

use Behat\Behat\Context\Context;
use VCR\Videorecorder;

interface VCRAwareContext extends Context
{
    /**
     * Set Videorecorder instance.
     *
     * @param \VCR\Videorecorder $videorecorder
     *
     * @return void
     */
    public function setVideorecorder(Videorecorder $videorecorder);
}
