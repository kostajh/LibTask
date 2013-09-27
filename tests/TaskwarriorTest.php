<?php

namespace Taskwarrior;

use LibTask\Taskwarrior;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class TaskwarriorTest extends \PHPUnit_Framework_TestCase
{

    protected $taskrc;
    protected $taskData;

    public function __construct()
    {
        $this->taskData = __DIR__ . '/.task';
        $this->taskrc = __DIR__ . '/.taskrc';
    }

    public static function setUpBeforeClass()
    {
        // Set up test directory.
        $command = sprintf('rm ' . __DIR__ . '/.task/*');
        $process = new Process($command);
        $process->run();

        $process_builder = new ProcessBuilder(
            array(
                'rc:' . __DIR__ . '/.task',
                'rc.data.location=' . __DIR__ . '/.taskrc',
                ));
        $process_builder->setPrefix('task');
        $process = $process_builder->getProcess();
        $process->run();
    }

    public function testImport()
    {
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $taskwarrior->import(__DIR__ . '/sample-tasks.json');
    }

    public function testLoadTasks()
    {
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertNotEmpty($taskwarrior->loadTasks());
    }

    public function testLoadTask()
    {
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertNotEmpty($taskwarrior->loadTask(1));
    }

    public function testLoadTasksWithFilter()
    {
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertNotEmpty($taskwarrior->loadTasks('1'));
    }

    public function testLoadTasksWithOptions()
    {
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertNotEmpty($taskwarrior->loadTasks(null, array('status' => 'pending')));
    }

    public function testLoadNoTasks()
    {
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertEmpty($taskwarrior->loadTasks(md5(time())));
    }

}
