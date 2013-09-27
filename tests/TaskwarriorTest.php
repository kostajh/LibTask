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

    protected function deleteTestData()
    {
        // Set up test directory.
        $command = sprintf('rm ' . __DIR__ . '/.task/*');
        $process = new Process($command);
        $process->run();
    }

    protected function mangleTaskData()
    {
        $command = 'echo "Mangled" >> ' . __DIR__ . '/.task/pending.data';
        $process = new Process($command);
        $process->run();
    }

    protected function initializeTaskwarrior()
    {
        $process_builder = new ProcessBuilder(
            array(
                'rc:' . __DIR__ . '/.task',
                'rc.data.location=' . __DIR__ . '/.taskrc',
                ));
        $process_builder->setPrefix('task');
        $process = $process_builder->getProcess();
        $process->run();
    }

    public static function setUpBeforeClass()
    {
        self::deleteTestData();
        self::initializeTaskwarrior();
    }

    /**
     * @covers LibTask\Taskwarrior::__construct
     */
    public function testTaskwarrior()
    {
        return TRUE;
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
     * @covers LibTask\Taskwarrior::loadTasks
     */
    public function testLoadTasks()
    {
        // Load all tasks.
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertNotEmpty($taskwarrior->loadTasks());
        // Load tasks with filter.
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertNotEmpty($taskwarrior->loadTasks('1'));
        // Load tasks with options.
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertNotEmpty($taskwarrior->loadTasks(null, array('status' => 'pending')));
        // Empty result when loading tasks.
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertEmpty($taskwarrior->loadTasks(md5(time())));
        // Empty task database.
        $taskwarrior = new Taskwarrior(md5(time()), md5(time()));
        $this->assertEmpty($taskwarrior->loadTasks());
        // Test failing export command.
        self::mangleTaskData();
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertFalse($taskwarrior->loadTasks());
    }

    /**
     * @covers LibTask\Taskwarrior::loadTask
     */
    public function testLoadTask()
    {
        self::deleteTestData();
        self::initializeTaskwarrior();
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $taskwarrior->import(__DIR__ . '/sample-tasks.json');
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertNotEmpty($taskwarrior->loadTask(1));
    }

}
