<?php

namespace LibTask;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

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
 * Methods for interacting with a Taskwarrior database.
 *
 * @author  Kosta Harlan <kosta@embros.org>
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
    protected $rcOptions = array();

    /**
     * @param string $taskrc
     * @param string $task_data
     * @param array  $rc_options
     */
    public function __construct($taskrc = '~/.taskrc', $task_data = '~/.task', $rc_options = array())
    {
        $this->taskData = $task_data;
        $this->taskrc = $taskrc;
        $this->rcOptions = $rc_options;
    }

    /**
     * Get the Taskwarrior version.
     * @return string
     */
    public function getVersion()
    {
        $process = new Process('task --version');
        $process->run();
        return $process->getOutput();
    }

    /**
     * Import command.
     * @param  string $data_file
     * @return
     */
    public function import($data_file)
    {
        $fs = new Filesystem();
        if (!$fs->exists($data_file)) {
            return false;
        }
        return $this->taskCommand('import', $data_file);
    }

    /**
     * @return array
     */
    public function loadTask($filter = NULL, $options = array())
    {
        $tasks = $this->loadTasks($filter, $options);
        return array_shift($tasks);
    }

    public function loadTasks($filter = NULL, $options = array())
    {
        $data = $this->taskCommand('export', $filter, $options);
        if (!$data['success'] || $data['exit_code'] != 0) {
            return false;
        }
        return $this->decodeJson($data['output']);
    }

    public function decodeJson($json_string)
    {
        return json_decode($json_string, TRUE);
    }

    public function convertOptionsToString($options = array())
    {
        if (!count($options)) {
            return false;
        }
        $option_string = '';
        foreach ($options as $key => $value) {
            $option_string .= $key . ':' . $value;
            $option_string .= ' ';
        }

        return $option_string;
    }

    public function addOptions(ProcessBuilder &$process_builder, $options = array())
    {
        if (!count($options)) {
            return false;
        }
        foreach ($options as $key => $value) {
            $process_builder->add($key . ':' . $value);
        }
    }

    public function addRcOptions(ProcessBuilder &$process_builder, $options = array())
    {
        if (!count($options)) {
            return false;
        }
        foreach ($options as $option) {
            $process_builder->add($option);
        }
    }

    /**
     * Executes a Taskwarrior command.
     * @param string $command The taskwarrior command.
     * @param string $filter A filter to use with the command.
     * @param array $options
     * @return array
     */
    public function taskCommand($command = NULL, $filter = NULL, $options = array())
    {
        if (!$command) {
            return false;
        }
        $process_builder = new ProcessBuilder();
        $this->addRcOptions($process_builder, array_merge($this->rcOptions, $this->getGlobalRcOptions()));
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

    /**
     * Return an array of global taskrc options.
     */
    public function getGlobalRcOptions()
    {
        return array(
            'rc:' . $this->taskrc,
            'rc.data.location=' . $this->taskData,
            'rc.json.array=true',
        );
    }

}
