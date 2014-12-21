LibTask - PHP Library for Taskwarrior
=====================================

LibTask is a PHP library for interacting with [Taskwarrior](http://www.taskwarrior.org) 2.x. You can use LibTask to add, modify, delete, and view tasks in a Taskwarrior database.

[![Build Status](https://travis-ci.org/kostajh/LibTask.png?branch=master)](https://travis-ci.org/kostajh/LibTask)
## Usage

```php
<?php

use LibTask\Task\Task;
use LibTask\Taskwarrior;

$taskwarrior = new Taskwarrior();

// Add a task
$task = new Task('Grind coffee beans');
$task
  ->setDue("today")
  ->setStatus("pending")
  ->setProject('morning')
  ->setTags(array('coffee', 'life'))
  ->setPriority('H');
$response = $taskwarrior->save($task)->getResponse();

// Load tasks
$tasks = $taskwarrior->loadTasks('overdue', array('status' => 'pending'));

```

References
----------

[Taskwarrior JSON Format](http://taskwarrior.org/docs/design/task.html)

Author
------

[Kosta Harlan](http://kostaharlan.net)

