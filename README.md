LibTask - PHP Library for Taskwarrior
=======

LibTask is a PHP library for interacting with [Taskwarrior](http://www.taskwarrior.org) 2.x. You can use LibTask to add, modify, delete, and view tasks in a Taskwarrior database.

[![Build Status](https://travis-ci.org/kostajh/LibTask.png?branch=master)](https://travis-ci.org/kostajh/LibTask)
## Usage

```php
<?php

use LibTask\Task\Task;
use LibTask\Taskwarrior;

// Add a task
$task = new Task('Grind coffee beans');
$task->setDue("today");
$task->setStatus("pending");
$task->setProject('morning');
$task->setTags(array('coffee', 'life'));
$task->setPriority('H');

$taskwarrior = new Taskwarrior();
$taskwarrior->addTask($task);

// Load tasks
$tasks = $taskwarrior->loadTasks('overdue', array('status' => 'pending'));

```

## Author

[Kosta Harlan](http://kostaharlan.net)

