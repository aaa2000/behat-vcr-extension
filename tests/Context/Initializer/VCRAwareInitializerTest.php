<?php

namespace BVCR\Behat\Tests\Context\Initializer;

use BVCR\Behat\Context\Initializer\VCRAwareInitializer;

class VCRAwareInitializerTest extends \PHPUnit_Framework_TestCase
{
    /**  @var \VCR\Videorecorder|\PHPUnit_Framework_MockObject_MockObject */
    private $videorecorder;
    private $initializer;

    protected function setUp()
    {
        $this->videorecorder = $this->getMockBuilder('\VCR\Videorecorder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->initializer = new VCRAwareInitializer($this->videorecorder);
    }

    public function testInitializeContextShouldSetVideorecorderOnVCRAwareContext()
    {
        $context = $this->getMockBuilder('\BVCR\Behat\Context\VCRAwareContext')
            ->getMock();
        $context->expects($this->once())
            ->method('setVideorecorder');
        $this->initializer->initializeContext($context);
    }

    public function testInitializeContextShouldNotSetVideorecorderOnBehatContext()
    {
        $context = $this->getMockBuilder('\Behat\Behat\Context\Context')
            ->getMock();
        $context->expects($this->never())
            ->method('setVideorecorder');
        $this->initializer->initializeContext($context);
    }
}
