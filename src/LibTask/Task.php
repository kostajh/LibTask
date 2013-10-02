<?php

namespace LibTask;

use JMS\Serializer;
use JMS\Serializer\Annotation\Type;
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
     * @Type("array")
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
     * @Type("array")
     */
    private $depends;
    /**
     * @Type("array")
     */
    private $udas;
    /**
     * @Type("string")
     */
    private $priority;

    /**
     * Construct.
     */
    public function __construct($description = '')
    {
        if ($description) {
            $this->setDescription($description);
        }
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getDue()
    {
        return $this->due;
    }

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

    public function getUdas()
    {
        return $this->udas;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    // Setters
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
        // TODO: Validate status.
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
