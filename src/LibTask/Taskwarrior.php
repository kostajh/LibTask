<?php

namespace LibTask;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Common\Annotations\AnnotationRegistry;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\Exception\RuntimeException;
use LibTask\Task\Task;
use LibTask\Task\Annotation;
use LibTask\Subscribers\TaskDeserializeSubscriber;

AnnotationRegistry::registerLoader('class_exists');

/**
 * @file
 *   Methods for interacting with Taskwarrior.
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
    protected $taskwarriorVersion = null;
    protected $taskwarriorResponse = array();

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
     * Get the response from Taskwarrior after issuing a command.
     * @return array
     */
    public function getResponse()
    {
        return $this->taskwarriorResponse;
    }

    /**
     * Returns the output from the Taskwarrior command.
     * @return string
     */
    public function getOutput()
    {
        return isset($this->taskwarriorResponse['output']) ? $this->taskwarriorResponse['output'] : null;
    }

    /**
     * Get the Taskwarrior version.
     * @return string
     */
    public function getVersion()
    {
        if (!$this->taskwarriorVersion) {
            $this->setVersion();
        }

        return $this->taskwarriorVersion;
    }

    /**
     * Set the Taskwarrior version.
     */
    public function setVersion()
    {
        $this->taskwarriorVersion = $this->taskCommand('_version')->getOutput();

        return $this;
    }

    /**
     * Import command.
     *
     * @param mixed $task
     *   The task object or a data file containing JSON encoded tasks.
     */
    public function import($task)
    {
        $fs = new Filesystem();
        if (is_object($task)) {
            $jsonData = $this->serializeTask($task);
            // Write the serialized task to a temp file.
            $data_file = tempnam(sys_get_temp_dir(), 'LibTask') . '.json';
            $fs->dumpFile($data_file, $jsonData);
        } else {
            $data_file = $task;
            // If we have a data file, check that it exists.
            if (!$fs->exists($data_file)) {
                return false;
            }
        }
        $result = $this->taskCommand('import', $data_file)->getResponse();
        if (is_object($task)) {
            // If task is an object then get the UUID and the loaded task.
            $this->taskwarriorResponse['uuid'] = $this->getUuidFromImport($task);
            $this->taskwarriorResponse['task'] = $this->loadTask($this->taskwarriorResponse['uuid']);
            $this->taskwarriorResponse['json'] = $jsonData;
        }
        $this->setResponse($result);

        return $this;
    }

    /**
     * `done` command.
     *
     * @param string $uuid
     */
    public function complete($uuid)
    {
        return $this->taskCommand('done', $uuid);
    }

    /**
     * `delete` command.
     */
    public function delete($uuid)
    {
        return $this->taskCommand('delete', $uuid);
    }

    /**
     * Wrapper around update() and add().
     *
     * @param Task $task
     */
    public function save(Task $task)
    {
        return ($task->getUuid()) ? $this->update($task) : $this->import($task);
    }

    /**
     * Update a task.
     *
     * The Task parameter must contain a UUID for the task to be updated.
     *
     * @param Task $task
     */
    public function update(Task $task)
    {
        if (!$task->getUuid()) {
            return false;
        }
        // Make sure we can load a task. TODO return error if not possible.
        $existing_task = $this->loadTask($task->getUuid());
        if (!$existing_task) {
            return false;
        }
        // Build a string to use with taskCommand().
        $modify = array();
        $modify['description'] = $task->getDescription();
        if ($task->getDue()) {
            $modify['due'] = $task->getDue();
        }
        // Taskwarrior doesn't support adding annotations via `modify`.
        $annotations = $task->getAnnotations();
        if ($task->getEntry()) {
            $modify['entry'] = $task->getEntry();
        }
        if ($task->getProject()) {
            $modify['project'] = $task->getProject();
        }
        if ($task->getStatus()) {
            $modify['status'] = $task->getStatus();
        }
        if ($task->getTags()) {
            $modify['tags'] = implode(',', $task->getTags());
        }
        if ($task->getUrgency()) {
            $modify['urgency'] = $task->getUrgency();
        }
        if ($task->getDependencies()) {
            $modify['depends'] = $task->getSerializedDependencies();
        }
        if ($task->getUdas()) {
            $udas = $task->getUdas();
            foreach ($udas as $key => $value) {
                $modify[$key] = $value;
            }
        }
        if ($task->getPriority()) {
            $modify['priority'] = $task->getPriority();
        }
        // TODO: Add support for remaining properties.
        $result = $this->taskCommand('modify', $existing_task->getUuid(), $modify)->getResponse();
        // Add annotations if any, and if they are different from what is
        // already there.
        $existing_annotations = $existing_task->getAnnotations();
        $existing_annotations_data = array();
        if (count($existing_annotations)) {
            foreach ($existing_annotations as $note) {
                $existing_annotations_data[] = $note->getDescription();
            }
        }
        $new_annotations = $task->getAnnotations();
        $new_annotations_data = array();
        if (count($new_annotations)) {
            foreach ($new_annotations as $note) {
                $new_annotations_data[] = $note->getDescription();
            }
        }
        $annotations_diff = array_diff($new_annotations_data, $existing_annotations_data);
        if (count($annotations_diff)) {
            foreach ($annotations_diff as $annotation) {
                $note = new Annotation($annotation);
                $this->annotate($task, $note);
            }
        }
        $result['uuid'] = $task->getUuid();
        $result['task'] = $this->loadTask($result['uuid']);

        return $result;
    }

    /**
     * Annotate a task.
     */
    public function annotate(Task $task, Annotation $annotation)
    {
        return $this->taskCommand('annotate', $task->getUuid(), $annotation->getDescription());
    }

    /**
     * Add a task.
     *
     * @param array $mods
     */
    public function addTask(Task $task)
    {
        $response = $this->import($task)
            ->getResponse();
        $response['uuid'] = $this->getUuidFromImport($task);
        $this->setResponse(array('task' => $this->loadTask($response['uuid'])));

        return $this;
    }

    /**
     * Get the total active time for a task.
     */
    public function getTaskActiveTime($uuid)
    {
        $task_info_output = $this->taskCommand('info', $uuid)->getOutput();
        if (strpos($task_info_output, 'Total active time')) {
            $task_info_output_components = explode(' ', $task_info_output);
            return trim(end($task_info_output_components));
        }
    }

    public function getUuidFromImport($task)
    {
        // Parse the output and get the task UUID.
        $result = $this->getResponse();
        $output = explode(' ', $result['output']);
        $parts = explode(' ', $result['command_line']);
        $intersect = array_intersect($output, $parts);
        foreach ($output as $item) {
            if (in_array(trim($item), $parts)) {
                $filename = $item;
                break;
            }
        }
        // Get the filename from the output.
        $output = $result['output'];
        $output = ltrim($output, 'Importing ');
        $output = ltrim($output, $filename);
        $uuid = trim(substr($output, 0, strpos($output, $task->getDescription())));

        return $uuid;
    }

    /**
     * Load a single Task.
     *
     * @return array
     */
    public function loadTask($filter = NULL, $options = array())
    {
        $tasks = $this->loadTasks($filter, $options);
        if (!is_array($tasks) || !count($tasks)) {
            // TODO: Throw exception.
            return false;
        }

        return array_shift($tasks);
    }

    /**
     * Load an array of Tasks.
     *
     * @param  string $filter
     * @param  array  $options
     * @return array
     */
    public function loadTasks($filter = NULL, $options = array())
    {
        $data = $this->taskCommand('export', $filter, $options)
            ->getResponse();
        if (!$data['success'] || $data['exit_code'] != 0) {
            return false;
        }
        if (!$data['output']) {
            return array();
        }
        $builder = SerializerBuilder::create();
        $builder
            ->configureListeners(function(EventDispatcher $dispatcher) {
                $dispatcher->addSubscriber(new TaskDeserializeSubscriber());
            })
        ;
        $serializer = $builder->build();
        $tasks = array();
        try {
            $object = $serializer->deserialize($data['output'], 'ArrayCollection<Libtask\Task\Task>', 'json');
        } catch (RuntimeException $e) {
            echo 'Malformed JSON: ' . $data['output'];

            return false;
        }

        return $object;
    }

    /**
     * Add options to the ProcessBuilder object.
     *
     * @param ProcessBuilder $process_builder
     * @param array          $options
     */
    public function addOptions(ProcessBuilder &$process_builder, $options = array())
    {
        if (!is_array($options) && is_string($options)) {
            $process_builder->add($options);

            return $this;
        }
        foreach ($options as $key => $value) {
            if (is_int($key)) {
                $process_builder->add(sprintf("%s", $value));
            }
            if (is_array($value)) {
                // TODO: Do something.
                print_r($value);
            } else {
                $process_builder->add(sprintf('%s:"%s"', $key, $value));
            }
        }

        return $this;
    }

    /**
     * Add RC options to the ProcessBuilder object.
     */
    public function addRcOptions(ProcessBuilder &$process_builder, $options = array())
    {
        if (!count($options)) {
            return false;
        }
        foreach ($options as $option) {
            $process_builder->add($option);
        }

        return $this;
    }

    /**
     * Executes a Taskwarrior command.
     *
     * This method should not be called directly if possible.
     *
     * @param  string $command The taskwarrior command.
     * @param  string $filter  A filter to use with the command.
     * @param  array  $options
     * @return array
     */
    public function taskCommand($command = NULL, $filter = NULL, $options = array())
    {
        if (!$command) {
            return false;
        }
        $this->setResponse(array());
        $process_builder = new ProcessBuilder();
        $this->addRcOptions($process_builder, array_merge($this->rcOptions, $this->getGlobalRcOptions()));
        if ($filter) {
            $process_builder->add($filter);
        }
        $process_builder->add($command);
        $this->addOptions($process_builder, $options);
        $process_builder->setPrefix('task');
        $process = $process_builder->getProcess();
        $process->setTimeout(360);
        $process->start();
        while ($process->isRunning()) {
            // Wait.
            $process->checkTimeout();
        }
        $response = array(
            'output' => $process->getOutput(),
            'error_output' => $process->getErrorOutput(),
            'command_line' => $process->getCommandLine(),
            'success' => $process->isSuccessful(),
            'exit_code' => $process->getExitCode(),
        );

        return $this->setResponse($response);
    }

    /**
     * Set the taskwarriorResponse variable.
     * @param array $response
     */
    public function setResponse($response)
    {
        $this->taskwarriorResponse = array_merge($this->taskwarriorResponse, $response);

        return $this;
    }

    /**
     * Start a task.
     */
    public function start($uuid)
    {
        return $this->taskCommand('start', $uuid);
    }

    /**
     * Stop a task.
     */
    public function stop($uuid)
    {
        return $this->taskCommand('stop', $uuid);
    }

    /**
     * Serializes a task to JSON for importing into Taskwarrior.
     *
     * @param Task $task
     */
    public function serializeTask(Task $task)
    {
        $serializer = SerializerBuilder::create()
        ->addDefaultHandlers()
        ->build();
        $jsonContent = $serializer->serialize($task, 'json');
        $jsonContent = str_replace("\\/", "/", $jsonContent);

        return $jsonContent;
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
            'rc.confirmation=no',
        );
    }

}
