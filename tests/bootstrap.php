<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require __DIR__ . "/../vendor/autoload.php";
$loader->add('LibTask\\', __DIR__);

AnnotationRegistry::registerLoader('class_exists');
