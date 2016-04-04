<?php

declare(ticks = 1);

define('EPROCESS_AUTOLOAD', __FILE__);
define('EPROCESS_SOCKET_DIR', '/tmp/eprocess');

$loader = require __DIR__ . '/../vendor/autoload.php';

use Doctrine\Common\Annotations\AnnotationRegistry;

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
