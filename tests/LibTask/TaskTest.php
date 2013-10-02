<?php

namespace Taskwarrior;

use LibTask\Task;
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
        $this->assertRegExp('/Grind coffee beans/', $task->getDescription());
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $result = $taskwarrior->addTask($task);
    }
}
