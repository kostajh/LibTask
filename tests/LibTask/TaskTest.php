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
     * @covers LibTask\Task::__construct
     */
    public function testTask() {
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
        $result = $taskwarrior->addTask($task);
        $this->assertEquals('1' ,$result['success']);
    }
}
