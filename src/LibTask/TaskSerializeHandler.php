<?php

namespace LibTask;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Context;

class TaskSerializeHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'Array',
            ),
        );
    }

    public function convertArrayToUdas(JsonSerializationVisitor $visitor, Array $udas, array $type, Context $context)
    {
        print 'here we are';
    }
}
