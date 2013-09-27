<?php

namespace LibTask;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @file
 *   Methods for interacting with Taskwarrior.
 * @category
 * @tags
 * @package
 * @license
 */

/**
 * Taskwarrior class.
 *
 */
class Taskwarrior
{

    /**
     * Path to Taskwarrior client data.
     *
     * @var string
     */
    protected $taskData = null;
    protected $taskrc = null;

    protected $tasks = null;

    public function __construct($taskrc = '~/.taskrc', $task_data = '~/.task')
    {
        $this->taskData = $task_data;
        $this->taskrc = $taskrc;
    }

    public function import($data_file)
    {
        return $this->taskCommand('import', $data_file);
    }

    /**
     * @return array
     */
    public function loadTask($filter = NULL, $options = array())
    {
        return array_shift($this->loadTasks($filter, $options));
    }

    public function loadTasks($filter = NULL, $options = array())
    {
        $data = $this->taskCommand('export', $filter, $options);
        if (!$data['success'] || $data['exit_code'] != 0) {
            return false;
        }
        try {
           $decoded = json_decode($data['output'], TRUE);

           return $decoded;
        } catch (Exception $e) {
            print 'Failed to decode JSON';
        }
    }

    protected function convertOptionsToString($options = array())
    {
        if (!count($options)) {
            return ' ';
        }
        $option_string = '';
        foreach ($options as $key => $value) {
            $option_string .= $key . ':' . $value;
            $option_string .= ' ';
        }

        return $option_string;
    }

    protected function addOptions(ProcessBuilder &$process_builder, $options)
    {
        if (!count($options)) {
            return;
        }
        foreach ($options as $key => $value) {
            $process_builder->add($key . ':' . $value);
        }
    }

    protected function addRcOptions(ProcessBuilder &$process_builder, $options)
    {
        if (!count($options)) {
            return;
        }
        foreach ($options as $option) {
            $process_builder->add($option);
        }
    }

    protected function taskCommand($command = NULL, $filter = NULL, $options = NULL)
    {
        if (!$command) {
            return false;
        }
        $process_builder = new ProcessBuilder();
        $this->addRcOptions($process_builder, $this->getGlobalRcOptions());
        if ($filter) {
            $process_builder->add($filter);
        }
        $process_builder->add($command);
        $this->addOptions($process_builder, $options);
        $process_builder->setPrefix('task');
        $process = $process_builder->getProcess();
        $process->run();

        return array(
            'output' => $process->getOutput(),
            'error_output' => $process->getErrorOutput(),
            'command_line' => $process->getCommandLine(),
            'success' => $process->isSuccessful(),
            'exit_code' => $process->getExitCode(),
        );
    }

    protected function getGlobalRcOptions()
    {
        return array(
            'rc:' . $this->taskrc,
            'rc.data.location=' . $this->taskData,
            'rc.json.array=true',
        );
    }

}
