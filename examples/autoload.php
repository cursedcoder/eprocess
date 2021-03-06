<?php

define('EPROCESS_AUTOLOAD', __FILE__);
define('EPROCESS_SOCKET_DIR', realpath(__DIR__ . '/../tmp'));

$loader = require __DIR__ . '/../vendor/autoload.php';

use Doctrine\Common\Annotations\AnnotationRegistry;

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
