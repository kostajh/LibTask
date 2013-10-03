<?php

namespace Taskwarrior;

use LibTask\Taskwarrior;
use LibTask\Task\Task;
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

    protected static function deleteTestData()
    {
        // Set up test directory.
        $command = sprintf('rm ' . __DIR__ . '/.task/*');
        $process = new Process($command);
        $process->run();
        self::initializeTaskwarrior();
    }

    protected static function mangleTaskData()
    {
        $command = 'echo "Mangled" >> ' . __DIR__ . '/.task/pending.data';
        $process = new Process($command);
        $process->run();
    }

    protected static function initializeTaskwarrior()
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
    }

    /**
     * @covers LibTask\Taskwarrior::__construct
     */
    public function testTaskwarrior()
    {
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertObjectHasAttribute('taskrc', $taskwarrior);
        $this->assertObjectHasAttribute('taskData', $taskwarrior);
        $this->assertObjectHasAttribute('rcOptions', $taskwarrior);
    }

    /**
     * @covers LibTask\Taskwarrior::getVersion
     */
    public function testGetVersion()
    {
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertRegExp('/2./', $taskwarrior->getVersion());
    }

    /**
     * @covers LibTask\Taskwarrior::import
     */
    public function testImport()
    {
        // Load non-existent file.
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertFalse($taskwarrior->import('/tmp/' . md5(time())));
        // Successful import.
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $result = $taskwarrior->import(__DIR__ . '/sample-tasks.json');
        $this->assertEquals($result['success'], 1);
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
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $taskwarrior->import(__DIR__ . '/sample-tasks.json');
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertNotEmpty($taskwarrior->loadTask(1));
    }

    /**
     * @covers LibTask\Taskwarrior::taskCommand
     */
    public function testTaskCommand()
    {
        // Test a basic command.
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $ret = $taskwarrior->taskCommand('list');
        $this->assertNotEmpty($ret);
        $this->assertNotEmpty($ret['output']);
        $this->assertEquals($ret['exit_code'], 0);
        $this->assertContains('list', $ret);
        $this->assertEquals($ret['success'], 1);
        // No command provided.
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $this->assertFalse($taskwarrior->taskCommand());
        // Provide filter.
        $ret = $taskwarrior->taskCommand('list', '+test');
        $this->assertEquals($ret['success'], 1);
    }

    /**
     * @covers LibTask\Taskwarrior::getGlobalRcOptions
     */
    public function testGetGlobalRcOptions()
    {
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $rc_options = $taskwarrior->getGlobalRcOptions();
        $this->assertContains('rc:' . $this->taskrc, $rc_options);
        $this->assertContains('rc.data.location=' . $this->taskData, $rc_options);
        $this->assertContains('rc.json.array=true', $rc_options);
    }

    /**
     * @covers LibTask\Taskwarrior::addRcOptions
     */
    public function testAddRcOptions()
    {
        $process_builder = new ProcessBuilder();
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        // Test failing to add options.
        $this->assertFalse($taskwarrior->addRcOptions($process_builder));
        // Add options.
        $taskwarrior->addRcOptions($process_builder, array('rc.json.array=false'));
        $process = $process_builder->getProcess();
        $this->assertRegExp('/rc.json.array=false/', $process->getCommandLine());
    }

    /**
     * @covers LibTask\Taskwarrior::addOptions
     */
    public function testAddOptions()
    {
        $process_builder = new ProcessBuilder();
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        // Test failing to add options.
        $this->assertFalse($taskwarrior->addOptions($process_builder));
        // Add options.
        $taskwarrior->addOptions($process_builder, array('status' => 'completed'));
        $process = $process_builder->getProcess();
        $this->assertRegExp('/status:completed/', $process->getCommandLine());
    }

    /**
     * @covers LibTask\Taskwarrior::addTask
     */
    public function testAddTask()
    {
        self::deleteTestData();
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $task = new Task('Brew coffee');
        $task->setProject('life');
        $task->setPriority('H');
        $task->setTags(array('coffee', 'beans'));
        $task->setUdas(array('logged' => 'false', 'estimate' => '3days'));
        $result = $taskwarrior->addTask($task);
        $this->assertContains('Created task', $result);
        $task = $result['task'];
        $this->assertEquals('Brew coffee', $task->getDescription());
        $this->assertEquals('H', $task->getPriority());
        $this->assertEquals('life', $task->getProject());
        $this->assertArrayHasKey('uuid', $result);
        $this->assertEquals($result['uuid'], $task->getUuid());
        $udas = $task->getUdas();
        // Test adding dependencies.
        $new_task = new Task('Drink coffee');
        $new_task->setDependencies(array($task->getUuid()));
        $result = $taskwarrior->addTask($new_task);
        $this->assertEquals($result['task']->getDependencies(), $task->getUuid());
    }

    public function testTaskSerialize() {
        $task = new Task();
        $task->setDescription('Hello world');
        $task->setUdas(array('logged' => 'false', 'estimate' => '1day'));
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $jsonData = $taskwarrior->serializeTask($task);
        $this->assertRegExp('/"description":"Hello world"/', $jsonData);
    }

    public function testTaskImport() {
        $task = new Task;
        $task->setDescription('Hello world');
        $task->setProject('life');
        $task->setPriority('M');
        $taskwarrior = new Taskwarrior($this->taskrc, $this->taskData);
        $result = $taskwarrior->importTask($task);
        $this->assertRegExp('/Hello world/', $result['output']);
    }

}
