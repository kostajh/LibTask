<?php

namespace LibTask\Task;

use JMS\Serializer;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Inline;
use LibTask\Task\Annotation;

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
    private $start;
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
     * @Type("array<LibTask\Task\Annotation>")
     * @Accessor(getter="getAnnotations", setter="setAnnotations")
     */
    private $annotations;
    /**
     * @Type("array")
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

        return $this;
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
     * Get the task start time.
     */
    public function getStart()
    {
        return $this->start;
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
     * Get the task annotations.
     */
    public function getAnnotations()
    {
        return $this->annotations;
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

    // Setters.

    /**
     * Note, this should only be used to unset the Id.
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    public function setDue($due)
    {
        $this->due = (is_string($due)) ? strtotime($due) : $due;

        return $this;
    }

    public function setEntry($entry)
    {
        $this->entry = (is_string($entry)) ? strtotime($entry) : $entry;

        return $this;
    }

    public function setModified($modified)
    {
        $this->modified = (is_string($modified)) ? strtotime($modified) : $modified;

        return $this;
    }

    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }

    public function setStatus($status)
    {
        if (!in_array($status, array('pending', 'completed'))) {
            // TODO: Throw exception.
        }
        $this->status = $status;

        return $this;
    }

    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    public function setUrgency($urgency)
    {
        $this->urgency = $urgency;

        return $this;
    }

    public function setDependencies($depends)
    {
        $this->depends = $depends;

        return $this;
    }

    public function setUdas($udas)
    {
        $this->udas = $udas;

        return $this;
    }

    public function setAnnotations($annotations)
    {
        $this->annotations = $annotations;

        return $this;
    }
}
