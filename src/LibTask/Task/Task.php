<?php

namespace LibTask\Task;

use JMS\Serializer;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Inline;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\ClassLoader\UniversalClassLoader;

/**
 * @file
 *   Methods for creating a Taskwarrior task object.
 */

/**
 * Task class.
 *
 * Methods for modeling a Taskwarrior task.
 *
 * @author  Kosta Harlan <kosta@embros.org>
 */
class Task
{
    /**
     * @Type("integer")
     */
    private $id;
    /**
     * @Type("string")
     */
    private $description;
    /**
     * @Type("string")
     */
    private $due;
    /**
     * @Type("string")
     */
    private $entry;
    /**
     * @Type("string")
     */
    private $modified;
    /**
     * @Type("string")
     */
    private $project;
    /**
     * @Type("string")
     */
    private $status;
    /**
     * @Type("array<string>")
     */
    private $tags;
    /**
     * @Type("string")
     */
    private $uuid;
    /**
     * @Type("double")
     */
    private $urgency;
    /**
     * @Type("string")
     * @Accessor(getter="getSerializedDependencies")
     */
    private $depends;
    /**
     * @Type("array<string, string>")
     * @Inline
     * @Accessor(getter="getUdas")
     */
    private $udas;
    /**
     * @Type("string")
     */
    private $priority;

    /**
     * Construct.
     *
     * @param $description The description to use for the task.
     */
    public function __construct($description = '')
    {
        // Set task description.
        if ($description) {
            $this->setDescription($description);
        }
    }

    /**
     * Get the task Id.
     *
     * @Type("integer")
     * @return int Taskwarrior task ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the task description.
     *
     * @Type("string")
     * @return string Taskwarrior task description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get the task due date.
     *
     * @Type("integer")
     * @return int The UNIX timestamp of the task due date.
     */
    public function getDue()
    {
        return $this->due;
    }

    /**
     * Get the task time entry.
     *
     * If the value is not set, return the current time as a UNIX timestamp.
     *
     * @Type("integer")
     *
     * @return int UNIX timestamp of the task creation time.
     */
    public function getEntry()
    {
        return ($this->entry) ? $this->entry :  (string) time();
    }

    public function getModified()
    {
        return $this->modified;
    }

    public function getProject()
    {
        return $this->project;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getUrgency()
    {
        return $this->urgency;
    }

    public function getDependencies()
    {
        return $this->depends;
    }

    public function getSerializedDependencies()
    {
        return (is_array($this->depends)) ? implode(',', $this->depends) : (string) $this->depends;
    }

    public function getUdas()
    {
        return $this->udas;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function setDue($due) {
        $this->due = (is_string($due)) ? strtotime($due) : $due;
    }

    public function setEntry($entry) {
        $this->entry = (is_string($entry)) ? strtotime($entry) : $entry;
    }

    public function setModified($modified) {
        $this->modified = (is_string($modified)) ? strtotime($modified) : $modified;
    }

    public function setProject($project) {
        $this->project = $project;
    }

    public function setStatus($status) {
        if (!in_array($status, array('pending', 'completed'))) {
            // TODO: Throw exception.
        }
        $this->status = $status;
    }

    public function setTags($tags) {
        $this->tags = $tags;
    }

    public function setUuid($uuid) {
        $this->uuid = $uuid;
    }

    public function setUrgency($urgency) {
        $this->urgency = $urgency;
    }

    public function setDependencies($depends) {
        $this->depends = $depends;
    }

    public function setUdas($udas) {
        $this->udas = $udas;
    }
}
