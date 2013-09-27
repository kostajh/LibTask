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

    /**
     * @covers LibTask\Taskwarrior::import
     */
    public function testImport()
    {
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $taskwarrior->import(__DIR__ . '/sample-tasks.json');
    }

    /**
     * @covers LibTask\Taskwarrior::loadTasks();
     */
    public function testLoadTasks()
    {
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertNotEmpty($taskwarrior->loadTasks());
    }

    /**
     * @covers LibTask\Taskwarrior::loadTask();
     */
    public function testLoadTask()
    {
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertNotEmpty($taskwarrior->loadTask(1));
    }

    /**
     * @covers LibTask\Taskwarrior::loadTasks();
     */
    public function testLoadTasksWithFilter()
    {
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertNotEmpty($taskwarrior->loadTasks('1'));
    }

    /**
     * @covers LibTask\Taskwarrior::loadTasks();
     */
    public function testLoadTasksWithOptions()
    {
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertNotEmpty($taskwarrior->loadTasks(null, array('status' => 'pending')));
    }

    /**
     * @covers LibTask\Taskwarrior::loadTasks();
     */
    public function testLoadNoTasks()
    {
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertEmpty($taskwarrior->loadTasks(md5(time())));
    }

}
