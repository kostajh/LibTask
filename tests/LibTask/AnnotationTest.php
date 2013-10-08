<?php

namespace Taskwarrior;

use LibTask\Task\Task;
use LibTask\Task\Annotation;
use LibTask\Taskwarrior;

class AnnotationTest extends \PHPUnit_Framework_TestCase
{
    protected $taskrc;
    protected $taskData;

    public function __construct()
    {
        $this->taskData = __DIR__ . '/.task';
        $this->taskrc = __DIR__ . '/.taskrc';
    }

    /**
     * Test creating an Annotation.
     *
     * @covers LibTask\Task\Annotation::__construct
     * @covers LibTask\Task\Annotation::getEntry
     * @covers LibTask\Task\Annotation::setEntry
     * @covers LibTask\Task\Annotation::getDescription
     * @covers LibTask\Task\Annotation::setDescription
     */
    public function testAnnotation()
    {
        $time = time();
        $annotation = new Annotation('Testing');
        $this->assertEquals($time, $annotation->getEntry());
        $this->assertEquals('Testing', $annotation->getDescription());
        $annotation->setEntry($time + 1);
        $annotation->setDescription('Time change');
        $this->assertEquals($time +1, $annotation->getEntry());
        $this->assertEquals('Time change', $annotation->getDescription());
    }

    /**
     * Test
     */

}
