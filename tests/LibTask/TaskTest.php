<?php

namespace Taskwarrior;

use LibTask\Task\Task;
use LibTask\Taskwarrior;

class TaskTest extends  \PHPUnit_Framework_TestCase
{
    protected $taskrc;
    protected $taskData;

    public function __construct()
    {
        $this->taskData = __DIR__ . '/.task';
        $this->taskrc = __DIR__ . '/.taskrc';
    }

    /**
     * Test creating a Task.
     *
     * @covers LibTask\Task\Task::__construct
     * @covers LibTask\Task\Task::setDue
     * @covers LibTask\Task\Task::setEntry
     * @covers LibTask\Task\Task::setModified
     * @covers LibTask\Task\Task::setStatus
     * @covers LibTask\Task\Task::setUrgency
     * @covers LibTask\Task\Task::setProject
     * @covers LibTask\Task\Task::setTags
     * @covers LibTask\Task\Task::setPriority
     * @covers LibTask\Task\Task::getDue
     * @covers LibTask\Task\Task::getEntry
     * @covers LibTask\Task\Task::getModified
     * @covers LibTask\Task\Task::getStatus
     * @covers LibTask\Task\Task::getUrgency
     * @covers LibTask\Task\Task::getProject
     * @covers LibTask\Task\Task::getTags
     * @covers LibTask\Task\Task::getPriority
     * @covers LibTask\Task\Task::getId
     */
    public function testTask()
    {
        $task = new Task('Grind coffee beans');
        $task->setDue("20130831T040000Z");
        $task->setEntry("20130831T165548Z");
        $task->setModified("20130831T165548Z");
        $task->setStatus("pending");
        $task->setUrgency("10.00");
        $task->setProject('morning');
        $task->setTags(array('coffee', 'life'));
        $task->setPriority('H');
        // Check if values are set.
        $this->assertRegExp('/Grind coffee beans/', $task->getDescription());
        $this->assertContains('coffee', $task->getTags());
        $this->assertContains('life', $task->getTags());
        $this->assertEquals('pending', $task->getStatus());
        $this->assertEquals('1377921600', $task->getDue());
        $this->assertEquals('1377968148', $task->getEntry());
        $this->assertEquals('1377968148', $task->getModified());
        $this->assertEquals('morning', $task->getProject());
        $this->assertEquals('10.00', $task->getUrgency());
        $this->assertEquals('H', $task->getPriority());
        // Test adding the task.
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $result = $taskwarrior->addTask($task)->getResponse();
        $this->assertEquals('1' ,$result['success']);
        $task = $result['task'];
        $this->assertEquals(4, $task->getId());
    }
}
