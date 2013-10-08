<?php

namespace LibTask\Task;

use JMS\Serializer\Annotation\Type;

class Annotation
{
    /**
     * @Type("string")
     * The time entry must be unique among multiple annotations of a single Task.
     */
    private $entry;
    /**
     * @Type("string")
     */
    private $description;

    public function __construct($description = '')
    {
      if ($description) {
        $this->entry = time();
        $this->description = $description;
      }
    }

    public function setEntry($entry)
    {
      $this->entry = $entry;
    }

    public function setDescription($description)
    {
      $this->description = $description;
    }

    public function getEntry()
    {
      return $this->entry;
    }

    public function getDescription()
    {
      return $this->description;
    }
}
