<?php

define('EPROCESS_AUTOLOAD', __FILE__);

$loader = require __DIR__ . '/../vendor/autoload.php';

use Doctrine\Common\Annotations\AnnotationRegistry;

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
