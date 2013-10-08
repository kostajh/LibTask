<?php

namespace LibTask\Subscribers;

use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use LibTask\Task\Task;

class TaskDeserializeSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.pre_deserialize', 'method' => 'onPreDeserialize'),
        );
    }

    public function onPreDeserialize(PreDeserializeEvent $event)
    {
        // Add UDAs to the UDA array.
        $data = $event->getData();
        // Core task properties. Everything else is a UDA.
        $task_vars = array(
            'id',
            'description',
            'due',
            'entry',
            'annotations',
            'modified',
            'project',
            'start',
            'status',
            'tags',
            'uuid',
            'urgency',
            'depends',
            'priority',
        );
        $udas = array();
        foreach ($data as $key => $value) {
            if (!in_array($key, $task_vars) && strpos($key, 'annotation_') !== 0) {
                $udas[$key] = $value;
            }
        }
        $data['udas'] = $udas;
        if ($data['udas'] && isset($data['uuid']) && !empty($data['uuid'])) {
            $event->setData($data);
        }
    }
}
