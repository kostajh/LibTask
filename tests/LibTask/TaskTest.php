<?php

namespace Taskwarrior;

use LibTask\Task;

class TaskTest extends  \PHPUnit_Framework_TestCase
{
    public function testTask() {
        $task = new Task;
        $task->setDescription('Grind coffee beans');
        $task->setDue("20130831T040000Z");
        $task->setEntry("20130831T165548Z");
        $task->setModified("20130831T165548Z");
        $task->setStatus("pending");
        $task->setTags(array('coffee', 'life'));
        $task->setUrgency("10.00");
        $task->setDependencies(NULL);
        $task->setUdas(array('logged' => 'false', 'estimate' => '1.0'));
        $this->assertRegExp('/Grind coffee beans/', $task->getDescription());
    }
}
